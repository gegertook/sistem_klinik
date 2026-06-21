@extends('layouts.app')

@section('content')
    <h1>{{ $kunjungan ? 'Ubah Kunjungan' : 'Pendaftaran Kunjungan' }}</h1>
    <div class="panel">
        <form method="post" action="{{ $kunjungan ? route('kunjungan.update', $kunjungan) : route('kunjungan.store') }}">
            @csrf
            @if ($kunjungan) @method('PUT') @endif
            <div class="grid grid-2">
                <div class="field">
                    <label for="pasien_id">Pasien</label>
                    <select id="pasien_id" name="pasien_id" required>
                        <option value="">Pilih pasien</option>
                        @foreach ($pasien as $row)
                            <option value="{{ $row->id }}" @selected((string) old('pasien_id', $kunjungan?->pasien_id) === (string) $row->id)>
                                {{ $row->nama_pasien }} - {{ $row->no_rm }}
                            </option>
                        @endforeach
                    </select>
                    @error('pasien_id') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="tanggal_kunjungan">Tanggal Kunjungan</label>
                    <input id="tanggal_kunjungan" name="tanggal_kunjungan" type="date" value="{{ old('tanggal_kunjungan', $kunjungan?->tanggal_kunjungan?->format('Y-m-d') ?? now()->toDateString()) }}" required>
                    @error('tanggal_kunjungan') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="poli_id">Poli</label>
                    <select id="poli_id" name="poli_id" required>
                        <option value="">Pilih poli</option>
                        @foreach ($poli as $row)
                            <option value="{{ $row->id }}" @selected((string) old('poli_id', $kunjungan?->poli_id) === (string) $row->id)>{{ $row->nama_poli }}</option>
                        @endforeach
                    </select>
                    @error('poli_id') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="dokter_id">Dokter</label>
                    <select id="dokter_id" name="dokter_id" required>
                        <option value="">Pilih dokter</option>
                        @foreach ($dokter as $row)
                            <option value="{{ $row->id }}" @selected((string) old('dokter_id', $kunjungan?->dokter_id) === (string) $row->id)>
                                {{ $row->nama_dokter }} - {{ $row->poli->nama_poli }}
                            </option>
                        @endforeach
                    </select>
                    @error('dokter_id') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="field">
                <label for="keluhan">Keluhan</label>
                <textarea id="keluhan" name="keluhan">{{ old('keluhan', $kunjungan?->keluhan) }}</textarea>
                @error('keluhan') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="inline">
                <button class="btn primary" type="submit">Simpan</button>
                <a class="btn secondary" href="{{ route('kunjungan.index') }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
