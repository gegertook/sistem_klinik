@extends('layouts.app')

@section('content')
    <div class="toolbar">
        <h1>Laporan Kunjungan</h1>
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
        <div class="stat"><span>Total Kunjungan</span><strong>{{ $total }}</strong></div>
        @foreach ($byStatus as $status => $count)
            <div class="stat"><span>{{ $status }}</span><strong>{{ $count }}</strong></div>
        @endforeach
    </div>
    <div class="panel" style="margin-top: 18px;">
        <table>
            <thead><tr><th>Tanggal</th><th>No Kunjungan</th><th>Pasien</th><th>Poli</th><th>Dokter</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($items as $row)
                <tr>
                    <td>{{ $row->tanggal_kunjungan->format('d/m/Y') }}</td>
                    <td>{{ $row->no_kunjungan }}</td>
                    <td>{{ $row->pasien->nama_pasien }}</td>
                    <td>{{ $row->poli->nama_poli }}</td>
                    <td>{{ $row->dokter->nama_dokter }}</td>
                    <td><span class="badge">{{ $row->status_kunjungan }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">Tidak ada data pada periode ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
