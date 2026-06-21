<?php

namespace Database\Seeders;

use App\Models\DetailPemeriksaanLayanan;
use App\Models\Dokter;
use App\Models\JadwalDokter;
use App\Models\Kunjungan;
use App\Models\Layanan;
use App\Models\Pasien;
use App\Models\Pegawai;
use App\Models\Pembayaran;
use App\Models\Pemeriksaan;
use App\Models\Poli;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            ['Admin Klinik', 'admin@example.com', 'admin'],
            ['Petugas Pendaftaran', 'pendaftaran@example.com', 'pendaftaran'],
            ['Dokter Demo', 'dokter@example.com', 'dokter'],
            ['Kasir Klinik', 'kasir@example.com', 'kasir'],
            ['Kepala Klinik', 'kepala@example.com', 'kepala_klinik'],
            ['Pasien Demo', 'pasien@example.com', 'pasien'],
            ['Farmasi Klinik', 'farmasi@example.com', 'farmasi'],
        ];

        foreach ($users as [$name, $email, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'role' => $role, 'password' => 'password']
            );
        }

        $umum = Poli::create(['nama_poli' => 'Poli Umum', 'deskripsi' => 'Layanan pemeriksaan umum']);
        $gigi = Poli::create(['nama_poli' => 'Poli Gigi', 'deskripsi' => 'Layanan kesehatan gigi']);
        $lab = Poli::create(['nama_poli' => 'Laboratorium', 'deskripsi' => 'Layanan pemeriksaan penunjang']);

        $dokterUmum = Dokter::create([
            'poli_id' => $umum->id,
            'nama_dokter' => 'dr. Raka Pratama',
            'kode_dokter' => 'DR2606070001',
            'spesialisasi' => 'Dokter Umum',
            'no_hp' => '081234567890',
        ]);

        $dokterGigi = Dokter::create([
            'poli_id' => $gigi->id,
            'nama_dokter' => 'drg. Maya Lestari',
            'kode_dokter' => 'DR2606070002',
            'spesialisasi' => 'Dokter Gigi',
            'no_hp' => '081298765432',
        ]);

        foreach ([
            [$dokterUmum->id, 'Senin', '08:00', '12:00'],
            [$dokterUmum->id, 'Rabu', '13:00', '17:00'],
            [$dokterGigi->id, 'Selasa', '09:00', '13:00'],
        ] as [$dokterId, $hari, $mulai, $selesai]) {
            JadwalDokter::create([
                'dokter_id' => $dokterId,
                'hari' => $hari,
                'jam_mulai' => $mulai,
                'jam_selesai' => $selesai,
            ]);
        }

        Pegawai::create([
            'nama_pegawai' => 'Nadia Putri',
            'jabatan' => 'Petugas Pendaftaran',
            'no_hp' => '082111223344',
            'email' => 'nadia@klinik.test',
            'alamat' => 'Jl. Mawar No. 10',
        ]);

        Pegawai::create([
            'nama_pegawai' => 'Bima Saputra',
            'jabatan' => 'Kasir',
            'no_hp' => '082155667788',
            'email' => 'bima@klinik.test',
            'alamat' => 'Jl. Melati No. 7',
        ]);

        $konsultasi = Layanan::create(['poli_id' => $umum->id, 'nama_layanan' => 'Konsultasi Umum', 'kategori' => 'Konsultasi', 'harga' => 75000]);
        $tekananDarah = Layanan::create(['poli_id' => $umum->id, 'nama_layanan' => 'Pemeriksaan Tekanan Darah', 'kategori' => 'Pemeriksaan', 'harga' => 25000]);
        Layanan::create(['poli_id' => $umum->id, 'nama_layanan' => 'Suntik Vitamin', 'kategori' => 'Tindakan', 'harga' => 90000]);
        Layanan::create(['poli_id' => $gigi->id, 'nama_layanan' => 'Konsultasi Gigi', 'kategori' => 'Konsultasi', 'harga' => 85000]);
        Layanan::create(['poli_id' => $lab->id, 'nama_layanan' => 'Pemeriksaan Gula Darah', 'kategori' => 'Laboratorium', 'harga' => 45000]);
        Layanan::create(['poli_id' => $lab->id, 'nama_layanan' => 'Pemeriksaan Kolesterol', 'kategori' => 'Laboratorium', 'harga' => 60000]);

        $pasienA = Pasien::create([
            'no_rm' => 'RM2606070001',
            'nik' => '3174010101900001',
            'nama_pasien' => 'Sinta Rahma',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Kenanga No. 2',
            'no_hp' => '081377766655',
            'golongan_darah' => 'O',
            'alergi' => 'Alergi amoksisilin',
        ]);

        Pasien::create([
            'no_rm' => 'RM2606070002',
            'nik' => '3174010202920002',
            'nama_pasien' => 'Arman Hakim',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1992-02-02',
            'alamat' => 'Jl. Anggrek No. 5',
            'no_hp' => '081399988877',
            'golongan_darah' => 'A',
        ]);

        $kunjungan = Kunjungan::create([
            'no_kunjungan' => 'KJ'.now()->format('Ymd').'0001',
            'pasien_id' => $pasienA->id,
            'poli_id' => $umum->id,
            'dokter_id' => $dokterUmum->id,
            'tanggal_kunjungan' => now()->toDateString(),
            'keluhan' => 'Demam dan pusing sejak dua hari.',
            'status_kunjungan' => 'Selesai',
        ]);

        $pemeriksaan = Pemeriksaan::create([
            'kunjungan_id' => $kunjungan->id,
            'dokter_id' => $dokterUmum->id,
            'diagnosa' => 'Infeksi saluran napas atas ringan.',
            'catatan_pemeriksaan' => 'Istirahat cukup dan konsumsi cairan.',
            'resep' => 'Paracetamol 500mg 3x1 setelah makan.',
        ]);

        foreach ([[$konsultasi, 1], [$tekananDarah, 1]] as [$layanan, $jumlah]) {
            DetailPemeriksaanLayanan::create([
                'pemeriksaan_id' => $pemeriksaan->id,
                'layanan_id' => $layanan->id,
                'harga' => $layanan->harga,
                'jumlah' => $jumlah,
                'subtotal' => $layanan->harga * $jumlah,
            ]);
        }

        $tagihan = Tagihan::create([
            'no_tagihan' => 'INV'.now()->format('Ymd').'0001',
            'kunjungan_id' => $kunjungan->id,
            'total_tagihan' => $konsultasi->harga + $tekananDarah->harga,
            'status_pembayaran' => 'Berhasil Dibayar',
            'metode_pembayaran' => 'Tunai',
            'tanggal_tagihan' => now()->toDateString(),
            'tanggal_bayar' => now(),
        ]);

        Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'order_id' => 'MANUAL-SEED',
            'payment_type' => 'Tunai',
            'transaction_status' => 'settlement',
            'transaction_time' => now(),
            'gross_amount' => $tagihan->total_tagihan,
            'response_midtrans' => ['source' => 'seed'],
        ]);
    }
}
