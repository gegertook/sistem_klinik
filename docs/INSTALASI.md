# Dokumentasi Instalasi

## Kebutuhan

- PHP 8.2 atau lebih baru.
- Composer.
- MySQL atau MariaDB.
- Web browser modern.
- Akun Midtrans Sandbox jika pembayaran online akan diuji.

## Langkah Instalasi

1. Ekstrak file zip project.
2. Buka terminal di folder project.
3. Jalankan:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

4. Buat database MySQL bernama `sistem_klinik`.
5. Sesuaikan koneksi database di file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistem_klinik
DB_USERNAME=root
DB_PASSWORD=
```

6. Jalankan migrasi dan data awal:

```bash
php artisan migrate --seed
```

7. Jalankan server lokal:

```bash
php artisan serve
```

8. Buka aplikasi di `http://127.0.0.1:8000`.

## Konfigurasi Midtrans Sandbox

Isi key berikut di file `.env`:

```env
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY=isi_server_key_sandbox
MIDTRANS_CLIENT_KEY=isi_client_key_sandbox
```

Endpoint notifikasi Midtrans:

```text
http://domain-atau-ngrok/midtrans/notification
```

Untuk pengujian lokal callback Midtrans, gunakan tunneling seperti Ngrok.
