<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LaporanWebController;
use App\Http\Controllers\Web\ProdukWebController;
use App\Http\Controllers\Web\UserWebController;
use App\Http\Controllers\Web\WebAuthController;
use App\Http\Controllers\Web\OwnerController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// Proteksi Auth untuk Admin & Owner
Route::middleware(['auth', 'role:admin,owner'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Laporan Keuangan (Akses Owner & Admin)
    Route::get('/laporan', [LaporanWebController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/cetak', [LaporanWebController::class, 'cetak'])->name('laporan.cetak');

    // Rute Bawaan Produk & User
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('produk', ProdukWebController::class);
        Route::get('/users', [UserWebController::class, 'index'])->name('users.index');
        Route::post('/users', [UserWebController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [UserWebController::class, 'destroy'])->name('users.destroy');
    });
});

// Panel Lengkap Admin (/admin/...)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard Ringkasan Admin
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/laporan', [AdminController::class, 'laporanIndex'])->name('laporan.index');

    // Produk & Kategori
    Route::get('/produk', [AdminController::class, 'produkIndex'])->name('produk.index');
    Route::post('/produk', [AdminController::class, 'produkStore'])->name('produk.store');
    Route::put('/produk/{id}', [AdminController::class, 'produkUpdate'])->name('produk.update');
    Route::delete('/produk/{id}', [AdminController::class, 'produkDestroy'])->name('produk.destroy');
    Route::post('/kategori', [AdminController::class, 'kategoriStore'])->name('kategori.store');
    Route::delete('/kategori/{id}', [AdminController::class, 'kategoriDestroy'])->name('kategori.destroy');
    Route::get('/produk/create', [AdminController::class, 'produkCreate'])->name('produk.create');
    Route::get('/produk/{id}/edit', [AdminController::class, 'produkEdit'])->name('produk.edit');

    // Stok Masuk (Pembelian), Barang Keluar & Distributor
    Route::get('/stok', [AdminController::class, 'stokIndex'])->name('stok.index');
    Route::post('/distributor', [AdminController::class, 'distributorStore'])->name('distributor.store');
    Route::post('/pembelian', [AdminController::class, 'pembelianStore'])->name('pembelian.store');
    Route::post('/barang-keluar', [AdminController::class, 'barangKeluarStore'])->name('barang_keluar.store');
    Route::post('/stok/masuk', [AdminController::class, 'pembelianStore'])->name('stok.masuk');
    Route::post('/stok/keluar', [AdminController::class, 'barangKeluarStore'])->name('stok.keluar');

    // Monitoring & Pembatalan Transaksi POS
    Route::get('/transaksi', [AdminController::class, 'transaksiIndex'])->name('transaksi.index');
    Route::post('/transaksi/{id}/batal', [AdminController::class, 'transaksiBatal'])->name('transaksi.batal');

    // Keuangan (Pengeluaran & Pemasukan Lain)
    Route::get('/keuangan', [AdminController::class, 'keuanganIndex'])->name('keuangan.index');
    Route::post('/pengeluaran', [AdminController::class, 'pengeluaranStore'])->name('pengeluaran.store');
    Route::post('/pemasukan-lain', [AdminController::class, 'pemasukanLainStore'])->name('pemasukan_lain.store');

    // Manajemen User
    Route::get('/users', [AdminController::class, 'userIndex'])->name('users.index');
    Route::post('/users', [AdminController::class, 'userStore'])->name('users.store');
    Route::put('/users/{id}', [AdminController::class, 'userUpdate'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'userDestroy'])->name('users.destroy');

   // Panel Khusus Owner (/owner/...)


});

// Panel Khusus Owner (/owner/...)
Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/laporan', [OwnerController::class, 'laporan'])->name('laporan');
    Route::get('/transaksi', [OwnerController::class, 'transaksi'])->name('transaksi');
    Route::get('/stok', [OwnerController::class, 'stok'])->name('stok');
});