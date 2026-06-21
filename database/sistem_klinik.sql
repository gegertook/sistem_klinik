CREATE DATABASE IF NOT EXISTS sistem_klinik CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistem_klinik;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS pembayaran;
DROP TABLE IF EXISTS tagihan;
DROP TABLE IF EXISTS detail_pemeriksaan_layanan;
DROP TABLE IF EXISTS pemeriksaan;
DROP TABLE IF EXISTS kunjungan;
DROP TABLE IF EXISTS layanan;
DROP TABLE IF EXISTS jadwal_dokter;
DROP TABLE IF EXISTS pegawai;
DROP TABLE IF EXISTS dokter;
DROP TABLE IF EXISTS poli;
DROP TABLE IF EXISTS pasien;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    role VARCHAR(30) NOT NULL DEFAULT 'admin',
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pasien (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    no_rm VARCHAR(30) NOT NULL UNIQUE,
    nik VARCHAR(30) NOT NULL UNIQUE,
    nama_pasien VARCHAR(255) NOT NULL,
    jenis_kelamin VARCHAR(20) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    alamat TEXT NOT NULL,
    no_hp VARCHAR(30) NULL,
    golongan_darah VARCHAR(5) NULL,
    alergi TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE poli (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_poli VARCHAR(255) NOT NULL,
    deskripsi TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Aktif',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE dokter (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poli_id BIGINT UNSIGNED NOT NULL,
    nama_dokter VARCHAR(255) NOT NULL,
    kode_dokter VARCHAR(30) NOT NULL UNIQUE,
    spesialisasi VARCHAR(255) NULL,
    no_hp VARCHAR(30) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Aktif',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT dokter_poli_id_foreign FOREIGN KEY (poli_id) REFERENCES poli(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pegawai (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_pegawai VARCHAR(255) NOT NULL,
    jabatan VARCHAR(255) NOT NULL,
    no_hp VARCHAR(30) NULL,
    email VARCHAR(255) NULL,
    alamat TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Aktif',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE jadwal_dokter (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dokter_id BIGINT UNSIGNED NOT NULL,
    hari VARCHAR(20) NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Aktif',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT jadwal_dokter_dokter_id_foreign FOREIGN KEY (dokter_id) REFERENCES dokter(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE layanan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poli_id BIGINT UNSIGNED NOT NULL,
    nama_layanan VARCHAR(255) NOT NULL,
    kategori VARCHAR(255) NOT NULL,
    harga BIGINT UNSIGNED NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Aktif',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT layanan_poli_id_foreign FOREIGN KEY (poli_id) REFERENCES poli(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kunjungan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    no_kunjungan VARCHAR(40) NOT NULL UNIQUE,
    pasien_id BIGINT UNSIGNED NOT NULL,
    poli_id BIGINT UNSIGNED NOT NULL,
    dokter_id BIGINT UNSIGNED NOT NULL,
    tanggal_kunjungan DATE NOT NULL,
    keluhan TEXT NULL,
    status_kunjungan VARCHAR(40) NOT NULL DEFAULT 'Menunggu Pemeriksaan',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT kunjungan_pasien_id_foreign FOREIGN KEY (pasien_id) REFERENCES pasien(id) ON UPDATE CASCADE,
    CONSTRAINT kunjungan_poli_id_foreign FOREIGN KEY (poli_id) REFERENCES poli(id) ON UPDATE CASCADE,
    CONSTRAINT kunjungan_dokter_id_foreign FOREIGN KEY (dokter_id) REFERENCES dokter(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pemeriksaan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kunjungan_id BIGINT UNSIGNED NOT NULL UNIQUE,
    dokter_id BIGINT UNSIGNED NOT NULL,
    diagnosa TEXT NOT NULL,
    catatan_pemeriksaan TEXT NULL,
    resep TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT pemeriksaan_kunjungan_id_foreign FOREIGN KEY (kunjungan_id) REFERENCES kunjungan(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT pemeriksaan_dokter_id_foreign FOREIGN KEY (dokter_id) REFERENCES dokter(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE detail_pemeriksaan_layanan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pemeriksaan_id BIGINT UNSIGNED NOT NULL,
    layanan_id BIGINT UNSIGNED NOT NULL,
    harga BIGINT UNSIGNED NOT NULL,
    jumlah INT UNSIGNED NOT NULL DEFAULT 1,
    subtotal BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT detail_pemeriksaan_layanan_pemeriksaan_id_foreign FOREIGN KEY (pemeriksaan_id) REFERENCES pemeriksaan(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT detail_pemeriksaan_layanan_layanan_id_foreign FOREIGN KEY (layanan_id) REFERENCES layanan(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tagihan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    no_tagihan VARCHAR(40) NOT NULL UNIQUE,
    kunjungan_id BIGINT UNSIGNED NOT NULL UNIQUE,
    total_tagihan BIGINT UNSIGNED NOT NULL,
    status_pembayaran VARCHAR(40) NOT NULL DEFAULT 'Belum Dibayar',
    metode_pembayaran VARCHAR(40) NULL,
    snap_token VARCHAR(255) NULL,
    midtrans_order_id VARCHAR(255) NULL UNIQUE,
    tanggal_tagihan DATE NOT NULL,
    tanggal_bayar TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT tagihan_kunjungan_id_foreign FOREIGN KEY (kunjungan_id) REFERENCES kunjungan(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pembayaran (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tagihan_id BIGINT UNSIGNED NOT NULL,
    order_id VARCHAR(255) NULL,
    payment_type VARCHAR(255) NULL,
    transaction_status VARCHAR(40) NOT NULL,
    transaction_time TIMESTAMP NULL,
    gross_amount BIGINT UNSIGNED NOT NULL,
    fraud_status VARCHAR(40) NULL,
    response_midtrans JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT pembayaran_tagihan_id_foreign FOREIGN KEY (tagihan_id) REFERENCES tagihan(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data demo tersedia melalui Laravel seeder:
-- php artisan migrate --seed
