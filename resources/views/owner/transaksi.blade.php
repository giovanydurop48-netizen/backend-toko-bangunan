@extends('layouts.owner')
@section('title', 'Riwayat Penjualan Kasir')

@section('content')
<div class="container-fluid py-2">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 px-3">
            <span class="fw-bold small text-primary"><i class="bi bi-receipt-cutoff me-1"></i> Seluruh Transaksi POS</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Tanggal &amp; Jam</th>
                        <th>Total Belanja</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                   @forelse($transaksis as $trx)
                        @php
                            $kodeTrx    = $trx->kode_transaksi ?? $trx->no_transaksi ?? $trx->nomor_faktur ?? $trx->kode_penjualan ?? ('#TRX-' . $trx->id);
                            $tanggalTrx = $trx->created_at ?? $trx->tanggal_penjualan ?? $trx->tanggal ?? null;
                            $totalTrx   = $trx->total_bayar ?? $trx->total_harga ?? $trx->total_akhir ?? $trx->grand_total ?? $trx->total ?? $trx->total_kotor ?? 0;
                            $st         = strtolower($trx->status ?? 'selesai');
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $kodeTrx }}</td>
                            <td class="text-muted">{{ $tanggalTrx ? \Carbon\Carbon::parse($tanggalTrx)->format('d M Y, H:i') : '-' }}</td>
                            <td class="fw-bold">Rp {{ number_format($totalTrx, 0, ',', '.') }}</td>
                            <td>
                                @if(in_array($st, ['batal', 'dibatalkan', 'cancelled']))
                                    <span class="badge bg-danger">Dibatalkan</span>
                                @else
                                    <span class="badge bg-success">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Belum ada data transaksi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($transaksis, 'links'))
        <div class="card-footer bg-white py-2">
            {{ $transaksis->links() }}
        </div>
        @endif
    </div>
</div>
@endsection