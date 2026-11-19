@extends('layouts.owner')
@section('title', 'Ringkasan Bisnis & Omzet')

@section('content')
<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <span class="text-muted small">Pantauan performa penjualan dan arus kas TB. Maju Jaya bulan {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}.</span>
        <a href="{{ route('owner.laporan') }}" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-printer me-1"></i> Buka &amp; Cetak Laporan
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #78350F !important;">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Omzet Kasir Hari Ini</small>
                    <h4 class="fw-bold text-primary mb-0">Rp {{ number_format($omzetHariIni, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #B45309 !important;">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Omzet Kasir Bulan Ini</small>
                    <h4 class="fw-bold text-dark mb-0">Rp {{ number_format($omzetBulanIni, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #DC2626 !important;">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Pengeluaran Bulan Ini</small>
                    <h4 class="fw-bold text-danger mb-0">Rp {{ number_format($pengeluaranBulanIni, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #15803D !important;">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Laba Bersih Bulan Ini</small>
                    <h4 class="fw-bold text-success mb-0">Rp {{ number_format($labaBersihBulanIni, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small text-primary"><i class="bi bi-receipt me-1"></i> Transaksi Penjualan Terbaru</span>
                    <a href="{{ route('owner.transaksi') }}" class="btn btn-outline-primary btn-sm py-0 px-2">Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>No. Struk / ID</th>
                                <th>Waktu</th>
                                <th>Total Belanja</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transaksiTerbaru as $trx)
                            <tr>
                                <td class="fw-semibold">{{ $trx->kode_transaksi ?? $trx->no_transaksi ?? '#TRX-' . $trx->id }}</td>
<td class="text-muted">{{ isset($trx->created_at) ? \Carbon\Carbon::parse($trx->created_at)->format('d/m/Y H:i') : '-' }}</td>
<td class="fw-bold">Rp {{ number_format($trx->total_akhir ?? $trx->total_bayar ?? $trx->total_harga ?? $trx->grand_total ?? $trx->total ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    @php $st = strtolower($trx->status ?? 'selesai'); @endphp
                                    @if(in_array($st, ['batal', 'dibatalkan', 'cancelled']))
                                        <span class="badge bg-danger">Batal</span>
                                    @else
                                        <span class="badge bg-success">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Belum ada transaksi tercatat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Stok Material Menipis (&le; 10)</span>
                    <a href="{{ route('owner.stok') }}" class="btn btn-outline-secondary btn-sm py-0 px-2">Cek Aset</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Material</th>
                                <th class="text-end">Sisa Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stokMenipis as $item)
                            <tr>
                                <td class="fw-semibold">{{ $item->nama_produk ?? $item->nama }}</td>
                                <td class="text-end">
                                    <span class="badge bg-danger">{{ $item->stok ?? 0 }} {{ $item->satuan ?? 'Pcs' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">Semua stok gudang dalam kondisi aman.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection