<?php

namespace Tests\Feature;

use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_midtrans_notification_marks_invoice_as_paid(): void
    {
        $this->seed();
        config(['services.midtrans.server_key' => 'server-test']);

        $tagihan = Tagihan::firstOrFail();
        $tagihan->update([
            'status_pembayaran' => 'Menunggu Pembayaran',
            'metode_pembayaran' => 'Mandiri',
            'midtrans_order_id' => 'MID-NOTIF-TEST',
            'snap_token' => 'snap-token-test',
            'tanggal_bayar' => null,
        ]);
        $tagihan->kunjungan()->update(['status_kunjungan' => 'Menunggu Pembayaran']);

        $payload = [
            'order_id' => 'MID-NOTIF-TEST',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'transaction_time' => '2026-06-27 10:00:00',
        ];
        $payload['signature_key'] = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'server-test');

        $this->postJson(route('midtrans.notification'), $payload)
            ->assertOk()
            ->assertJson(['message' => 'ok']);

        $tagihan->refresh();

        $this->assertSame('Berhasil Dibayar', $tagihan->status_pembayaran);
        $this->assertSame('Mandiri - BANK TRANSFER', $tagihan->metode_pembayaran);
        $this->assertNotNull($tagihan->tanggal_bayar);
        $this->assertSame('Selesai', $tagihan->kunjungan->fresh()->status_kunjungan);
        $this->assertDatabaseHas('pembayaran', [
            'tagihan_id' => $tagihan->id,
            'order_id' => 'MID-NOTIF-TEST',
            'transaction_status' => 'settlement',
        ]);
    }

    public function test_sync_midtrans_status_marks_invoice_as_paid(): void
    {
        $this->seed();
        config([
            'services.midtrans.server_key' => 'server-test',
            'services.midtrans.is_production' => false,
        ]);

        $kasir = User::where('email', 'kasir@example.com')->firstOrFail();
        $tagihan = Tagihan::firstOrFail();
        $tagihan->update([
            'status_pembayaran' => 'Menunggu Pembayaran',
            'metode_pembayaran' => 'Mandiri',
            'midtrans_order_id' => 'MID-SYNC-TEST',
            'snap_token' => 'snap-token-test',
            'tanggal_bayar' => null,
        ]);
        $tagihan->kunjungan()->update(['status_kunjungan' => 'Menunggu Pembayaran']);

        Http::fake([
            'https://app.sandbox.midtrans.com/v2/MID-SYNC-TEST/status' => Http::response([
                'order_id' => 'MID-SYNC-TEST',
                'status_code' => '200',
                'gross_amount' => '100000.00',
                'transaction_status' => 'settlement',
                'payment_type' => 'qris',
                'transaction_time' => '2026-06-27 10:05:00',
            ], 200),
        ]);

        $this->actingAs($kasir)
            ->postJson(route('tagihan.midtrans.sync', $tagihan))
            ->assertOk()
            ->assertJson([
                'message' => 'ok',
                'status_pembayaran' => 'Berhasil Dibayar',
            ]);

        $tagihan->refresh();

        $this->assertSame('Berhasil Dibayar', $tagihan->status_pembayaran);
        $this->assertSame('Mandiri - QRIS', $tagihan->metode_pembayaran);
        $this->assertSame('Selesai', $tagihan->kunjungan->fresh()->status_kunjungan);
        $this->assertDatabaseHas('pembayaran', [
            'tagihan_id' => $tagihan->id,
            'order_id' => 'MID-SYNC-TEST',
            'transaction_status' => 'settlement',
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://app.sandbox.midtrans.com/v2/MID-SYNC-TEST/status');
    }

    public function test_bpjs_payment_marks_invoice_as_paid(): void
    {
        $this->seed();

        $kasir = User::where('email', 'kasir@example.com')->firstOrFail();
        $tagihan = Tagihan::firstOrFail();
        $tagihan->update([
            'status_pembayaran' => 'Belum Dibayar',
            'metode_pembayaran' => null,
            'midtrans_order_id' => 'MID-WILL-BE-CLEARED',
            'snap_token' => 'snap-token-test',
            'tanggal_bayar' => null,
        ]);
        $tagihan->kunjungan()->update(['status_kunjungan' => 'Menunggu Pembayaran']);

        $this->actingAs($kasir)
            ->post(route('tagihan.bpjs', $tagihan))
            ->assertRedirect();

        $tagihan->refresh();

        $this->assertSame('Berhasil Dibayar', $tagihan->status_pembayaran);
        $this->assertSame('BPJS', $tagihan->metode_pembayaran);
        $this->assertNull($tagihan->midtrans_order_id);
        $this->assertNull($tagihan->snap_token);
        $this->assertSame('Selesai', $tagihan->kunjungan->fresh()->status_kunjungan);
        $this->assertDatabaseHas('pembayaran', [
            'tagihan_id' => $tagihan->id,
            'payment_type' => 'BPJS',
            'transaction_status' => 'settlement',
        ]);
    }
}
