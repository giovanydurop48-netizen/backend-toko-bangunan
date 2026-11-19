<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangKeluarController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DistributorController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\PemasukanLainController;
use App\Http\Controllers\Api\PembeliController;
use App\Http\Controllers\Api\PembelianController;
use App\Http\Controllers\Api\PengeluaranController;
use App\Http\Controllers\Api\PenjualanController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\StokController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Dapat diakses Publik tanpa Token)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/produk', [ProdukController::class, 'index']);
Route::get('/produk/{id}', [ProdukController::class, 'show']);
Route::get('/kategori', [KategoriController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Wajib Token Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth Actions
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Master Pembeli
    Route::get('/pembeli', [PembeliController::class, 'index']);
    Route::post('/pembeli', [PembeliController::class, 'store']);
    Route::put('/pembeli/{id}', [PembeliController::class, 'update']);

    // POS & Penjualan (Kasir & Admin)
    Route::middleware('role:kasir,admin')->group(function () {
        Route::post('/penjualan', [PenjualanController::class, 'store']);
    });
    Route::get('/penjualan', [PenjualanController::class, 'index']);
    Route::get('/penjualan/{id}', [PenjualanController::class, 'show']);

    // Admin Exclusive Actions
    Route::middleware('role:admin')->group(function () {
        // User Management
        Route::apiResource('users', UserController::class);

        // CRUD Master Data
        Route::post('/kategori', [KategoriController::class, 'store']);
        Route::put('/kategori/{id}', [KategoriController::class, 'update']);
        Route::delete('/kategori/{id}', [KategoriController::class, 'destroy']);

        Route::post('/produk', [ProdukController::class, 'store']);
        Route::put('/produk/{id}', [ProdukController::class, 'update']);
        Route::delete('/produk/{id}', [ProdukController::class, 'destroy']);

        Route::apiResource('distributor', DistributorController::class);
        Route::delete('/pembeli/{id}', [PembeliController::class, 'destroy']);

        // Logistics & Purchasing
        Route::get('/pembelian', [PembelianController::class, 'index']);
        Route::post('/pembelian', [PembelianController::class, 'store']);
        Route::get('/barang-keluar', [BarangKeluarController::class, 'index']);
        Route::post('/barang-keluar', [BarangKeluarController::class, 'store']);

        // Cancel Sale
        Route::post('/penjualan/{id}/batal', [PenjualanController::class, 'batal']);

        // Financial Records
        Route::apiResource('pengeluaran', PengeluaranController::class)->only(['index', 'store']);
        Route::apiResource('pemasukan-lain', PemasukanLainController::class)->only(['index', 'store']);
    });

    // Owner & Admin Reports
    Route::middleware('role:admin,owner')->group(function () {
        Route::get('/stok/riwayat', [StokController::class, 'riwayat']);
        Route::get('/stok/menipis', [StokController::class, 'menipis']);
        Route::get('/laporan/penjualan', [LaporanController::class, 'penjualan']);
        Route::get('/laporan/laba-rugi', [LaporanController::class, 'labaRugi']);
    });
});