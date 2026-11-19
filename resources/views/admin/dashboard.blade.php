@extends('layouts.admin')
@section('title', 'Dashboard Admin - Toko Bangunan')

@section('content')
<div class="container-fluid py-2">
    <!-- Baris Panduan Singkat -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <span class="text-muted small">Pusat kendali operasional dan alur kerja sistem TB. Maju Jaya</span>
        <button type="button" class="btn btn-outline-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalAlurSistem">
            <i class="bi bi-diagram-3 me-1"></i> Bagan Alur
        </button>
    </div>

    <!-- PANEL TOMBOL ALUR KERJA (LANGKAH 1 - 5) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-primary small">
                <i class="bi bi-signpost-split me-1"></i> Alur Kerja Operasional (Ikuti Urutan Langkah 1 – 5)
            </span>
            <span class="badge bg-primary-subtle text-primary">Panduan Klien</span>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-stretch">
                <!-- Langkah 1 -->
                <div class="col-md">
                    <div class="border rounded-3 p-3 h-100 d-flex flex-column justify-content-between bg-light">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary rounded-pill">Langkah 1</span>
                                <i class="bi bi-arrow-right text-muted d-none d-md-inline"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Siapkan Akun</h6>
                            <p class="text-muted small mb-3">Buat akun login untuk <strong>Kasir</strong> &amp; <strong>Owner</strong>.</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary px-3">
                                <i class="bi bi-people me-1"></i> Kelola User
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Langkah 2 -->
                <div class="col-md">
                    <div class="border rounded-3 p-3 h-100 d-flex flex-column justify-content-between bg-light">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary rounded-pill">Langkah 2</span>
                                <i class="bi bi-arrow-right text-muted d-none d-md-inline"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Input Material</h6>
                            <p class="text-muted small mb-3">Tambahkan kategori &amp; katalog barang bangunan.</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.produk.index') }}" class="btn btn-sm btn-primary px-3">
                                <i class="bi bi-box-seam me-1"></i> Produk
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Langkah 3 -->
                <div class="col-md">
                    <div class="border rounded-3 p-3 h-100 d-flex flex-column justify-content-between bg-light">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary rounded-pill">Langkah 3</span>
                                <i class="bi bi-arrow-right text-muted d-none d-md-inline"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Stok &amp; Supplier</h6>
                            <p class="text-muted small mb-3">Input distributor dan barang masuk ke gudang.</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.stok.index') }}" class="btn btn-sm btn-primary px-3">
                                <i class="bi bi-truck me-1"></i> Stok Masuk
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Langkah 4 -->
                <div class="col-md">
                    <div class="border rounded-3 p-3 h-100 d-flex flex-column justify-content-between bg-light">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-success rounded-pill">Langkah 4</span>
                                <i class="bi bi-arrow-right text-muted d-none d-md-inline"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Pantau Kasir POS</h6>
                            <p class="text-muted small mb-3">Pantau transaksi penjualan kasir secara real-time.</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.transaksi.index') }}" class="btn btn-sm btn-success px-3">
                                <i class="bi bi-receipt me-1"></i> Transaksi
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Langkah 5 -->
                <div class="col-md">
                    <div class="border rounded-3 p-3 h-100 d-flex flex-column justify-content-between bg-light">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-dark rounded-pill">Langkah 5</span>
                                <i class="bi bi-check-circle-fill text-success"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Catat Kas Toko</h6>
                            <p class="text-muted small mb-3">Catat biaya operasional &amp; pemasukan tambahan.</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.keuangan.index') }}" class="btn btn-sm btn-dark px-3">
                                <i class="bi bi-cash-coin me-1"></i> Keuangan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3 KARTU RINGKASAN DATA -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Total Jenis Material</small>
                    <h3 class="fw-bold text-primary mb-0">{{ $totalProduk ?? 0 }} Item</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Transaksi Kasir Hari Ini</small>
                    <h3 class="fw-bold text-success mb-0">{{ $transaksiHariIni ?? 0 }} Struk</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Total Akun Terdaftar</small>
                    <h3 class="fw-bold text-dark mb-0">{{ $totalUser ?? 0 }} Pengguna</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL PERINGATAN STOK MENIPIS -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 px-3">
            <span class="fw-bold text-danger small">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Peringatan Stok Menipis (&le; 10)
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama Material</th>
                        <th>Sisa Stok</th>
                        <th>Satuan</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stokMenipis ?? [] as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->nama_produk ?? $item->nama }}</td>
                        <td><span class="badge bg-danger">{{ $item->stok }}</span></td>
                        <td>{{ $item->satuan ?? 'Pcs' }}</td>
                        <td>
                            <a href="{{ route('admin.stok.index') }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                                + Restok
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4 small">Semua stok dalam kondisi aman.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL BAGAN ALUR SISTEM -->
<div class="modal fade" id="modalAlurSistem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-diagram-3 me-2"></i>Alur Kerja Sistem POS Toko Bangunan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item py-2">
                        <div class="fw-bold text-primary small">1. Persiapan Awal (Admin Web)</div>
                        <p class="mb-0 small text-muted">Membuat akun <strong>Kasir</strong> &amp; <strong>Owner</strong>, mengisi <strong>Kategori &amp; Produk Material</strong>, serta mencatat <strong>Stok Masuk</strong>.</p>
                    </div>
                    <div class="list-group-item py-2">
                        <div class="fw-bold text-success small">2. Transaksi Penjualan (Kasir POS)</div>
                        <p class="mb-0 small text-muted">Kasir memproses belanja pelanggan dan mencetak struk. <strong>Stok otomatis berkurang</strong>.</p>
                    </div>
                    <div class="list-group-item py-2">
                        <div class="fw-bold text-warning-emphasis small">3. Kontrol &amp; Kas Harian (Admin Web)</div>
                        <p class="mb-0 small text-muted">Admin memantau transaksi masuk, pembatalan struk, serta pengeluaran operasional toko.</p>
                    </div>
                    <div class="list-group-item py-2">
                        <div class="fw-bold text-dark small">4. Laporan Keuangan (Owner)</div>
                        <p class="mb-0 small text-muted">Owner memantau omzet harian/bulanan dan mencetak laporan keuangan.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-sm px-3">Mulai Langkah 1</a>
            </div>
        </div>
    </div>
</div>
@endsection