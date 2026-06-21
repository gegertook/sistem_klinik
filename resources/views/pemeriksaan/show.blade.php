@extends('layouts.app')

@section('content')
    <div class="toolbar">
        <h1>Ringkasan Pemeriksaan</h1>
        <button class="btn secondary no-print" onclick="window.print()">Cetak</button>
    </div>
    <div class="panel">
        <div class="grid grid-3">
            <div><span class="muted">Pasien</span><br><strong>{{ $kunjungan->pasien->nama_pasien }}</strong><br>{{ $kunjungan->pasien->no_rm }}</div>
            <div><span class="muted">Kunjungan</span><br><strong>{{ $kunjungan->no_kunjungan }}</strong><br>{{ $kunjungan->tanggal_kunjungan->format('d/m/Y') }}</div>
            <div><span class="muted">Dokter</span><br><strong>{{ $kunjungan->dokter->nama_dokter }}</strong><br>{{ $kunjungan->poli->nama_poli }}</div>
        </div>
    </div>
    <div class="panel">
        <h2>Hasil Pemeriksaan</h2>
        <p><strong>Keluhan:</strong><br>{{ $kunjungan->keluhan ?: '-' }}</p>
        <p><strong>Diagnosa:</strong><br>{{ $kunjungan->pemeriksaan->diagnosa }}</p>
        <p><strong>Catatan:</strong><br>{{ $kunjungan->pemeriksaan->catatan_pemeriksaan ?: '-' }}</p>
        <p><strong>Resep:</strong><br>{{ $kunjungan->pemeriksaan->resep ?: '-' }}</p>
    </div>
    <div class="panel">
        <h2>Layanan</h2>
        <table>
            <thead><tr><th>Layanan</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
            <tbody>
            @foreach ($kunjungan->pemeriksaan->detailLayanan as $detail)
                <tr>
                    <td>{{ $detail->layanan->nama_layanan }}</td>
                    <td class="money">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                    <td>{{ $detail->jumlah }}</td>
                    <td class="money">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
