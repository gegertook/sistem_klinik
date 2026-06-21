@extends('layouts.app')

@section('content')
    <div class="toolbar">
        <h1>Manajemen User</h1>
        <a class="btn primary" href="{{ route('users.create') }}">Tambah User</a>
    </div>
    <div class="panel">
        <table>
            <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge">{{ $roles[$user->role] ?? $user->role }}</span></td>
                    <td>
                        <div class="actions">
                            <a class="btn secondary" href="{{ route('users.edit', $user) }}">Ubah</a>
                            @if (auth()->id() !== $user->id)
                                <form method="post" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn danger" type="submit">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $users->links() }}</div>
    </div>
@endsection
