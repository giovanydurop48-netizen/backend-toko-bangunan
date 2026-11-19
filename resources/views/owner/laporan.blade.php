@extends('layouts.owner')
@section('title', 'Laporan Keuangan & Laba Rugi')

@section('content')
<div class="container-fluid py-2">
    <!-- Filter Tanggal & Tombol Cetak (Tersembunyi saat dicetak) -->
    <div class="card border-0 shadow-sm mb-3 no-print">
        <div class="card-body py-2 px-3">
            <form action="{{ route('owner.laporan') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Dari Tanggal</label>
                    <input type="date" name="dari" value="{{ $dari }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
                    <input type="date" name="sampai" value="{{ $sampai }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-6 d-flex justify-content-md-end gap-2 mt-2 mt-md-0">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-funnel me-1"></i> Tampilkan
                    </button>
                    <button type="button" onclick="window.print()" class="btn btn-brown btn-sm px-3">
                        <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Kop Laporan (Muncul rapi di layar maupun saat cetak PDF) -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="text-center border-bottom pb-3 mb-3">
                <h5 class="fw-bold mb-1">LAPORAN KEUANGAN TB. MAJU JAYA</h5>
                <div class="small text-muted">Periode: {{ \Carbon\Carbon::parse($dari)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d M Y') }}</div>
            </div>

            <!-- Ringkasan Laba Rugi -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="p-3 rounded bg-light border">
                        <small class="text-muted d-block">Penjualan POS</small>
                        <strong class="fs-6 text-primary">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 rounded bg-light border">
                        <small class="text-muted d-block">Pemasukan Tambahan</small>
                        <strong class="fs-6 text-success">Rp {{ number_format($totalPemasukanLain, 0, ',', '.') }}</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 rounded bg-light border">
                        <small class="text-muted d-block">Pengeluaran Operasional</small>
                        <strong class="fs-6 text-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 rounded bg-light border">
                        <small class="text-muted d-block">Laba / Kas Bersih</small>
                        <strong class="fs-6 text-dark">Rp {{ number_format($labaBersih, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Rincian Pengeluaran & Pemasukan Tambahan -->
            <div class="row g-3">
                <div class="col-md-6">
                    <h6 class="fw-bold small text-danger mb-2">Rincian Pengeluaran Operasional</h6>
                    <table class="table table-bordered table-sm small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($listPengeluaran as $out)
                            <tr>
                                <td>{{ isset($out->created_at) ? \Carbon\Carbon::parse($out->created_at)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $out->keterangan ?? $out->deskripsi ?? '-' }}</td>
                                <td class="text-end text-danger">Rp {{ number_format($out->nominal ?? $out->jumlah ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-2">Tidak ada pengeluaran pada periode ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="col-md-6">
                    <h6 class="fw-bold small text-success mb-2">Rincian Pemasukan Tambahan</h6>
                    <table class="table table-bordered table-sm small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($listPemasukanLain as $in)
                            <tr>
                                <td>{{ isset($in->created_at) ? \Carbon\Carbon::parse($in->created_at)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $in->keterangan ?? $in->deskripsi ?? '-' }}</td>
                                <td class="text-end text-success">Rp {{ number_format($in->nominal ?? $in->jumlah ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-2">Tidak ada pemasukan tambahan pada periode ini.</td>
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