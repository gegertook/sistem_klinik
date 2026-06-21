@extends('layouts.app')

@section('content')
    <div class="toolbar">
        <h1>Laporan Pemasukan</h1>
        <button class="btn secondary no-print" onclick="window.print()">Cetak</button>
    </div>
    <div class="panel no-print">
        <form class="inline" method="get">
            <input type="date" name="from" value="{{ $from }}" style="max-width: 180px;">
            <input type="date" name="to" value="{{ $to }}" style="max-width: 180px;">
            <button class="btn secondary" type="submit">Filter</button>
        </form>
    </div>
    <div class="grid grid-3">
        <div class="stat"><span>Total Pemasukan</span><strong class="money">Rp {{ number_format($total, 0, ',', '.') }}</strong></div>
        @foreach ($byDate as $date => $sum)
            <div class="stat"><span>{{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}</span><strong class="money">Rp {{ number_format($sum, 0, ',', '.') }}</strong></div>
        @endforeach
    </div>
    <div class="panel" style="margin-top: 18px;">
        <table>
            <thead><tr><th>Tanggal Bayar</th><th>No Tagihan</th><th>Pasien</th><th>Poli</th><th>Metode</th><th>Total</th></tr></thead>
            <tbody>
            @forelse ($items as $row)
                <tr>
                    <td>{{ $row->tanggal_bayar?->format('d/m/Y H:i') }}</td>
                    <td>{{ $row->no_tagihan }}</td>
                    <td>{{ $row->kunjungan->pasien->nama_pasien }}</td>
                    <td>{{ $row->kunjungan->poli->nama_poli }}</td>
                    <td>{{ $row->metode_pembayaran }}</td>
                    <td class="money">Rp {{ number_format($row->total_tagihan, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">Tidak ada pemasukan pada periode ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
