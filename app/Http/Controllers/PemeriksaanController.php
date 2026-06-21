<?php

namespace App\Http\Controllers;

use App\Models\DetailPemeriksaanLayanan;
use App\Models\Kunjungan;
use App\Models\Layanan;
use App\Models\Pemeriksaan;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PemeriksaanController extends Controller
{
    public function index()
    {
        return view('pemeriksaan.index', [
            'items' => Kunjungan::with(['pasien', 'poli', 'dokter', 'pemeriksaan', 'tagihan'])
                ->whereIn('status_kunjungan', [
                    'Menunggu Pemeriksaan',
                    'Sedang Diperiksa',
                    'Selesai Diperiksa',
                    'Menunggu Pembayaran',
                    'Selesai',
                ])
                ->latest()
                ->paginate(10),
        ]);
    }

    public function edit(Kunjungan $kunjungan)
    {
        $kunjungan->load(['pasien', 'poli', 'dokter', 'pemeriksaan.detailLayanan']);

        if ($kunjungan->status_kunjungan === 'Menunggu Pemeriksaan') {
            $kunjungan->update(['status_kunjungan' => 'Sedang Diperiksa']);
        }

        return view('pemeriksaan.form', [
            'kunjungan' => $kunjungan->fresh(['pasien', 'poli', 'dokter', 'pemeriksaan.detailLayanan']),
            'layanan' => Layanan::where('poli_id', $kunjungan->poli_id)
                ->where('status', 'Aktif')
                ->orderBy('nama_layanan')
                ->get(),
        ]);
    }

    public function update(Request $request, Kunjungan $kunjungan)
    {
        abort_if($kunjungan->tagihan?->status_pembayaran === 'Berhasil Dibayar', 422, 'Pemeriksaan tidak dapat diubah setelah tagihan lunas.');

        $data = $request->validate([
            'keluhan' => ['nullable', 'string'],
            'diagnosa' => ['required', 'string'],
            'catatan_pemeriksaan' => ['nullable', 'string'],
            'resep' => ['nullable', 'string'],
            'layanan' => ['array'],
        ]);

        $selectedLayanan = collect($request->input('layanan', []))
            ->filter(fn (array $row) => isset($row['selected']))
            ->map(fn (array $row, int|string $id) => [
                'layanan_id' => (int) $id,
                'jumlah' => max(1, (int) ($row['jumlah'] ?? 1)),
            ]);

        if ($selectedLayanan->isEmpty()) {
            return back()->withErrors(['layanan' => 'Pilih minimal satu layanan atau tindakan.'])->withInput();
        }

        DB::transaction(function () use ($kunjungan, $data, $selectedLayanan) {
            $kunjungan->update([
                'keluhan' => $data['keluhan'] ?? $kunjungan->keluhan,
                'status_kunjungan' => 'Menunggu Pembayaran',
            ]);

            $pemeriksaan = Pemeriksaan::updateOrCreate(
                ['kunjungan_id' => $kunjungan->id],
                [
                    'dokter_id' => $kunjungan->dokter_id,
                    'diagnosa' => $data['diagnosa'],
                    'catatan_pemeriksaan' => $data['catatan_pemeriksaan'] ?? null,
                    'resep' => $data['resep'] ?? null,
                ]
            );

            $pemeriksaan->detailLayanan()->delete();

            $total = 0;
            $layananById = Layanan::whereIn('id', $selectedLayanan->pluck('layanan_id'))->get()->keyBy('id');

            foreach ($selectedLayanan as $row) {
                $layanan = $layananById[$row['layanan_id']];
                $subtotal = $layanan->harga * $row['jumlah'];
                $total += $subtotal;

                DetailPemeriksaanLayanan::create([
                    'pemeriksaan_id' => $pemeriksaan->id,
                    'layanan_id' => $layanan->id,
                    'harga' => $layanan->harga,
                    'jumlah' => $row['jumlah'],
                    'subtotal' => $subtotal,
                ]);
            }

            $tagihan = Tagihan::firstOrNew(['kunjungan_id' => $kunjungan->id]);
            $tagihan->fill([
                'no_tagihan' => $tagihan->no_tagihan ?: $this->nextInvoiceNumber(),
                'total_tagihan' => $total,
                'status_pembayaran' => $tagihan->status_pembayaran ?: 'Belum Dibayar',
                'tanggal_tagihan' => $tagihan->tanggal_tagihan ?: now()->toDateString(),
            ])->save();
        });

        return redirect()->route('tagihan.index')->with('success', 'Pemeriksaan disimpan dan tagihan berhasil dibuat.');
    }

    public function show(Kunjungan $kunjungan)
    {
        $kunjungan->load(['pasien', 'poli', 'dokter', 'pemeriksaan.detailLayanan.layanan', 'tagihan']);
        abort_unless($kunjungan->pemeriksaan, 404);

        return view('pemeriksaan.show', compact('kunjungan'));
    }

    private function nextInvoiceNumber(): string
    {
        $prefix = 'INV'.now()->format('Ymd');
        $next = Tagihan::where('no_tagihan', 'like', $prefix.'%')->count() + 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
