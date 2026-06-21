<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasien', function (Blueprint $table) {
            $table->id();
            $table->string('no_rm', 30)->unique();
            $table->string('nik', 30)->unique();
            $table->string('nama_pasien');
            $table->string('jenis_kelamin', 20);
            $table->date('tanggal_lahir');
            $table->text('alamat');
            $table->string('no_hp', 30)->nullable();
            $table->string('golongan_darah', 5)->nullable();
            $table->text('alergi')->nullable();
            $table->timestamps();
        });

        Schema::create('poli', function (Blueprint $table) {
            $table->id();
            $table->string('nama_poli');
            $table->text('deskripsi')->nullable();
            $table->string('status', 20)->default('Aktif');
            $table->timestamps();
        });

        Schema::create('dokter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poli_id')->constrained('poli')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nama_dokter');
            $table->string('kode_dokter', 30)->unique();
            $table->string('spesialisasi')->nullable();
            $table->string('no_hp', 30)->nullable();
            $table->string('status', 20)->default('Aktif');
            $table->timestamps();
        });

        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pegawai');
            $table->string('jabatan');
            $table->string('no_hp', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();
            $table->string('status', 20)->default('Aktif');
            $table->timestamps();
        });

        Schema::create('jadwal_dokter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokter_id')->constrained('dokter')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('hari', 20);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('status', 20)->default('Aktif');
            $table->timestamps();
        });

        Schema::create('layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poli_id')->constrained('poli')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nama_layanan');
            $table->string('kategori');
            $table->unsignedBigInteger('harga');
            $table->string('status', 20)->default('Aktif');
            $table->timestamps();
        });

        Schema::create('kunjungan', function (Blueprint $table) {
            $table->id();
            $table->string('no_kunjungan', 40)->unique();
            $table->foreignId('pasien_id')->constrained('pasien')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('poli_id')->constrained('poli')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('dokter_id')->constrained('dokter')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal_kunjungan');
            $table->text('keluhan')->nullable();
            $table->string('status_kunjungan', 40)->default('Menunggu Pemeriksaan');
            $table->timestamps();
        });

        Schema::create('pemeriksaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kunjungan_id')->unique()->constrained('kunjungan')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('dokter_id')->constrained('dokter')->cascadeOnUpdate()->restrictOnDelete();
            $table->text('diagnosa');
            $table->text('catatan_pemeriksaan')->nullable();
            $table->text('resep')->nullable();
            $table->timestamps();
        });

        Schema::create('detail_pemeriksaan_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemeriksaan_id')->constrained('pemeriksaan')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('layanan_id')->constrained('layanan')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedBigInteger('harga');
            $table->unsignedInteger('jumlah')->default(1);
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });

        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->string('no_tagihan', 40)->unique();
            $table->foreignId('kunjungan_id')->unique()->constrained('kunjungan')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('total_tagihan');
            $table->string('status_pembayaran', 40)->default('Belum Dibayar');
            $table->string('metode_pembayaran', 40)->nullable();
            $table->string('snap_token')->nullable();
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->date('tanggal_tagihan');
            $table->timestamp('tanggal_bayar')->nullable();
            $table->timestamps();
        });

        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')->constrained('tagihan')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('order_id')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('transaction_status', 40);
            $table->timestamp('transaction_time')->nullable();
            $table->unsignedBigInteger('gross_amount');
            $table->string('fraud_status', 40)->nullable();
            $table->json('response_midtrans')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('tagihan');
        Schema::dropIfExists('detail_pemeriksaan_layanan');
        Schema::dropIfExists('pemeriksaan');
        Schema::dropIfExists('kunjungan');
        Schema::dropIfExists('layanan');
        Schema::dropIfExists('jadwal_dokter');
        Schema::dropIfExists('pegawai');
        Schema::dropIfExists('dokter');
        Schema::dropIfExists('poli');
        Schema::dropIfExists('pasien');
    }
};
