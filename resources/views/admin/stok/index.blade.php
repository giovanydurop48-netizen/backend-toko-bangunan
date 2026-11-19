@extends('layouts.admin')
@section('title', 'Manajemen Stok & Supplier')

@section('content')
<div class="container-fluid py-2">
    <!-- Keterangan Singkat Halaman -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <span class="text-muted small">Catat pembelian barang dari luar (supplier), data pemasok, dan mutasi stok gudang.</span>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Gudang &amp; Pembelian Supplier</span>
    </div>

    <!-- Notifikasi Sukses / Error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 small" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 small" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Panduan Urutan Pengisian -->
    <div class="alert alert-light border shadow-sm py-2 px-3 mb-3 small d-flex flex-wrap align-items-center gap-3">
        <span class="fw-bold text-dark"><i class="bi bi-info-circle-fill text-primary me-1"></i> Alur Barang Masuk:</span>
        <span><span class="badge bg-success">1. Kotak Hijau</span> Beli barang dari luar (ketik nama barang, satuan, harga beli &amp; jumlah)</span>
        <i class="bi bi-arrow-right text-muted d-none d-md-inline"></i>
        <span><span class="badge bg-primary">2. Menu Tambah Produk</span> Pilih barang yang sudah dibeli tersebut untuk diberi harga jual &amp; tampil di kasir</span>
    </div>

    <!-- BARIS 1: KOTAK BIRU (SUPPLIER) & KOTAK HIJAU (BELI BARANG DARI LUAR) -->
    <div class="row g-3 mb-3">
        <!-- 1. KOTAK BIRU: MASTER DATA SUPPLIER -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white py-2 px-3">
                    <div class="fw-bold small"><i class="bi bi-building-add me-1"></i> 1. Tambah Distributor / Supplier</div>
                    <div class="opacity-75" style="font-size: 11px;">Master data perusahaan pemasok (isi jika supplier belum ada)</div>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <form action="{{ Route::has('admin.distributor.store') ? route('admin.distributor.store') : url('/admin/distributor') }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Nama Supplier / PT <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control form-control-sm" placeholder="Contoh: PT Sinar Jaya Material" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">No. Telepon / WA</label>
                            <input type="text" name="telepon" class="form-control form-control-sm" placeholder="0812xxxxxxxx">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Alamat Gudang / Kantor</label>
                            <textarea name="alamat" rows="2" class="form-control form-control-sm" placeholder="Alamat lengkap supplier..."></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                                <i class="bi bi-save me-1"></i> Simpan Supplier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 2. KOTAK HIJAU: PEMBELIAN BARANG DARI LUAR (MULTI-BARANG) -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold small"><i class="bi bi-cart-plus me-1"></i> 2. Stok Masuk / Pembelian Barang dari Luar</div>
                        <div class="opacity-75" style="font-size: 11px;">Isi harga beli lalu masukkan jumlah untuk menghitung total pembelian otomatis</div>
                    </div>
                    <button type="button" id="btn-tambah-baris" class="btn btn-light btn-sm fw-semibold text-success py-1 px-2">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Barang
                    </button>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <form action="{{ Route::has('admin.stok.masuk') ? route('admin.stok.masuk') : (Route::has('admin.stok.store') ? route('admin.stok.store') : url('/admin/stok/masuk')) }}" method="POST" id="form-stok-masuk">
                        @csrf
                        <div class="row g-2 mb-3">
                            <div class="col-md-7">
                                <label class="form-label small fw-semibold">Pilih Supplier</label>
                                <select name="distributor_id" class="form-select form-select-sm">
                                    <option value="">-- Umum / Tanpa Supplier --</option>
                                    @foreach($distributors ?? [] as $dist)
                                        <option value="{{ $dist->id }}">{{ $dist->nama_distributor ?? $dist->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">Total Pembelian</label>
                                <div class="form-control form-control-sm bg-light fw-bold text-success" id="grand-total-pembelian">
                                    Rp 0
                                </div>
                            </div>
                        </div>

                        <!-- Header Kolom Barang (Harga Beli dulu, lalu Jumlah) -->
                        <div class="row g-2 mb-1 d-none d-md-flex small fw-semibold text-muted px-1">
                            <div class="col-md-4">Nama Barang Dibeli <span class="text-danger">*</span></div>
                            <div class="col-md-2">Satuan</div>
                            <div class="col-md-2">Harga Beli <span class="text-danger">*</span></div>
                            <div class="col-md-2">Jumlah <span class="text-danger">*</span></div>
                            <div class="col-md-2 text-end">Subtotal</div>
                        </div>

                        <!-- Datalist Saran Barang yang Sudah Pernah Ada -->
                        <datalist id="datalist-barang-tersedia">
                            @foreach($produks ?? [] as $prd)
                                <option value="{{ $prd->nama_produk ?? $prd->nama }}"></option>
                            @endforeach
                            @foreach($gudangBelumProduk ?? [] as $gd)
                                <option value="{{ $gd->nama_barang }}"></option>
                            @endforeach
                        </datalist>

                        <!-- Daftar Baris Barang -->
                        <div id="daftar-item-pembelian" class="d-flex flex-column gap-2 mb-3">
                            <div class="row g-2 align-items-center baris-pembelian border rounded p-2 bg-light bg-opacity-50 mx-0">
                                <div class="col-md-4">
                                    <input type="text" list="datalist-barang-tersedia" name="items[0][nama_barang]" class="form-control form-control-sm input-nama-barang" placeholder="Ketik nama barang (misal: Paku)" required>
                                </div>
                                <div class="col-4 col-md-2">
                                    <input type="text" name="items[0][satuan]" class="form-control form-control-sm input-satuan" value="Pcs" placeholder="Pcs/KL/Sak">
                                </div>
                                <div class="col-4 col-md-2">
                                    <input type="number" step="any" name="items[0][harga_beli]" class="form-control form-control-sm input-harga-beli" min="0" placeholder="Rp 0" required>
                                </div>
                                <div class="col-4 col-md-2">
                                    <input type="number" step="any" name="items[0][jumlah]" class="form-control form-control-sm input-jumlah" min="1" placeholder="Qty" required>
                                </div>
                                <div class="col-12 col-md-2 d-flex justify-content-between justify-content-md-end align-items-center gap-2">
                                    <span class="small fw-semibold text-dark subtotal-text">Rp 0</span>
                                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-1 btn-hapus-baris" title="Hapus Baris">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <span class="text-muted" style="font-size: 11px;">* Setelah disimpan, klik <strong>Tambah Produk</strong> di menu Produk untuk memasukkannya ke kasir.</span>
                            <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold">
                                <i class="bi bi-check2-circle me-1"></i> Simpan Pembelian Barang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- BARIS 2: KOTAK MERAH (BARANG KELUAR) & POSISI STOK SAAT INI -->
    <div class="row g-3 mb-3">
        <!-- 3. KOTAK MERAH: BARANG KELUAR / RUSAK -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-danger text-white py-2 px-3">
                    <div class="fw-bold small"><i class="bi bi-dash-circle me-1"></i> 3. Barang Keluar / Rusak</div>
                    <div class="opacity-75" style="font-size: 11px;">Otomatis MENGURANGI stok (rusak, pecah, atau hilang)</div>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <form action="{{ Route::has('admin.stok.keluar') ? route('admin.stok.keluar') : url('/admin/stok/keluar') }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Pilih Material <span class="text-danger">*</span></label>
                            <select name="produk_id" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Material Bangunan --</option>
                                @foreach($produks ?? $materials ?? [] as $prd)
                                    @php $satuanPrd = $prd->satuan_dasar ?? $prd->satuan ?? 'Pcs'; @endphp
                                    <option value="{{ $prd->id }}">
                                        {{ $prd->nama_produk ?? $prd->nama }} (Sisa: {{ $prd->stok ?? 0 }} {{ $satuanPrd }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Jumlah Keluar <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="jumlah" class="form-control form-control-sm" min="1" placeholder="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Alasan / Keterangan <span class="text-danger">*</span></label>
                            <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Contoh: Semen mengeras / keramik pecah" required>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-danger btn-sm px-4 fw-semibold">
                                <i class="bi bi-dash-lg me-1"></i> Kurangi Stok
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Posisi Stok Material Gudang & Barang Baru Dibeli -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small text-success"><i class="bi bi-box-seam me-1"></i> Posisi Stok Gudang &amp; Barang Dibeli</span>
                    <span class="badge bg-light text-dark border">{{ count($gudangBelumProduk ?? []) + count($produks ?? []) }} Item</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Material</th>
                                <th>Harga Beli / Unit</th>
                                <th>Harga Jual</th>
                                <th>Stok</th>
                                <th class="text-end">Status / Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Barang yang sudah dibeli tapi belum ditambahkan ke halaman Produk -->
                            @foreach($gudangBelumProduk ?? [] as $gd)
                            <tr>
                                <td class="fw-semibold">{{ $gd->nama_barang }}</td>
                                <td>Rp {{ number_format($gd->harga_beli ?? 0, 0, ',', '.') }}</td>
                                <td class="text-muted fst-italic">Belum diatur</td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $gd->jumlah }} {{ $gd->satuan ?? 'Pcs' }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.produk.create', ['gudang_id' => $gd->id]) }}" class="btn btn-primary btn-sm py-0 px-2">
                                        <i class="bi bi-plus-circle me-1"></i> Tambah ke Produk
                                    </a>
                                </td>
                            </tr>
                            @endforeach

                            <!-- Produk yang sudah aktif di katalog kasir -->
                            @forelse($produks ?? $materials ?? [] as $prd)
                            @php
                                $hargaBeli = $prd->harga_beli_terakhir ?? $prd->hpp_rata_rata ?? $prd->harga_beli ?? 0;
                                $hargaJual = $prd->harga_jual ?? $prd->harga ?? 0;
                                $satuanPrd = $prd->satuan_dasar ?? $prd->satuan ?? 'Pcs';
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $prd->nama_produk ?? $prd->nama }}</td>
                                <td>Rp {{ number_format($hargaBeli, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($hargaJual, 0, ',', '.') }}</td>
                                <td>
                                    @if(($prd->stok ?? 0) <= 10)
                                        <span class="badge bg-danger">{{ $prd->stok ?? 0 }} {{ $satuanPrd }}</span>
                                    @else
                                        <span class="badge bg-success">{{ $prd->stok ?? 0 }} {{ $satuanPrd }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif di Kasir</span>
                                </td>
                            </tr>
                            @empty
                                @if(count($gudangBelumProduk ?? []) === 0)
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Belum ada barang. Input pembelian di Kotak Hijau.</td>
                                </tr>
                                @endif
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- BARIS 3: DAFTAR SUPPLIER TERDAFTAR -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small text-primary"><i class="bi bi-building me-1"></i> Daftar Supplier Terdaftar</span>
                    <span class="badge bg-light text-dark border">{{ count($distributors ?? []) }} Supplier</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Supplier</th>
                                <th>Telepon / WA</th>
                                <th>Alamat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($distributors ?? [] as $dist)
                            <tr>
                                <td class="fw-semibold">{{ $dist->nama_distributor ?? $dist->nama }}</td>
                                <td>{{ $dist->telepon ?? $dist->no_telp ?? '-' }}</td>
                                <td class="text-muted">{{ $dist->alamat ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Belum ada data supplier.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigasi Alur Langkah 3 -->
    @include('admin.partials.alur-nav', ['step' => 3])
</div>

<!-- Template Baris Pembelian Dinamis (Harga Beli dulu, lalu Jumlah) -->
<template id="template-baris-pembelian">
    <div class="row g-2 align-items-center baris-pembelian border rounded p-2 bg-light bg-opacity-50 mx-0">
        <div class="col-md-4">
            <input type="text" list="datalist-barang-tersedia" name="items[__INDEX__][nama_barang]" class="form-control form-control-sm input-nama-barang" placeholder="Ketik nama barang..." required>
        </div>
        <div class="col-4 col-md-2">
            <input type="text" name="items[__INDEX__][satuan]" class="form-control form-control-sm input-satuan" value="Pcs" placeholder="Pcs/KL/Sak">
        </div>
        <div class="col-4 col-md-2">
            <input type="number" step="any" name="items[__INDEX__][harga_beli]" class="form-control form-control-sm input-harga-beli" min="0" placeholder="Rp 0" required>
        </div>
        <div class="col-4 col-md-2">
            <input type="number" step="any" name="items[__INDEX__][jumlah]" class="form-control form-control-sm input-jumlah" min="1" placeholder="Qty" required>
        </div>
        <div class="col-12 col-md-2 d-flex justify-content-between justify-content-md-end align-items-center gap-2">
            <span class="small fw-semibold text-dark subtotal-text">Rp 0</span>
            <button type="button" class="btn btn-outline-danger btn-sm py-0 px-1 btn-hapus-baris" title="Hapus Baris">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container    = document.getElementById('daftar-item-pembelian');
    const btnTambah    = document.getElementById('btn-tambah-baris');
    const template     = document.getElementById('template-baris-pembelian');
    const grandTotalEl = document.getElementById('grand-total-pembelian');
    let rowIndex = 1;

    function formatRupiah(angka) {
        return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
    }

    function hitungTotal() {
        let grandTotal = 0;
        container.querySelectorAll('.baris-pembelian').forEach(function (row) {
            const harga = parseFloat(row.querySelector('.input-harga-beli').value) || 0;
            const qty = parseFloat(row.querySelector('.input-jumlah').value) || 0;
            const subtotal = harga * qty;
            row.querySelector('.subtotal-text').textContent = formatRupiah(subtotal);
            grandTotal += subtotal;
        });
        grandTotalEl.textContent = formatRupiah(grandTotal);
    }

    function ikatEventBaris(row) {
        const inputNama  = row.querySelector('.input-nama-barang');
        const inputHarga = row.querySelector('.input-harga-beli');
        const inputQty   = row.querySelector('.input-jumlah');
        const btnHapus   = row.querySelector('.btn-hapus-baris');

        inputHarga.addEventListener('input', hitungTotal);
        inputQty.addEventListener('input', hitungTotal);

        btnHapus.addEventListener('click', function () {
            const semuaBaris = container.querySelectorAll('.baris-pembelian');
            if (semuaBaris.length > 1) {
                row.remove();
                hitungTotal();
            } else {
                inputNama.value = '';
                inputHarga.value = '';
                inputQty.value = '';
                hitungTotal();
            }
        });
    }

    container.querySelectorAll('.baris-pembelian').forEach(ikatEventBaris);

    btnTambah.addEventListener('click', function () {
        const html = template.innerHTML.replace(/__INDEX__/g, rowIndex++);
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const barisBaru = wrapper.firstElementChild;
        container.appendChild(barisBaru);
        ikatEventBaris(barisBaru);
    });
});
</script>
@endsection