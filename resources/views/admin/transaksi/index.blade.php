@extends('layouts.admin')
@section('title', 'Monitoring & Pembatalan Transaksi POS')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Total Belanja</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksis as $trx)
                <tr>
                    <td class="fw-bold">#{{ $trx->kode_transaksi ?? $trx->id }}</td>
                    <td>{{ $trx->created_at }}</td>
                    <td>Rp {{ number_format($trx->total_harga ?? $trx->total ?? 0, 0, ',', '.') }}</td>
                    <td>
                        @if(($trx->status ?? 'selesai') === 'batal')
                            <span class="badge bg-danger">Dibatalkan</span>
                        @else
                            <span class="badge bg-success">Selesai</span>
                        @endif
                    </td>
                    <td>
                        @if(($trx->status ?? 'selesai') !== 'batal')
                        <form action="{{ route('admin.transaksi.batal', $trx->id) }}" method="POST" onsubmit="return confirm('Batalkan transaksi ini dan kembalikan stok ke gudang?')">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger">Batalkan Struk</button>
                        </form>
                        @else
                        <span class="text-muted small">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada transaksi tercatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('admin.partials.alur-nav', ['step' => 4])
@endsection