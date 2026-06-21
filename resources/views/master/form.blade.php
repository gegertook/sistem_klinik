@extends('layouts.app')

@section('content')
    <h1>{{ $item ? 'Ubah '.$config['singular'] : 'Tambah '.$config['singular'] }}</h1>
    <div class="panel">
        <form method="post" action="{{ $item ? route('master.update', [$resource, $item->id]) : route('master.store', $resource) }}">
            @csrf
            @if ($item) @method('PUT') @endif
            <div class="grid grid-2">
                @foreach ($config['fields'] as $field)
                    @php
                        $name = $field['name'];
                        $raw = old($name, $item?->{$name} ?? ($defaults[$name] ?? ''));
                        $value = $raw instanceof \Carbon\CarbonInterface ? $raw->format('Y-m-d') : $raw;
                        if ($field['type'] === 'time' && is_string($value)) { $value = substr($value, 0, 5); }
                    @endphp
                    <div class="field">
                        <label for="{{ $name }}">{{ $field['label'] }}</label>
                        @if ($field['type'] === 'textarea')
                            <textarea id="{{ $name }}" name="{{ $name }}">{{ $value }}</textarea>
                        @elseif ($field['type'] === 'select')
                            <select id="{{ $name }}" name="{{ $name }}">
                                @foreach (($field['options'] ?? []) as $key => $label)
                                    <option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @else
                            <input id="{{ $name }}" name="{{ $name }}" type="{{ $field['type'] }}" value="{{ $value }}">
                        @endif
                        @error($name) <div class="error">{{ $message }}</div> @enderror
                    </div>
                @endforeach
            </div>
            <div class="inline">
                <button class="btn primary" type="submit">Simpan</button>
                <a class="btn secondary" href="{{ route('master.index', $resource) }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
