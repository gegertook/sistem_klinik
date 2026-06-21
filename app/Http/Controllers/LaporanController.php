<?php

namespace App\Http\Controllers;

use App\Models\Kunjungan;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LaporanController extends Controller
{
    public function kunjungan(Request $request)
    {
        [$from, $to] = $this->dates($request);

        $items = Kunjungan::with(['pasien', 'poli', 'dokter'])
            ->whereBetween('tanggal_kunjungan', [$from, $to])
            ->latest()
            ->get();

        return view('laporan.kunjungan', [
            'items' => $items,
            'from' => $from,
            'to' => $to,
            'total' => $items->count(),
            'byStatus' => $items->groupBy('status_kunjungan')->map->count(),
        ]);
    }

    public function pemasukan(Request $request)
    {
        [$from, $to] = $this->dates($request);

        $items = Tagihan::with(['kunjungan.pasien', 'kunjungan.poli'])
            ->where('status_pembayaran', 'Berhasil Dibayar')
            ->whereDate('tanggal_bayar', '>=', $from)
            ->whereDate('tanggal_bayar', '<=', $to)
            ->latest('tanggal_bayar')
            ->get();

        return view('laporan.pemasukan', [
            'items' => $items,
            'from' => $from,
            'to' => $to,
            'total' => $items->sum('total_tagihan'),
            'byDate' => $items->groupBy(fn (Tagihan $tagihan) => $tagihan->tanggal_bayar?->toDateString())
                ->map(fn ($rows) => $rows->sum('total_tagihan')),
        ]);
    }

    private function dates(Request $request): array
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        return [
            Carbon::parse($from)->toDateString(),
            Carbon::parse($to)->toDateString(),
        ];
    }
}
