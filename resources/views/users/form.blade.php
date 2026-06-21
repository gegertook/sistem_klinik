@extends('layouts.app')

@section('content')
    <h1>{{ $user ? 'Ubah User' : 'Tambah User' }}</h1>
    <div class="panel">
        <form method="post" action="{{ $user ? route('users.update', $user) : route('users.store') }}">
            @csrf
            @if ($user) @method('PUT') @endif
            <div class="grid grid-2">
                <div class="field">
                    <label for="name">Nama</label>
                    <input id="name" name="name" value="{{ old('name', $user?->name) }}" required>
                    @error('name') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required>
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $user?->role) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div></div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" {{ $user ? '' : 'required' }}>
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" {{ $user ? '' : 'required' }}>
                </div>
            </div>
            <div class="inline">
                <button class="btn primary" type="submit">Simpan</button>
                <a class="btn secondary" href="{{ route('users.index') }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
