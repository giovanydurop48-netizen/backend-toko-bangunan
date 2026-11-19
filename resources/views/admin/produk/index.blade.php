@extends('layouts.admin')
@section('title', 'Manajemen Produk')

@section('content')
<div class="container-fluid py-2">
    <!-- Header Compact -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <span class="text-muted small">Kelola daftar material bangunan dan ketersediaan stok kasir.</span>
        <div class="d-flex gap-2">
            @if(isset($kategoris) && \Illuminate\Support\Facades\Schema::hasTable(\Illuminate\Support\Facades\Schema::hasTable('kategoris') ? 'kategoris' : 'kategori'))
            <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalKategori">
                <i class="bi bi-tags me-1"></i> Kategori
            </button>
            @endif
            <a href="{{ route('admin.produk.create') }}" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-plus-lg me-1"></i> Tambah Produk
            </a>
        </div>
    </div>

    <!-- Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 small" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Card Pencarian & Tabel Produk -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Form Pencarian -->
            <form action="{{ route('admin.produk.index') }}" method="GET" class="mb-3">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" value="{{ request('search') ?? request('q') }}" class="form-control" placeholder="Cari nama produk / kategori...">
                    <button class="btn btn-outline-secondary px-3" type="submit">
                        <i class="bi bi-search me-1"></i> Cari
                    </button>
                    @if(request('search') || request('q'))
                        <a href="{{ route('admin.produk.index') }}" class="btn btn-outline-danger">Reset</a>
                    @endif
                </div>
            </form>

            <!-- Tabel Katalog Produk -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Kategori</th>
                            <th>Nama Produk</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                            <th width="180" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $katMap = collect($kategoris ?? [])->mapWithKeys(function ($k) {
                                return [$k->id => ($k->nama_kategori ?? $k->nama ?? '-')];
                            });
                        @endphp

                        @forelse($produks as $index => $item)
                        @php
                            $namaKat   = $item->nama_kategori ?? ($katMap[$item->kategori_id ?? 0] ?? 'Umum');
                            $namaPrd   = $item->nama_produk ?? $item->nama ?? '-';
                            $hargaJual = $item->harga_jual ?? $item->harga ?? 0;
                            $satuanPrd = ($item->satuan_dasar ?: ($item->satuan ?: ($item->unit ?? null))) ?: 'Pcs';
                        @endphp
                        <tr>
                            <td>{{ $produks->firstItem() + $index }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $namaKat }}</span></td>
                            <td class="fw-semibold">{{ $namaPrd }}</td>
                            <td>Rp {{ number_format($hargaJual, 0, ',', '.') }}</td>
                            <td>
                                @if($item->stok <= 10)
                                    <span class="badge bg-danger">{{ $item->stok }} (Menipis)</span>
                                @else
                                    <span class="badge bg-success">{{ $item->stok }}</span>
                                @endif
                            </td>
                            <td>{{ $satuanPrd }}</td>
                            <td class="text-center text-nowrap">
    <a href="{{ route('admin.stok.index') }}" class="btn btn-sm btn-success py-0 px-2">
        <i class="bi bi-plus-circle me-1"></i> Tambah Stok
    </a>
    <a href="{{ route('admin.produk.edit', $item->id) }}" class="btn btn-sm btn-warning py-0 px-2" title="Edit Produk">
        <i class="bi bi-pencil-square me-1"></i> Edit
    </a>
</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted small">Data produk tidak ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
             {{ $produks->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Navigasi Alur Langkah 2 -->
    @include('admin.partials.alur-nav', ['step' => 2])
</div>

<!-- Modal Kelola Kategori -->
<div class="modal fade" id="modalKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold">Kelola Kategori Material</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.kategori.store') }}" method="POST" class="mb-3">
                    @csrf
                    <div class="input-group input-group-sm">
                        <input type="text" name="nama" class="form-control" placeholder="Nama kategori baru (misal: Cat & Pelapis)" required>
                        <button type="submit" class="btn btn-primary px-3">Tambah</button>
                    </div>
                </form>

                <ul class="list-group list-group-flush">
                    @forelse($kategoris ?? [] as $kat)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="small">{{ $kat->nama_kategori ?? $kat->nama }}</span>
                            <form action="{{ route('admin.kategori.destroy', $kat->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2"><i class="bi bi-trash"></i></button>
                            </form>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center small">Belum ada kategori tersimpan.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection