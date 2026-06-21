# Laporan Projek Singkat

## Identitas Projek

Nama projek: Sistem Informasi Klinik Terintegrasi  
Platform: Web Based Application  
Framework: Laravel  
Database: MySQL  
Payment gateway: Midtrans Sandbox

## Ringkasan

Sistem Informasi Klinik Terintegrasi dibuat untuk mengelola proses layanan klinik dari data master, pendaftaran pasien, pemeriksaan dokter, pembuatan tagihan, pembayaran, dashboard, sampai laporan. Sistem menggunakan relasi database agar data dari satu modul dipakai oleh modul lain secara terintegrasi.

## Aktor Sistem

- Admin mengelola data master, user, dashboard, dan laporan.
- Petugas pendaftaran mengelola pasien dan kunjungan.
- Dokter mengisi pemeriksaan, diagnosa, tindakan, dan resep.
- Kasir mengelola tagihan dan pembayaran.
- Kepala klinik melihat dashboard dan laporan.
- Pasien dapat melihat tagihan dan membayar online.
- Farmasi disiapkan sebagai role untuk validasi resep lanjutan.

## Alur Bisnis

1. Petugas mendaftarkan pasien ke poli dan dokter.
2. Dokter melakukan pemeriksaan dan memilih layanan medis.
3. Sistem menghitung total layanan dan membuat tagihan.
4. Kasir atau pasien melakukan pembayaran manual atau online.
5. Sistem menyimpan riwayat pembayaran dan memperbarui status tagihan.
6. Admin atau kepala klinik melihat laporan kunjungan dan pemasukan.

## Struktur Database Utama

Tabel yang dibuat meliputi `users`, `pasien`, `poli`, `dokter`, `pegawai`, `jadwal_dokter`, `layanan`, `kunjungan`, `pemeriksaan`, `detail_pemeriksaan_layanan`, `tagihan`, dan `pembayaran`.

## Catatan Pengembangan

Project ini sudah dilengkapi migration, model relasi Eloquent, controller, view Blade, role middleware, seed data demo, integrasi Snap API Midtrans, dan smoke test Laravel.
