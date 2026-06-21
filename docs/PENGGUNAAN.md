# Dokumentasi Penggunaan

## Akun Demo

Semua akun demo menggunakan password:

```text
password
```

| Role | Email |
| --- | --- |
| Admin | admin@example.com |
| Petugas Pendaftaran | pendaftaran@example.com |
| Dokter | dokter@example.com |
| Kasir | kasir@example.com |
| Kepala Klinik | kepala@example.com |
| Pasien | pasien@example.com |
| Farmasi | farmasi@example.com |

## Alur Penggunaan Utama

1. Admin login dan melengkapi data master: poli, layanan, dokter, pegawai, jadwal dokter, pasien, dan user.
2. Petugas pendaftaran membuka menu Pendaftaran, memilih pasien, poli, dokter, tanggal, dan keluhan.
3. Dokter membuka menu Pemeriksaan, mengisi diagnosa, catatan, resep, serta memilih layanan atau tindakan.
4. Sistem otomatis membuat tagihan berdasarkan layanan yang dipilih dokter.
5. Kasir membuka menu Tagihan, melihat detail invoice, lalu memproses pembayaran manual atau membuat transaksi Midtrans.
6. Jika pembayaran lunas, status tagihan berubah menjadi Berhasil Dibayar dan status kunjungan menjadi Selesai.
7. Admin atau kepala klinik membuka laporan kunjungan dan laporan pemasukan berdasarkan periode tanggal.

## Modul Yang Tersedia

- Login dan logout.
- Hak akses berdasarkan role.
- CRUD pasien.
- CRUD dokter.
- CRUD pegawai.
- CRUD poli.
- CRUD layanan.
- Jadwal praktik dokter.
- Pendaftaran kunjungan pasien.
- Pemeriksaan dokter.
- Pembuatan tagihan otomatis.
- Pembayaran manual.
- Pembayaran online Midtrans Sandbox.
- Callback notifikasi Midtrans.
- Invoice dan ringkasan pemeriksaan yang dapat dicetak.
- Dashboard admin.
- Laporan kunjungan.
- Laporan pemasukan.
- Manajemen user.
