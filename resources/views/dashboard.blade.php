@extends('layouts.app')

@section('content')
    <h1>Dashboard</h1>
    <div class="grid grid-5">
        <div class="stat"><span>Jumlah Pasien</span><strong>{{ $stats['pasien'] }}</strong></div>
        <div class="stat"><span>Jumlah Dokter</span><strong>{{ $stats['dokter'] }}</strong></div>
        <div class="stat"><span>Kunjungan Hari Ini</span><strong>{{ $stats['kunjungan_hari_ini'] }}</strong></div>
        <div class="stat"><span>Transaksi</span><strong>{{ $stats['transaksi'] }}</strong></div>
        <div class="stat"><span>Pemasukan Hari Ini</span><strong class="money">Rp {{ number_format($stats['pemasukan_hari_ini'], 0, ',', '.') }}</strong></div>
    </div>

    <div class="grid grid-2" style="margin-top: 18px;">
        <div class="panel">
            <h2>Grafik Kunjungan</h2>
            @php $maxVisit = max(max($chartKunjungan), 1); @endphp
            <div class="mini-bars">
                @foreach ($chartLabels as $i => $label)
                    <div class="mini-bar">
                        <i style="height: {{ max(4, round(($chartKunjungan[$i] / $maxVisit) * 120)) }}px"></i>
                        <span>{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="panel">
            <h2>Grafik Pemasukan</h2>
            @php $maxIncome = max(max($chartPemasukan), 1); @endphp
            <div class="mini-bars">
                @foreach ($chartLabels as $i => $label)
                    <div class="mini-bar alt">
                        <i style="height: {{ max(4, round(($chartPemasukan[$i] / $maxIncome) * 120)) }}px"></i>
                        <span>{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="panel">
            <h2>Kunjungan Terbaru</h2>
            <table>
                <thead><tr><th>No</th><th>Pasien</th><th>Poli</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($latestKunjungan as $item)
                    <tr>
                        <td>{{ $item->no_kunjungan }}</td>
                        <td>{{ $item->pasien->nama_pasien }}</td>
                        <td>{{ $item->poli->nama_poli }}</td>
                        <td><span class="badge">{{ $item->status_kunjungan }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel">
            <h2>Pembayaran Terbaru</h2>
            <table>
                <thead><tr><th>Invoice</th><th>Pasien</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($latestPembayaran as $item)
                    <tr>
                        <td>{{ $item->no_tagihan }}</td>
                        <td>{{ $item->kunjungan->pasien->nama_pasien }}</td>
                        <td class="money">Rp {{ number_format($item->total_tagihan, 0, ',', '.') }}</td>
                        <td><span class="badge {{ $item->status_pembayaran === 'Berhasil Dibayar' ? 'good' : 'warn' }}">{{ $item->status_pembayaran }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
