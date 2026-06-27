<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Layanan;
use App\Models\Pasien;
use App\Models\Poli;
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

    public function test_doctor_is_redirected_to_examination_summary_after_saving_examination(): void
    {
        $this->seed();

        $doctorUser = User::where('email', 'dokter@example.com')->firstOrFail();
        $pasien = Pasien::firstOrFail();
        $poli = Poli::where('nama_poli', 'Poli Umum')->firstOrFail();
        $dokter = Dokter::where('poli_id', $poli->id)->firstOrFail();
        $layanan = Layanan::where('poli_id', $poli->id)->firstOrFail();

        $kunjungan = Kunjungan::create([
            'no_kunjungan' => 'KJ'.now()->format('Ymd').'0099',
            'pasien_id' => $pasien->id,
            'poli_id' => $poli->id,
            'dokter_id' => $dokter->id,
            'tanggal_kunjungan' => now()->toDateString(),
            'keluhan' => 'Batuk dan demam.',
            'status_kunjungan' => 'Menunggu Pemeriksaan',
        ]);

        $this->actingAs($doctorUser)
            ->put(route('pemeriksaan.update', $kunjungan), [
                'keluhan' => 'Batuk dan demam.',
                'diagnosa' => 'ISPA ringan.',
                'catatan_pemeriksaan' => 'Istirahat cukup.',
                'resep' => 'Paracetamol.',
                'layanan' => [
                    $layanan->id => [
                        'selected' => '1',
                        'jumlah' => '1',
                    ],
                ],
            ])
            ->assertRedirect(route('pemeriksaan.show', $kunjungan));

        $this->assertDatabaseHas('tagihan', [
            'kunjungan_id' => $kunjungan->id,
            'status_pembayaran' => 'Belum Dibayar',
        ]);
    }

    public function test_registration_user_continues_to_visit_registration_after_creating_patient(): void
    {
        $this->seed();

        $pendaftaran = User::where('email', 'pendaftaran@example.com')->firstOrFail();

        $response = $this->actingAs($pendaftaran)
            ->post(route('master.store', 'pasien'), [
                'nik' => '3174012706260001',
                'nama_pasien' => 'Pasien Baru',
                'jenis_kelamin' => 'Perempuan',
                'tanggal_lahir' => '2001-01-01',
                'alamat' => 'Jl. Baru No. 1',
            ]);

        $pasien = Pasien::where('nik', '3174012706260001')->firstOrFail();

        $response->assertRedirect(route('kunjungan.create', ['pasien_id' => $pasien->id]));

        $this->actingAs($pendaftaran)
            ->get(route('kunjungan.create', ['pasien_id' => $pasien->id]))
            ->assertOk()
            ->assertSee('value="'.$pasien->id.'" selected', false);
    }
}
