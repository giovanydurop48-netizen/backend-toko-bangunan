@extends('layouts.admin')
@section('title', 'Tambah Produk dari Pembelian')

@section('content')
<div class="container-fluid py-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small">Pilih barang yang sudah dibeli dari luar untuk ditambahkan ke katalog jual kasir.</span>
        <a href="{{ route('admin.produk.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 px-3 small">
            <strong>Gagal menyimpan produk:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(count($barangDibeli ?? []) === 0)
        <div class="alert alert-warning py-2 px-3 small d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-exclamation-circle me-1"></i>
                Belum ada barang baru dari pembelian luar. Silakan input pembelian barang terlebih dahulu di menu <strong>Stok &amp; Pembelian</strong>.
            </span>
            <a href="{{ route('admin.stok.index') }}" class="btn btn-success btn-sm px-3 fw-semibold">
                <i class="bi bi-cart-plus me-1"></i> Beli Barang Sekarang
            </a>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.produk.store') }}" method="POST">
                @csrf
                <input type="hidden" name="gudang_id" id="input-gudang-id" value="{{ old('gudang_id', $selectedGudangId ?? '') }}">

                <div class="row g-3">
                    <!-- PILIH BARANG YANG SUDAH DIBELI -->
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-success">
                            <i class="bi bi-box-seam me-1"></i> Pilih Barang yang Sudah Dibeli <span class="text-danger">*</span>
                        </label>
                        <select id="select-barang-dibeli" class="form-select form-select-sm border-success" required>
                            <option value="">-- Klik untuk memilih barang yang sudah dibeli --</option>
                            @foreach($barangDibeli ?? [] as $item)
                                <option value="{{ $item->id }}"
                                        data-nama="{{ $item->nama_barang }}"
                                        data-satuan="{{ $item->satuan ?? 'Pcs' }}"
                                        data-harga="{{ (float) ($item->harga_beli ?? 0) }}"
                                        data-stok="{{ (float) ($item->jumlah ?? 0) }}"
                                        {{ (old('gudang_id', $selectedGudangId ?? '') == $item->id) ? 'selected' : '' }}>
                                    {{ $item->nama_barang }} — (Stok Dibeli: {{ $item->jumlah }} {{ $item->satuan ?? 'Pcs' }} | Modal: Rp {{ number_format($item->harga_beli ?? 0, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">Nama barang, satuan, harga beli, dan stok otomatis terisi setelah Anda memilih barang di atas.</div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Nama Produk / Material <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="input-nama-produk" value="{{ old('nama') }}" class="form-control form-control-sm bg-light" placeholder="Otomatis terisi dari pilihan barang di atas..." readonly required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Kategori Material <span class="text-danger">*</span></label>
                        @if(isset($kategoris) && $kategoris->count() > 0)
                            <select name="kategori_id" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat->id }}" {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>
                                        {{ $kat->nama_kategori ?? $kat->nama }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" name="nama_kategori" value="{{ old('nama_kategori', 'Bahan Siap Pakai') }}" class="form-control form-control-sm" required>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Satuan Barang <span class="text-danger">*</span></label>
                        <input type="text" name="satuan" id="input-satuan-produk" value="{{ old('satuan', 'Pcs') }}" class="form-control form-control-sm bg-light" readonly required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Harga Beli / Modal (Rp)</label>
                        <input type="number" step="any" name="harga_beli" id="input-harga-beli" value="{{ old('harga_beli', 0) }}" class="form-control form-control-sm bg-light" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Harga Jual Kasir (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="any" name="harga_jual" id="input-harga-jual" value="{{ old('harga_jual') }}" class="form-control form-control-sm border-primary" min="1" placeholder="Masukkan harga jual..." required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Stok Masuk <span class="text-danger">*</span></label>
                        <input type="number" step="any" name="stok" id="input-stok-produk" value="{{ old('stok', 0) }}" class="form-control form-control-sm bg-light" readonly required>
                    </div>
                </div>

                <hr class="my-3">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.produk.index') }}" class="btn btn-light border btn-sm px-3">Batal</a>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                        <i class="bi bi-save me-1"></i> Tambahkan ke Produk Kasir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectBarang = document.getElementById('select-barang-dibeli');
    const inputGudang  = document.getElementById('input-gudang-id');
    const inputNama    = document.getElementById('input-nama-produk');
    const inputSatuan  = document.getElementById('input-satuan-produk');
    const inputBeli    = document.getElementById('input-harga-beli');
    const inputStok    = document.getElementById('input-stok-produk');
    const inputJual    = document.getElementById('input-harga-jual');

    function isiOtomatisDariPilihan() {
        const opt = selectBarang.options[selectBarang.selectedIndex];
        if (!opt || !opt.value) {
            inputGudang.value = '';
            inputNama.value   = '';
            inputSatuan.value = 'Pcs';
            inputBeli.value   = 0;
            inputStok.value   = 0;
            return;
        }
        inputGudang.value = opt.value;
        inputNama.value   = opt.getAttribute('data-nama') || '';
        inputSatuan.value = opt.getAttribute('data-satuan') || 'Pcs';
        inputBeli.value   = opt.getAttribute('data-harga') || 0;
        inputStok.value   = opt.getAttribute('data-stok') || 0;
        inputJual.focus();
    }

    selectBarang.addEventListener('change', isiOtomatisDariPilihan);

    // Jalankan otomatis jika sudah terpilih dari URL (?gudang_id=...)
    if (selectBarang.value) {
        isiOtomatisDariPilihan();
    }
});
</script>
@endsection