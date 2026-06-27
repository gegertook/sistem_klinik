<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KunjunganController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\PemeriksaanController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/midtrans/notification', [TagihanController::class, 'notification'])->name('midtrans.notification');

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/master/{resource}', [MasterDataController::class, 'index'])->name('master.index');
    Route::get('/master/{resource}/create', [MasterDataController::class, 'create'])->name('master.create');
    Route::post('/master/{resource}', [MasterDataController::class, 'store'])->name('master.store');
    Route::get('/master/{resource}/{id}/edit', [MasterDataController::class, 'edit'])->name('master.edit');
    Route::put('/master/{resource}/{id}', [MasterDataController::class, 'update'])->name('master.update');
    Route::delete('/master/{resource}/{id}', [MasterDataController::class, 'destroy'])->name('master.destroy');

    Route::resource('kunjungan', KunjunganController::class)
        ->except(['show'])
        ->middleware('role:admin,pendaftaran');

    Route::get('/pemeriksaan', [PemeriksaanController::class, 'index'])
        ->name('pemeriksaan.index')
        ->middleware('role:admin,dokter');
    Route::get('/pemeriksaan/{kunjungan}', [PemeriksaanController::class, 'show'])
        ->name('pemeriksaan.show')
        ->middleware('role:admin,dokter,kasir,kepala_klinik');
    Route::get('/pemeriksaan/{kunjungan}/edit', [PemeriksaanController::class, 'edit'])
        ->name('pemeriksaan.edit')
        ->middleware('role:admin,dokter');
    Route::put('/pemeriksaan/{kunjungan}', [PemeriksaanController::class, 'update'])
        ->name('pemeriksaan.update')
        ->middleware('role:admin,dokter');

    Route::get('/tagihan', [TagihanController::class, 'index'])
        ->name('tagihan.index')
        ->middleware('role:admin,kasir,pasien');
    Route::get('/tagihan/{tagihan}', [TagihanController::class, 'show'])
        ->name('tagihan.show')
        ->middleware('role:admin,kasir,pasien,kepala_klinik');
    Route::post('/tagihan/{tagihan}/manual', [TagihanController::class, 'payManual'])
        ->name('tagihan.manual')
        ->middleware('role:admin,kasir');
    Route::post('/tagihan/{tagihan}/bpjs', [TagihanController::class, 'payBpjs'])
        ->name('tagihan.bpjs')
        ->middleware('role:admin,kasir');
    Route::post('/tagihan/{tagihan}/midtrans', [TagihanController::class, 'payOnline'])
        ->name('tagihan.midtrans')
        ->middleware('role:admin,kasir,pasien');
    Route::post('/tagihan/{tagihan}/midtrans/sync', [TagihanController::class, 'syncMidtransStatus'])
        ->name('tagihan.midtrans.sync')
        ->middleware('role:admin,kasir,pasien');

    Route::middleware('role:admin,kepala_klinik')->group(function () {
        Route::get('/laporan/kunjungan', [LaporanController::class, 'kunjungan'])->name('laporan.kunjungan');
        Route::get('/laporan/pemasukan', [LaporanController::class, 'pemasukan'])->name('laporan.pemasukan');
    });

    Route::resource('users', UserController::class)
        ->except(['show'])
        ->middleware('role:admin');
});
