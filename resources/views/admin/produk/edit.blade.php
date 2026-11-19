@extends('layouts.admin')
@section('title', 'Edit Produk Material')

@section('content')
@php
    $namaPrd   = $produk->nama_produk ?? $produk->nama ?? '';
    $satuanPrd = $produk->satuan_dasar ?? $produk->satuan ?? 'Pcs';
    $hargaBeli = (float) ($produk->harga_beli_terakhir ?? $produk->hpp_rata_rata ?? $produk->harga_beli ?? 0);
    $hargaJual = (float) ($produk->harga_jual ?? $produk->harga ?? 0);
    $stokPrd   = (float) ($produk->stok ?? 0);
@endphp

<div class="container-fluid py-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small">Perbarui harga, kategori, atau jumlah stok barang.</span>
        <a href="{{ route('admin.produk.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 px-3 small">
            <strong>Gagal menyimpan perubahan:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger py-2 px-3 small">
            {{ session('error') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.produk.update', $produk->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Nama Produk / Material <span class="text-danger">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama', $namaPrd) }}" class="form-control form-control-sm" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Kategori Material</label>
                        @if(isset($kategoris) && $kategoris->count() > 0)
                            <select name="kategori_id" class="form-select form-select-sm">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat->id }}" {{ old('kategori_id', $produk->kategori_id ?? '') == $kat->id ? 'selected' : '' }}>
                                        {{ $kat->nama_kategori ?? $kat->nama }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" name="nama_kategori" value="{{ old('nama_kategori', $produk->nama_kategori ?? 'Umum') }}" class="form-control form-control-sm">
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Satuan Barang <span class="text-danger">*</span></label>
                        <input type="text" name="satuan" value="{{ old('satuan', $satuanPrd) }}" class="form-control form-control-sm" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Harga Beli / Modal (Rp)</label>
                        <input type="number" step="any" name="harga_beli" value="{{ old('harga_beli', $hargaBeli) }}" class="form-control form-control-sm" min="0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Harga Jual Kasir (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="any" name="harga_jual" value="{{ old('harga_jual', $hargaJual) }}" class="form-control form-control-sm" min="0" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Stok <span class="text-danger">*</span></label>
                        <input type="number" step="any" name="stok" value="{{ old('stok', $stokPrd) }}" class="form-control form-control-sm" min="0" required>
                    </div>
                </div>

                <hr class="my-3">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.produk.index') }}" class="btn btn-light border btn-sm px-3">Batal</a>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection