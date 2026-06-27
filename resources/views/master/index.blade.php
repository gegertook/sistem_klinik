@extends('layouts.app')

@section('content')
    <div class="toolbar">
        <h1>{{ $config['title'] }}</h1>
        <a class="btn primary" href="{{ route('master.create', $resource) }}">Tambah</a>
    </div>

    <div class="panel">
        <form class="toolbar" method="get">
            <input name="q" value="{{ request('q') }}" placeholder="Cari data" style="max-width: 360px;">
            <button class="btn secondary" type="submit">Cari</button>
        </form>
        <table>
            <thead>
                <tr>
                    @foreach ($config['columns'] as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    @foreach ($config['columns'] as $column => $label)
                        @php $value = data_get($item, $column); @endphp
                        <td class="{{ str_contains($column, 'harga') ? 'money' : '' }}">
                            @if ($value instanceof \Carbon\CarbonInterface)
                                {{ $value->format('d/m/Y') }}
                            @elseif (str_contains($column, 'harga'))
                                Rp {{ number_format((int) $value, 0, ',', '.') }}
                            @else
                                {{ $value ?: '-' }}
                            @endif
                        </td>
                    @endforeach
                    <td>
                        <div class="actions">
                            @if ($resource === 'pasien')
                                <a class="btn primary" href="{{ route('kunjungan.create', ['pasien_id' => $item->id]) }}">Daftar Kunjungan</a>
                            @endif
                            <a class="btn secondary" href="{{ route('master.edit', [$resource, $item->id]) }}">Ubah</a>
                            <form action="{{ route('master.destroy', [$resource, $item->id]) }}" method="post" onsubmit="return confirm('Hapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn danger" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ count($config['columns']) + 1 }}" class="muted">Belum ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $items->links() }}</div>
    </div>
@endsection
