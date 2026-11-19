@extends('layouts.owner')
@section('title', 'Pantau Aset & Stok Gudang')

@section('content')
<div class="container-fluid py-2">
    <!-- Ringkasan Nilai Aset Barang -->
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted d-block">Total Nilai Modal Stok di Gudang</small>
                    <h4 class="fw-bold text-primary mb-0">Rp {{ number_format($totalAsetModal, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted d-block">Estimasi Nilai Jual Keseluruhan Stok</small>
                    <h4 class="fw-bold text-success mb-0">Rp {{ number_format($potensiOmzet, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Stok Material -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 px-3">
            <span class="fw-bold small text-primary"><i class="bi bi-boxes me-1"></i> Ketersediaan Stok Material (Diurutkan dari Paling Sedikit)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Nama Material</th>
                        <th>Harga Modal</th>
                        <th>Harga Jual</th>
                        <th>Sisa Stok</th>
                        <th class="text-end">Nilai Modal Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produks as $prd)
                    @php
    $hargaModal = (float) ($prd->harga_beli_terakhir ?? $prd->hpp_rata_rata ?? $prd->harga_beli ?? $prd->harga_modal ?? 0);
    $hargaJual  = (float) ($prd->harga_jual ?? $prd->harga ?? 0);
    $stok       = (float) ($prd->stok ?? 0);
    $satuan     = $prd->satuan_dasar ?? $prd->satuan ?? 'Pcs';
    $nilaiModal = $hargaModal * $stok;
@endphp
<tr>
    <td class="fw-semibold">{{ $prd->nama_produk ?? $prd->nama }}</td>
    <td>Rp {{ number_format($hargaModal, 0, ',', '.') }}</td>
    <td>Rp {{ number_format($hargaJual, 0, ',', '.') }}</td>
    <td>
        <span class="badge {{ $stok <= 10 ? 'bg-danger' : 'bg-success' }}">
            {{ number_format($stok, 2, '.', ',') }} {{ $satuan }}
        </span>
    </td>
    <td class="text-end fw-bold">Rp {{ number_format($nilaiModal, 0, ',', '.') }}</td>
</tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada data material.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection