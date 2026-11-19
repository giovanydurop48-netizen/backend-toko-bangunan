@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Laporan Transaksi & Keuangan</h3>
        <p class="text-muted small">Ringkasan hasil penjualan kasir berdasarkan rentang tanggal.</p>
    </div>
    <a href="{{ route('laporan.cetak', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="btn btn-outline-danger fw-bold">
        <i class="bi bi-printer me-1"></i> Cetak Laporan PDF / Print
    </a>
</div>

<!-- Filter Periode -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <form action="{{ route('laporan.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary fw-bold w-100"><i class="bi bi-filter"></i> Filter Data</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 bg-primary text-white p-3 rounded-3 shadow-sm">
            <div class="small text-white-50 text-uppercase fw-bold">Total Omset Penjualan</div>
            <h2 class="fw-bold mb-0">Rp {{ number_format($totalOmset, 0, ',', '.') }}</h2>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 bg-dark text-white p-3 rounded-3 shadow-sm">
            <div class="small text-white-50 text-uppercase fw-bold">Total Transaksi</div>
            <h2 class="fw-bold mb-0">{{ $totalTransaksi }} Transaksi</h2>
        </div>
    </div>
</div>

<!-- Tabel Laporan -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Waktu & Tanggal</th>
                    <th>Kode Transaksi</th>
                    <th>Kasir</th>
                    <th>Diskon</th>
                    <th>Total Bayar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksi as $trx)
                <tr>
                    <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                    <td class="fw-bold text-primary">{{ $trx->kode_transaksi ?? 'TRX-'.$trx->id }}</td>
                    <td>{{ $trx->user->name ?? 'Kasir' }}</td>
                    <td>{{ $trx->diskon_persen }}%</td>
                    <td class="fw-bold">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</td>
                </tr>
               @empty
        <tr>
            <td colspan="5" class="text-center py-4 text-muted">Tidak ada transaksi pada periode ini.</td>
        </tr>
        @endforelse
    </tbody>
            </tbody>
        </table>
    </div>
</div>
@endsection