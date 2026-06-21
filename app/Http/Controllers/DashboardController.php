<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $today = Carbon::today();
        $period = CarbonPeriod::create($today->copy()->subDays(6), $today);
        $dates = collect($period)->map(fn (Carbon $date) => $date->toDateString());

        $kunjunganByDate = Kunjungan::query()
            ->selectRaw('DATE(tanggal_kunjungan) as tanggal, COUNT(*) as total')
            ->whereDate('tanggal_kunjungan', '>=', $dates->first())
            ->groupBy(DB::raw('DATE(tanggal_kunjungan)'))
            ->pluck('total', 'tanggal');

        $pemasukanByDate = Tagihan::query()
            ->selectRaw('DATE(tanggal_bayar) as tanggal, SUM(total_tagihan) as total')
            ->where('status_pembayaran', 'Berhasil Dibayar')
            ->whereDate('tanggal_bayar', '>=', $dates->first())
            ->groupBy(DB::raw('DATE(tanggal_bayar)'))
            ->pluck('total', 'tanggal');

        return view('dashboard', [
            'stats' => [
                'pasien' => Pasien::count(),
                'dokter' => Dokter::count(),
                'kunjungan_hari_ini' => Kunjungan::whereDate('tanggal_kunjungan', $today)->count(),
                'transaksi' => Pembayaran::count(),
                'pemasukan_hari_ini' => Tagihan::where('status_pembayaran', 'Berhasil Dibayar')
                    ->whereDate('tanggal_bayar', $today)
                    ->sum('total_tagihan'),
            ],
            'chartLabels' => $dates->map(fn ($date) => Carbon::parse($date)->format('d M'))->all(),
            'chartKunjungan' => $dates->map(fn ($date) => (int) ($kunjunganByDate[$date] ?? 0))->all(),
            'chartPemasukan' => $dates->map(fn ($date) => (int) ($pemasukanByDate[$date] ?? 0))->all(),
            'latestKunjungan' => Kunjungan::with(['pasien', 'poli', 'dokter'])->latest()->limit(5)->get(),
            'latestPembayaran' => Tagihan::with('kunjungan.pasien')->latest()->limit(5)->get(),
        ]);
    }
}
