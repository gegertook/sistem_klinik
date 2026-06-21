<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Sistem Klinik') }}</title>
    <style>
        :root {
            --bg: #f6f8fb;
            --panel: #ffffff;
            --ink: #1d2733;
            --muted: #6b7280;
            --line: #d8dee8;
            --primary: #166c7d;
            --primary-dark: #0f4d5a;
            --accent: #c56b2c;
            --success: #1f8a5b;
            --danger: #b83232;
            --warning: #b7791f;
            --radius: 8px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 14px;
        }
        a { color: inherit; text-decoration: none; }
        .shell { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
        .sidebar {
            background: #12313a;
            color: #edf7f9;
            padding: 22px 18px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        .brand { font-size: 18px; font-weight: 800; margin-bottom: 4px; }
        .role { color: #a9cbd3; font-size: 12px; margin-bottom: 22px; }
        .nav { display: grid; gap: 6px; }
        .nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 6px;
            padding: 10px 12px;
            color: #d8eef2;
        }
        .nav a.active, .nav a:hover { background: #1f5966; color: white; }
        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #e7a05a;
            flex: 0 0 auto;
        }
        .main { min-width: 0; }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 26px;
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, .86);
            position: sticky;
            top: 0;
            z-index: 5;
            backdrop-filter: blur(10px);
        }
        .page { padding: 26px; }
        h1 { font-size: 26px; margin: 0 0 18px; letter-spacing: 0; }
        h2 { font-size: 18px; margin: 0 0 14px; letter-spacing: 0; }
        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 18px;
            margin-bottom: 18px;
        }
        .grid { display: grid; gap: 16px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        .stat {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 16px;
        }
        .stat span { color: var(--muted); font-size: 12px; }
        .stat strong { display: block; font-size: 24px; margin-top: 6px; }
        .toolbar {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .inline { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; border-bottom: 1px solid var(--line); padding: 11px 10px; vertical-align: top; }
        th { color: #435363; font-size: 12px; text-transform: uppercase; background: #f1f5f9; }
        tr:hover td { background: #fafcfe; }
        label { display: block; color: #374151; font-weight: 700; margin-bottom: 6px; }
        input, select, textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 11px;
            background: white;
            color: var(--ink);
            font: inherit;
        }
        textarea { min-height: 92px; resize: vertical; }
        .field { margin-bottom: 14px; }
        .error { color: var(--danger); font-size: 12px; margin-top: 5px; }
        .alert {
            border-radius: 6px;
            padding: 12px 14px;
            margin-bottom: 16px;
            border: 1px solid;
        }
        .alert.success { color: #14543a; background: #e7f6ef; border-color: #b9e4ce; }
        .alert.error { color: #842525; background: #fdeaea; border-color: #f5b8b8; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid transparent;
            border-radius: 6px;
            padding: 9px 12px;
            min-height: 38px;
            cursor: pointer;
            font-weight: 700;
            font: inherit;
            white-space: nowrap;
        }
        .btn.primary { background: var(--primary); color: white; }
        .btn.primary:hover { background: var(--primary-dark); }
        .btn.secondary { background: white; color: var(--ink); border-color: var(--line); }
        .btn.warning { background: var(--accent); color: white; }
        .btn.danger { background: var(--danger); color: white; }
        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 4px 9px;
            font-size: 12px;
            font-weight: 800;
            background: #e8eef5;
            color: #344256;
        }
        .badge.good { background: #dff3ea; color: #176443; }
        .badge.warn { background: #fff1d8; color: #835410; }
        .badge.bad { background: #fae2e2; color: #942626; }
        .muted { color: var(--muted); }
        .money { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .actions { display: flex; gap: 6px; flex-wrap: wrap; }
        .pagination { margin-top: 14px; }
        .login-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: linear-gradient(135deg, #e8f3f5 0%, #f7efe6 100%);
        }
        .login-card {
            width: min(430px, 100%);
            background: white;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 26px;
            box-shadow: 0 16px 40px rgba(18, 49, 58, .14);
        }
        .mini-bars { display: flex; align-items: end; gap: 8px; height: 150px; padding-top: 8px; }
        .mini-bar { flex: 1; display: grid; align-content: end; gap: 6px; min-width: 0; }
        .mini-bar i { display: block; background: var(--primary); border-radius: 4px 4px 0 0; min-height: 4px; }
        .mini-bar.alt i { background: var(--accent); }
        .mini-bar span { color: var(--muted); font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-align: center; }
        @media (max-width: 900px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { position: static; height: auto; }
            .grid-2, .grid-3, .grid-5 { grid-template-columns: 1fr; }
            .page, .topbar { padding-left: 16px; padding-right: 16px; }
        }
        @media print {
            .sidebar, .topbar, .no-print, .toolbar { display: none !important; }
            .shell { display: block; }
            body { background: white; }
            .page { padding: 0; }
            .panel { border: none; padding: 0; }
        }
    </style>
</head>
<body>
@auth
    @php
        $roleLabels = [
            'admin' => 'Admin',
            'pendaftaran' => 'Petugas Pendaftaran',
            'dokter' => 'Dokter',
            'kasir' => 'Kasir',
            'kepala_klinik' => 'Kepala Klinik',
            'pasien' => 'Pasien',
            'farmasi' => 'Farmasi',
        ];
        $role = auth()->user()->role;
        $nav = [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'roles' => ['admin', 'kepala_klinik']],
            ['label' => 'Data Poli', 'url' => route('master.index', 'poli'), 'roles' => ['admin']],
            ['label' => 'Data Layanan', 'url' => route('master.index', 'layanan'), 'roles' => ['admin']],
            ['label' => 'Data Dokter', 'url' => route('master.index', 'dokter'), 'roles' => ['admin']],
            ['label' => 'Data Pegawai', 'url' => route('master.index', 'pegawai'), 'roles' => ['admin']],
            ['label' => 'Jadwal Dokter', 'url' => route('master.index', 'jadwal'), 'roles' => ['admin']],
            ['label' => 'Data Pasien', 'url' => route('master.index', 'pasien'), 'roles' => ['admin', 'pendaftaran']],
            ['label' => 'Pendaftaran', 'url' => route('kunjungan.index'), 'roles' => ['admin', 'pendaftaran']],
            ['label' => 'Pemeriksaan', 'url' => route('pemeriksaan.index'), 'roles' => ['admin', 'dokter']],
            ['label' => 'Tagihan', 'url' => route('tagihan.index'), 'roles' => ['admin', 'kasir', 'pasien']],
            ['label' => 'Laporan Kunjungan', 'url' => route('laporan.kunjungan'), 'roles' => ['admin', 'kepala_klinik']],
            ['label' => 'Laporan Pemasukan', 'url' => route('laporan.pemasukan'), 'roles' => ['admin', 'kepala_klinik']],
            ['label' => 'Manajemen User', 'url' => route('users.index'), 'roles' => ['admin']],
        ];
    @endphp
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">Sistem Klinik</div>
            <div class="role">{{ $roleLabels[$role] ?? $role }} · {{ auth()->user()->name }}</div>
            <nav class="nav">
                @foreach ($nav as $item)
                    @if (in_array($role, $item['roles'], true))
                        <a href="{{ $item['url'] }}" class="{{ url()->current() === $item['url'] ? 'active' : '' }}">
                            <span class="dot"></span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>
        </aside>
        <main class="main">
            <div class="topbar">
                <strong>{{ config('app.name', 'Sistem Klinik') }}</strong>
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button class="btn secondary" type="submit">Logout</button>
                </form>
            </div>
            <div class="page">
                @if (session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert error">{{ session('error') }}</div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
@else
    @yield('content')
@endauth
</body>
</html>
