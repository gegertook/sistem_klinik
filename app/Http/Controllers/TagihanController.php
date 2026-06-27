<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $query = Tagihan::with(['kunjungan.pasien', 'kunjungan.poli', 'kunjungan.dokter']);

        if ($request->filled('q')) {
            $query->where(function ($builder) use ($request) {
                $builder
                    ->where('no_tagihan', 'like', '%'.$request->q.'%')
                    ->orWhereHas('kunjungan.pasien', fn ($pasien) => $pasien->where('nama_pasien', 'like', '%'.$request->q.'%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status_pembayaran', $request->status);
        }

        return view('tagihan.index', [
            'items' => $query->latest()->paginate(10)->withQueryString(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function show(Tagihan $tagihan)
    {
        $tagihan->load([
            'kunjungan.pasien',
            'kunjungan.poli',
            'kunjungan.dokter',
            'kunjungan.pemeriksaan.detailLayanan.layanan',
            'pembayaran',
        ]);

        return view('tagihan.show', [
            'tagihan' => $tagihan,
            'midtransClientKey' => config('services.midtrans.client_key'),
            'isMidtransProduction' => filter_var(config('services.midtrans.is_production'), FILTER_VALIDATE_BOOL),
        ]);
    }

    public function payManual(Request $request, Tagihan $tagihan)
    {
        abort_if($tagihan->status_pembayaran === 'Berhasil Dibayar', 422, 'Tagihan sudah lunas.');

        $data = $request->validate([
            'metode_pembayaran' => ['required', 'string', 'max:40'],
        ]);

        $this->completeDirectPayment($tagihan, $data['metode_pembayaran'], 'MANUAL', 'manual');

        return back()->with('success', 'Pembayaran manual berhasil diproses.');
    }

    public function payBpjs(Tagihan $tagihan)
    {
        abort_if($tagihan->status_pembayaran === 'Berhasil Dibayar', 422, 'Tagihan sudah lunas.');

        $this->completeDirectPayment($tagihan, 'BPJS', 'BPJS', 'bpjs');

        return back()->with('success', 'Pembayaran BPJS berhasil diproses.');
    }

    public function payOnline(Tagihan $tagihan)
    {
        abort_if($tagihan->status_pembayaran === 'Berhasil Dibayar', 422, 'Tagihan sudah lunas.');

        $serverKey = config('services.midtrans.server_key');
        if (blank($serverKey)) {
            return back()->with('error', 'MIDTRANS_SERVER_KEY belum diisi di file .env.');
        }

        $tagihan->load(['kunjungan.pasien', 'kunjungan.pemeriksaan.detailLayanan.layanan']);
        $orderId = $tagihan->midtrans_order_id ?: 'MID-'.$tagihan->id.'-'.now()->format('YmdHis');
        $baseUrl = $this->midtransBaseUrl();

        $items = $tagihan->kunjungan->pemeriksaan->detailLayanan
            ->map(fn ($detail) => [
                'id' => (string) $detail->layanan_id,
                'price' => $detail->harga,
                'quantity' => $detail->jumlah,
                'name' => $detail->layanan->nama_layanan,
            ])->values()->all();

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post($baseUrl.'/snap/v1/transactions', [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $tagihan->total_tagihan,
                ],
                'customer_details' => [
                    'first_name' => $tagihan->kunjungan->pasien->nama_pasien,
                    'phone' => $tagihan->kunjungan->pasien->no_hp,
                ],
                'item_details' => $items,
                'callbacks' => [
                    'finish' => route('tagihan.show', $tagihan),
                ],
            ]);

        if ($response->failed()) {
            return back()->with('error', 'Gagal membuat transaksi Midtrans: '.$response->body());
        }

        $tagihan->update([
            'status_pembayaran' => 'Menunggu Pembayaran',
            'metode_pembayaran' => 'Mandiri',
            'snap_token' => $response->json('token'),
            'midtrans_order_id' => $orderId,
        ]);

        return back()->with('success', 'Pembayaran mandiri dibuat. Tombol bayar online sudah aktif.');
    }

    public function syncMidtransStatus(Tagihan $tagihan)
    {
        abort_if(blank($tagihan->midtrans_order_id), 422, 'Order Midtrans belum dibuat.');

        $payload = $this->fetchMidtransStatus($tagihan->midtrans_order_id);
        $tagihan = $this->applyMidtransPayment($tagihan, $payload);

        return response()->json([
            'message' => 'ok',
            'status_pembayaran' => $tagihan->status_pembayaran,
        ]);
    }

    public function notification(Request $request)
    {
        $payload = $request->all();
        $orderId = $payload['order_id'] ?? null;

        abort_if(blank($orderId), 422, 'order_id tidak ditemukan.');

        $this->validateMidtransSignature($payload);

        $tagihan = Tagihan::where('midtrans_order_id', $orderId)->firstOrFail();
        $this->applyMidtransPayment($tagihan, $payload);

        return response()->json(['message' => 'ok']);
    }

    private function applyMidtransPayment(Tagihan $tagihan, array $payload): Tagihan
    {
        $systemStatus = $this->mapStatus(
            $payload['transaction_status'] ?? 'pending',
            $payload['fraud_status'] ?? null
        );

        return DB::transaction(function () use ($tagihan, $payload, $systemStatus) {
            if ($tagihan->status_pembayaran === 'Berhasil Dibayar' && $systemStatus !== 'Berhasil Dibayar') {
                $this->recordPayment($tagihan, $payload);

                return $tagihan->refresh();
            }

            $paidAt = $payload['settlement_time'] ?? $payload['transaction_time'] ?? now();

            $tagihan->update([
                'status_pembayaran' => $systemStatus,
                'metode_pembayaran' => $this->mandiriPaymentMethod($payload['payment_type'] ?? null),
                'tanggal_bayar' => $systemStatus === 'Berhasil Dibayar' ? $paidAt : $tagihan->tanggal_bayar,
            ]);

            if ($systemStatus === 'Berhasil Dibayar') {
                $tagihan->kunjungan()->update(['status_kunjungan' => 'Selesai']);
            }

            $this->recordPayment($tagihan, $payload);

            return $tagihan->refresh();
        });
    }

    private function recordPayment(Tagihan $tagihan, array $payload): void
    {
        $orderId = (string) ($payload['order_id'] ?? $tagihan->midtrans_order_id);
        $transactionStatus = (string) ($payload['transaction_status'] ?? 'pending');

        Pembayaran::updateOrCreate(
            [
                'tagihan_id' => $tagihan->id,
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
            ],
            [
                'payment_type' => $payload['payment_type'] ?? null,
                'transaction_time' => $payload['transaction_time'] ?? now(),
                'gross_amount' => (int) round((float) ($payload['gross_amount'] ?? $tagihan->total_tagihan)),
                'fraud_status' => $payload['fraud_status'] ?? null,
                'response_midtrans' => $payload,
            ]
        );
    }

    private function completeDirectPayment(Tagihan $tagihan, string $paymentMethod, string $orderPrefix, string $source): void
    {
        DB::transaction(function () use ($tagihan, $paymentMethod, $orderPrefix, $source) {
            $orderId = $orderPrefix.'-'.$tagihan->id.'-'.now()->format('YmdHis');

            $tagihan->update([
                'status_pembayaran' => 'Berhasil Dibayar',
                'metode_pembayaran' => $paymentMethod,
                'snap_token' => null,
                'midtrans_order_id' => null,
                'tanggal_bayar' => now(),
            ]);

            $tagihan->kunjungan()->update(['status_kunjungan' => 'Selesai']);

            Pembayaran::create([
                'tagihan_id' => $tagihan->id,
                'order_id' => $orderId,
                'payment_type' => $paymentMethod,
                'transaction_status' => 'settlement',
                'transaction_time' => now(),
                'gross_amount' => $tagihan->total_tagihan,
                'response_midtrans' => ['source' => $source],
            ]);
        });
    }

    private function fetchMidtransStatus(string $orderId): array
    {
        $serverKey = config('services.midtrans.server_key');
        abort_if(blank($serverKey), 422, 'MIDTRANS_SERVER_KEY belum diisi di file .env.');

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->get($this->midtransBaseUrl().'/v2/'.rawurlencode($orderId).'/status');

        abort_if($response->failed(), 422, 'Gagal membaca status Midtrans: '.$response->body());

        return $response->json();
    }

    private function validateMidtransSignature(array $payload): void
    {
        $serverKey = config('services.midtrans.server_key');

        abort_if(blank($serverKey), 403, 'MIDTRANS_SERVER_KEY belum diisi di file .env.');
        abort_if(blank($payload['signature_key'] ?? null), 403, 'Signature Midtrans tidak ditemukan.');

        $expected = hash(
            'sha512',
            ($payload['order_id'] ?? '').
            ($payload['status_code'] ?? '').
            ($payload['gross_amount'] ?? '').
            $serverKey
        );

        abort_unless(hash_equals($expected, (string) $payload['signature_key']), 403, 'Signature Midtrans tidak valid.');
    }

    private function midtransBaseUrl(): string
    {
        $isProduction = filter_var(config('services.midtrans.is_production'), FILTER_VALIDATE_BOOL);

        return $isProduction ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
    }

    private function mandiriPaymentMethod(?string $paymentType): string
    {
        if (blank($paymentType)) {
            return 'Mandiri';
        }

        return 'Mandiri - '.strtoupper(str_replace('_', ' ', $paymentType));
    }

    private function mapStatus(string $status, ?string $fraudStatus = null): string
    {
        if ($status === 'capture' && $fraudStatus === 'challenge') {
            return 'Menunggu Pembayaran';
        }

        return match ($status) {
            'settlement', 'capture' => 'Berhasil Dibayar',
            'pending' => 'Menunggu Pembayaran',
            'expire' => 'Kadaluarsa',
            'cancel' => 'Dibatalkan',
            'deny', 'failure' => 'Gagal',
            default => 'Menunggu Pembayaran',
        };
    }

    private function statuses(): array
    {
        return [
            'Belum Dibayar',
            'Menunggu Pembayaran',
            'Berhasil Dibayar',
            'Gagal',
            'Kadaluarsa',
            'Dibatalkan',
        ];
    }
}
