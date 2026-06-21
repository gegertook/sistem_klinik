@extends('layouts.app')

@section('content')
    <div class="toolbar">
        <h1>Pendaftaran Kunjungan</h1>
        <a class="btn primary" href="{{ route('kunjungan.create') }}">Daftar Kunjungan</a>
    </div>

    <div class="panel">
        <form class="toolbar" method="get">
            <div class="inline">
                <input name="q" value="{{ request('q') }}" placeholder="Cari pasien / no kunjungan" style="max-width: 300px;">
                <select name="status" style="max-width: 240px;">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <button class="btn secondary" type="submit">Filter</button>
            </div>
        </form>
        <table>
            <thead>
                <tr>
                    <th>No Kunjungan</th>
                    <th>Tanggal</th>
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
                    <td>{{ $item->no_kunjungan }}</td>
                    <td>{{ $item->tanggal_kunjungan->format('d/m/Y') }}</td>
                    <td>{{ $item->pasien->nama_pasien }}<br><span class="muted">{{ $item->pasien->no_rm }}</span></td>
                    <td>{{ $item->poli->nama_poli }}</td>
                    <td>{{ $item->dokter->nama_dokter }}</td>
                    <td><span class="badge">{{ $item->status_kunjungan }}</span></td>
                    <td>
                        <div class="actions">
                            @if (! $item->pemeriksaan)
                                <a class="btn secondary" href="{{ route('kunjungan.edit', $item) }}">Ubah</a>
                                <form action="{{ route('kunjungan.destroy', $item) }}" method="post" onsubmit="return confirm('Hapus kunjungan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn danger" type="submit">Hapus</button>
                                </form>
                            @endif
                            @if ($item->tagihan)
                                <a class="btn secondary" href="{{ route('tagihan.show', $item->tagihan) }}">Tagihan</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Belum ada kunjungan.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $items->links() }}</div>
    </div>
@endsection
