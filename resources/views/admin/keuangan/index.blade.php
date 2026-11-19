@extends('layouts.admin')
@section('title', 'Pencatatan Pengeluaran & Pemasukan Lain')

@section('content')
<div class="row g-4">
    <!-- Pengeluaran Operasional -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold text-danger">Catat Pengeluaran Operasional</div>
            <div class="card-body">
                <form action="{{ route('admin.pengeluaran.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Keterangan Pengeluaran</label>
                        <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Contoh: Bayar Listrik / BBM Truk" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Nominal (Rp)</label>
                        <input type="number" name="nominal" class="form-control form-control-sm" min="1" required>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-danger btn-sm px-4">Simpan Pengeluaran</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Riwayat Pengeluaran Terakhir</div>
            <ul class="list-group list-group-flush">
                @forelse($pengeluarans as $out)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $out->keterangan ?? $out->deskripsi }}</span>
                        <strong class="text-danger">- Rp {{ number_format($out->nominal ?? $out->jumlah ?? 0, 0, ',', '.') }}</strong>
                    </li>
                @empty
                    <li class="list-group-item text-muted text-center small">Belum ada data.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Pemasukan Lain -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold text-success">Catat Pemasukan Tambahan</div>
            <div class="card-body">
                <form action="{{ route('admin.pemasukan_lain.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Keterangan Pemasukan</label>
                        <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Contoh: Biaya Ongkir / Jasa Potong Besi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Nominal (Rp)</label>
                        <input type="number" name="nominal" class="form-control form-control-sm" min="1" required>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-success btn-sm px-4">Simpan Pemasukan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Riwayat Pemasukan Tambahan</div>
            <ul class="list-group list-group-flush">
                @forelse($pemasukanLains as $in)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $in->keterangan ?? $in->deskripsi }}</span>
                        <strong class="text-success">+ Rp {{ number_format($in->nominal ?? $in->jumlah ?? 0, 0, ',', '.') }}</strong>
                    </li>
                @empty
                    <li class="list-group-item text-muted text-center small">Belum ada data.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

@include('admin.partials.alur-nav', ['step' => 5])
@endsection