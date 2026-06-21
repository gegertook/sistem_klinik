@extends('layouts.app')

@section('content')
    <div class="toolbar">
        <h1>Invoice {{ $tagihan->no_tagihan }}</h1>
        <button class="btn secondary no-print" onclick="window.print()">Cetak</button>
    </div>

    <div class="panel">
        <div class="grid grid-3">
            <div><span class="muted">Pasien</span><br><strong>{{ $tagihan->kunjungan->pasien->nama_pasien }}</strong><br>{{ $tagihan->kunjungan->pasien->no_rm }}</div>
            <div><span class="muted">Kunjungan</span><br><strong>{{ $tagihan->kunjungan->no_kunjungan }}</strong><br>{{ $tagihan->kunjungan->tanggal_kunjungan->format('d/m/Y') }}</div>
            <div><span class="muted">Status</span><br><span class="badge {{ $tagihan->status_pembayaran === 'Berhasil Dibayar' ? 'good' : 'warn' }}">{{ $tagihan->status_pembayaran }}</span><br>{{ $tagihan->metode_pembayaran ?: '-' }}</div>
        </div>
    </div>

    <div class="panel">
        <h2>Detail Layanan</h2>
        <table>
            <thead><tr><th>Layanan</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
            <tbody>
            @foreach ($tagihan->kunjungan->pemeriksaan->detailLayanan as $detail)
                <tr>
                    <td>{{ $detail->layanan->nama_layanan }}</td>
                    <td class="money">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                    <td>{{ $detail->jumlah }}</td>
                    <td class="money">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Total</th>
                    <th class="money">Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    @if ($tagihan->status_pembayaran !== 'Berhasil Dibayar')
        <div class="grid grid-2 no-print">
            @if (in_array(auth()->user()->role, ['admin', 'kasir'], true))
                <div class="panel">
                    <h2>Pembayaran Manual</h2>
                    <form method="post" action="{{ route('tagihan.manual', $tagihan) }}">
                        @csrf
                        <div class="field">
                            <label for="metode_pembayaran">Metode</label>
                            <select id="metode_pembayaran" name="metode_pembayaran">
                                <option value="Tunai">Tunai</option>
                                <option value="Transfer">Transfer</option>
                                <option value="Debit">Debit</option>
                            </select>
                        </div>
                        <button class="btn primary" type="submit">Proses Manual</button>
                    </form>
                </div>
            @endif
            <div class="panel">
                <h2>Midtrans</h2>
                @if ($tagihan->snap_token && $midtransClientKey)
                    <button id="pay-button" class="btn warning" type="button">Bayar Online</button>
                @else
                    <form method="post" action="{{ route('tagihan.midtrans', $tagihan) }}">
                        @csrf
                        <button class="btn warning" type="submit">Buat Pembayaran Online</button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    <div class="panel">
        <h2>Riwayat Pembayaran</h2>
        <table>
            <thead><tr><th>Order ID</th><th>Tipe</th><th>Status</th><th>Waktu</th><th>Jumlah</th></tr></thead>
            <tbody>
            @forelse ($tagihan->pembayaran as $row)
                <tr>
                    <td>{{ $row->order_id ?: '-' }}</td>
                    <td>{{ $row->payment_type ?: '-' }}</td>
                    <td>{{ $row->transaction_status }}</td>
                    <td>{{ $row->transaction_time?->format('d/m/Y H:i') ?: '-' }}</td>
                    <td class="money">Rp {{ number_format($row->gross_amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Belum ada pembayaran.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($tagihan->snap_token && $midtransClientKey && $tagihan->status_pembayaran !== 'Berhasil Dibayar')
        <script src="{{ $isMidtransProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ $midtransClientKey }}"></script>
        <script>
            document.getElementById('pay-button')?.addEventListener('click', function () {
                window.snap.pay(@json($tagihan->snap_token));
            });
        </script>
    @endif
@endsection
