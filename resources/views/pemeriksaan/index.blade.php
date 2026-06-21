@extends('layouts.app')

@section('content')
    <h1>Pemeriksaan Pasien</h1>
    <div class="panel">
        <table>
            <thead>
                <tr>
                    <th>No Kunjungan</th>
                    <th>Pasien</th>
                    <th>Poli</th>
                    <th>Dokter</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $item->no_kunjungan }}<br><span class="muted">{{ $item->tanggal_kunjungan->format('d/m/Y') }}</span></td>
                    <td>{{ $item->pasien->nama_pasien }}<br><span class="muted">{{ $item->keluhan ?: '-' }}</span></td>
                    <td>{{ $item->poli->nama_poli }}</td>
                    <td>{{ $item->dokter->nama_dokter }}</td>
                    <td><span class="badge">{{ $item->status_kunjungan }}</span></td>
                    <td>
                        <div class="actions">
                            @if ($item->status_kunjungan !== 'Selesai')
                                <a class="btn primary" href="{{ route('pemeriksaan.edit', $item) }}">Periksa</a>
                            @endif
                            @if ($item->pemeriksaan)
                                <a class="btn secondary" href="{{ route('pemeriksaan.show', $item) }}">Ringkasan</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">Belum ada pasien pemeriksaan.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $items->links() }}</div>
    </div>
@endsection
