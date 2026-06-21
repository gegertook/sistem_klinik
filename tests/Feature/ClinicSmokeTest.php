<?php

namespace Tests\Feature;

use App\Models\Kunjungan;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_roles_can_open_their_main_pages(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $pendaftaran = User::where('email', 'pendaftaran@example.com')->firstOrFail();
        $dokter = User::where('email', 'dokter@example.com')->firstOrFail();
        $kasir = User::where('email', 'kasir@example.com')->firstOrFail();
        $kepala = User::where('email', 'kepala@example.com')->firstOrFail();

        $this->actingAs($admin)->get('/dashboard')->assertOk();
        $this->actingAs($admin)->get('/master/pasien')->assertOk();
        $this->actingAs($pendaftaran)->get('/kunjungan')->assertOk();
        $this->actingAs($dokter)->get('/pemeriksaan')->assertOk();
        $this->actingAs($kasir)->get('/tagihan')->assertOk();
        $this->actingAs($kepala)->get('/laporan/kunjungan')->assertOk();
    }

    public function test_invoice_and_examination_summary_render(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $tagihan = Tagihan::firstOrFail();
        $kunjungan = Kunjungan::firstOrFail();

        $this->actingAs($admin)->get(route('tagihan.show', $tagihan))->assertOk();
        $this->actingAs($admin)->get(route('pemeriksaan.show', $kunjungan))->assertOk();
    }
}
