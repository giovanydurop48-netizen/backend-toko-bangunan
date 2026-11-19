@extends('layouts.admin')
@section('title', 'Manajemen Produk & Kategori')

@section('content')
<div class="row g-4">
    <!-- Kolom Kategori -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Tambah Kategori</div>
            <div class="card-body">
                <form action="{{ route('admin.kategori.store') }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Semen & Pasir" required>
                        <button class="btn btn-primary" type="submit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Kategori</div>
            <ul class="list-group list-group-flush">
                @foreach($kategoris as $kat)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $kat->nama_kategori ?? $kat->nama }}
                    <form action="{{ route('admin.kategori.destroy', $kat->id) }}" method="POST" onsubmit="return confirm('Hapus kategori?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Kolom Produk -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">Katalog Material Bangunan</span>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahProduk">
                    <i class="bi bi-plus-lg"></i> Tambah Material
                </button>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-6">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama barang...">
                    </div>
                    <div class="col-md-4">
                        <select name="kategori_id" class="form-select form-select-sm">
                            <option value="">-- Semua Kategori --</option>
                            @foreach($kategoris as $kat)
                                <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                                    {{ $kat->nama_kategori ?? $kat->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary btn-sm w-100">Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Material</th>
                                <th>Harga Jual</th>
                                <th>Stok</th>
                                <th>Satuan</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($produks as $p)
                            <tr>
                                <td>{{ $p->nama_produk ?? $p->nama }}</td>
                                <td>Rp {{ number_format($p->harga_jual ?? $p->harga ?? 0, 0, ',', '.') }}</td>
                                <td>{{ $p->stok }}</td>
                                <td>{{ $p->satuan ?? 'Pcs' }}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProduk{{ $p->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.produk.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus material ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Edit Produk -->
                            <div class="modal fade" id="editProduk{{ $p->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('admin.produk.update', $p->id) }}" method="POST" class="modal-content">
                                        @csrf @method('PUT')
                                        <div class="modal-header"><h5 class="modal-title">Edit Material</h5></div>
                                        <div class="modal-body">
                                            <div class="mb-2">
                                                <label class="form-label">Nama Material</label>
                                                <input type="text" name="nama" class="form-control" value="{{ $p->nama_produk ?? $p->nama }}" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Kategori</label>
                                                <select name="kategori_id" class="form-select">
                                                    <option value="">-- Pilih --</option>
                                                    @foreach($kategoris as $kat)
                                                        <option value="{{ $kat->id }}" {{ ($p->kategori_id ?? '') == $kat->id ? 'selected' : '' }}>
                                                            {{ $kat->nama_kategori ?? $kat->nama }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col">
                                                    <label class="form-label">Harga Beli</label>
                                                    <input type="number" name="harga_beli" class="form-control" value="{{ $p->harga_beli ?? 0 }}">
                                                </div>
                                                <div class="col">
                                                    <label class="form-label">Harga Jual</label>
                                                    <input type="number" name="harga_jual" class="form-control" value="{{ $p->harga_jual ?? $p->harga ?? 0 }}" required>
                                                </div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col">
                                                    <label class="form-label">Stok</label>
                                                    <input type="number" name="stok" class="form-control" value="{{ $p->stok }}" required>
                                                </div>
                                                <div class="col">
                                                    <label class="form-label">Satuan</label>
                                                    <input type="text" name="satuan" class="form-control" value="{{ $p->satuan ?? 'Pcs' }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Produk -->
<div class="modal fade" id="modalTambahProduk" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.produk.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Tambah Material Baru</h5></div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Nama Material</label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Semen Tiga Roda 50kg" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Kategori</label>
                    <select name="kategori_id" class="form-select">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}">{{ $kat->nama_kategori ?? $kat->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col">
                        <label class="form-label">Harga Beli (Modal)</label>
                        <input type="number" name="harga_beli" class="form-control" value="0">
                    </div>
                    <div class="col">
                        <label class="form-label">Harga Jual (Kasir)</label>
                        <input type="number" name="harga_jual" class="form-control" required>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col">
                        <label class="form-label">Stok Awal</label>
                        <input type="number" name="stok" class="form-control" value="0" required>
                    </div>
                    <div class="col">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" placeholder="Sak / Batang / Kg" value="Pcs">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Material</button>
            </div>
        </form>
    </div>
</div>
@include('admin.partials.alur-nav', ['step' => 1])
@endsection