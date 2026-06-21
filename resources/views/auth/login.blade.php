@extends('layouts.app')

@section('content')
    <div class="login-page">
        <div class="login-card">
            <h1>Sistem Informasi Klinik</h1>
            <form action="{{ route('login.store') }}" method="post">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autofocus required>
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>
                <label class="inline" style="font-weight: 500; margin-bottom: 16px;">
                    <input name="remember" type="checkbox" value="1" style="width: auto;">
                    <span>Ingat saya</span>
                </label>
                <button class="btn primary" type="submit" style="width: 100%;">Login</button>
            </form>
        </div>
    </div>
@endsection
