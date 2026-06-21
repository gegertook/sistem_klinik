<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;
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

        $orderId = 'MANUAL-'.$tagihan->id.'-'.now()->format('YmdHis');

        $tagihan->update([
            'status_pembayaran' => 'Berhasil Dibayar',
            'metode_pembayaran' => $data['metode_pembayaran'],
            'tanggal_bayar' => now(),
        ]);

        $tagihan->kunjungan()->update(['status_kunjungan' => 'Selesai']);

        Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'order_id' => $orderId,
            'payment_type' => $data['metode_pembayaran'],
            'transaction_status' => 'settlement',
            'transaction_time' => now(),
            'gross_amount' => $tagihan->total_tagihan,
            'response_midtrans' => ['source' => 'manual'],
        ]);

        return back()->with('success', 'Pembayaran manual berhasil diproses.');
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
        $isProduction = filter_var(config('services.midtrans.is_production'), FILTER_VALIDATE_BOOL);
        $baseUrl = $isProduction ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';

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
            'metode_pembayaran' => 'Midtrans',
            'snap_token' => $response->json('token'),
            'midtrans_order_id' => $orderId,
        ]);

        return back()->with('success', 'Transaksi Midtrans dibuat. Tombol bayar online sudah aktif.');
    }

    public function notification(Request $request)
    {
        $payload = $request->all();
        $orderId = $payload['order_id'] ?? null;

        abort_if(blank($orderId), 422, 'order_id tidak ditemukan.');

        $tagihan = Tagihan::where('midtrans_order_id', $orderId)->firstOrFail();
        $systemStatus = $this->mapStatus($payload['transaction_status'] ?? 'pending');

        $tagihan->update([
            'status_pembayaran' => $systemStatus,
            'metode_pembayaran' => $payload['payment_type'] ?? 'Midtrans',
            'tanggal_bayar' => $systemStatus === 'Berhasil Dibayar' ? now() : $tagihan->tanggal_bayar,
        ]);

        if ($systemStatus === 'Berhasil Dibayar') {
            $tagihan->kunjungan()->update(['status_kunjungan' => 'Selesai']);
        }

        Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'order_id' => $orderId,
            'payment_type' => $payload['payment_type'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? 'pending',
            'transaction_time' => $payload['transaction_time'] ?? now(),
            'gross_amount' => (int) ($payload['gross_amount'] ?? $tagihan->total_tagihan),
            'fraud_status' => $payload['fraud_status'] ?? null,
            'response_midtrans' => $payload,
        ]);

        return response()->json(['message' => 'ok']);
    }

    private function mapStatus(string $status): string
    {
        return match ($status) {
            'settlement', 'capture' => 'Berhasil Dibayar',
            'pending' => 'Menunggu Pembayaran',
            'expire' => 'Kadaluarsa',
            'cancel' => 'Dibatalkan',
            'deny' => 'Gagal',
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
