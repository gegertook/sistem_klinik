@extends('layouts.app')

@section('content')
    <h1>Tagihan</h1>
    <div class="panel">
        <form class="toolbar" method="get">
            <div class="inline">
                <input name="q" value="{{ request('q') }}" placeholder="Cari invoice / pasien" style="max-width: 300px;">
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
                    <th>No Tagihan</th>
                    <th>Pasien</th>
                    <th>Kunjungan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $item->no_tagihan }}<br><span class="muted">{{ $item->tanggal_tagihan->format('d/m/Y') }}</span></td>
                    <td>{{ $item->kunjungan->pasien->nama_pasien }}<br><span class="muted">{{ $item->kunjungan->pasien->no_rm }}</span></td>
                    <td>{{ $item->kunjungan->no_kunjungan }}<br><span class="muted">{{ $item->kunjungan->poli->nama_poli }}</span></td>
                    <td class="money">Rp {{ number_format($item->total_tagihan, 0, ',', '.') }}</td>
                    <td><span class="badge {{ $item->status_pembayaran === 'Berhasil Dibayar' ? 'good' : ($item->status_pembayaran === 'Belum Dibayar' ? 'warn' : '') }}">{{ $item->status_pembayaran }}</span></td>
                    <td><a class="btn secondary" href="{{ route('tagihan.show', $item) }}">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">Belum ada tagihan.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $items->links() }}</div>
    </div>
@endsection
