@extends('layouts.app')

@section('content')
    @php
        $pemeriksaan = $kunjungan->pemeriksaan;
        $selected = $pemeriksaan?->detailLayanan?->keyBy('layanan_id') ?? collect();
    @endphp
    <h1>Pemeriksaan Pasien</h1>
    <div class="panel">
        <div class="grid grid-3">
            <div><strong>{{ $kunjungan->pasien->nama_pasien }}</strong><br><span class="muted">{{ $kunjungan->pasien->no_rm }}</span></div>
            <div><strong>{{ $kunjungan->poli->nama_poli }}</strong><br><span class="muted">{{ $kunjungan->dokter->nama_dokter }}</span></div>
            <div><strong>{{ $kunjungan->no_kunjungan }}</strong><br><span class="muted">{{ $kunjungan->tanggal_kunjungan->format('d/m/Y') }}</span></div>
        </div>
    </div>
    <div class="panel">
        <form method="post" action="{{ route('pemeriksaan.update', $kunjungan) }}">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="keluhan">Keluhan</label>
                <textarea id="keluhan" name="keluhan">{{ old('keluhan', $kunjungan->keluhan) }}</textarea>
                @error('keluhan') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="grid grid-2">
                <div class="field">
                    <label for="diagnosa">Diagnosa</label>
                    <textarea id="diagnosa" name="diagnosa" required>{{ old('diagnosa', $pemeriksaan?->diagnosa) }}</textarea>
                    @error('diagnosa') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="resep">Resep / Obat</label>
                    <textarea id="resep" name="resep">{{ old('resep', $pemeriksaan?->resep) }}</textarea>
                    @error('resep') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="field">
                <label for="catatan_pemeriksaan">Catatan Pemeriksaan</label>
                <textarea id="catatan_pemeriksaan" name="catatan_pemeriksaan">{{ old('catatan_pemeriksaan', $pemeriksaan?->catatan_pemeriksaan) }}</textarea>
                @error('catatan_pemeriksaan') <div class="error">{{ $message }}</div> @enderror
            </div>
            <h2>Layanan / Tindakan</h2>
            @error('layanan') <div class="alert error">{{ $message }}</div> @enderror
            <table>
                <thead><tr><th>Pilih</th><th>Layanan</th><th>Kategori</th><th>Harga</th><th>Jumlah</th></tr></thead>
                <tbody>
                @forelse ($layanan as $row)
                    @php $detail = $selected->get($row->id); @endphp
                    <tr>
                        <td><input type="checkbox" name="layanan[{{ $row->id }}][selected]" value="1" style="width: auto;" @checked(old("layanan.$row->id.selected", (bool) $detail))></td>
                        <td>{{ $row->nama_layanan }}</td>
                        <td>{{ $row->kategori }}</td>
                        <td class="money">Rp {{ number_format($row->harga, 0, ',', '.') }}</td>
                        <td><input type="number" min="1" name="layanan[{{ $row->id }}][jumlah]" value="{{ old("layanan.$row->id.jumlah", $detail?->jumlah ?? 1) }}" style="max-width: 90px;"></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Belum ada layanan aktif untuk poli ini.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div class="inline" style="margin-top: 16px;">
                <button class="btn primary" type="submit">Simpan Pemeriksaan</button>
                <a class="btn secondary" href="{{ route('pemeriksaan.index') }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
