@extends('layouts.admin')
@section('title', 'Laporan Laba/Rugi & Riwayat Stok')

@section('content')
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        body {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 11pt;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Sembunyikan sidebar, navbar/topbar, dan elemen non-cetak */
        aside,
        nav,
        header,
        .sidebar,
        .navbar,
        .topbar,
        .d-print-none {
            display: none !important;
        }

        /* Lebarkan area konten laporan agar penuh 1 halaman */
        body > .d-flex,
        #app > .d-flex,
        .wrapper,
        main,
        .main-content,
        .content-wrapper,
        .container,
        .container-fluid {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Pastikan header kop & tanda tangan tetap sejajar kiri-kanan */
        .print-flex {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-end !important;
        }

        /* Jaga 3 kartu ringkasan tetap sejajar horizontal */
        .row {
            display: flex !important;
            flex-wrap: nowrap !important;
            gap: 10px;
        }
        .row > [class*="col-"] {
            flex: 1 1 0 !important;
            width: auto !important;
        }

        /* Mode cetak rangkuman: tampilkan seluruh 6 tab secara berurutan */
        body.print-all-reports .tab-content > .tab-pane {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            margin-bottom: 18px !important;
        }

        .card {
            border: 1px solid #cccccc !important;
            box-shadow: none !important;
            break-inside: auto;
        }

        .card-header {
            background-color: #f4f4f4 !important;
            border-bottom: 1px solid #999999 !important;
            break-after: avoid;
        }

        .table-responsive {
            overflow: visible !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        thead {
            display: table-header-group;
        }
        tr {
            break-inside: avoid;
        }
        table th,
        table td {
            border: 1px solid #999999 !important;
            padding: 6px 8px !important;
            font-size: 9.5pt;
        }
    }
</style>

<div class="container-fluid py-2">
    <!-- Header & Tombol Cetak (Hanya tampil di web) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 d-print-none">
        <span class="text-muted small">Laporan Laba/Rugi (Hasil Penjualan - Total Pembelian), Riwayat Mutasi Stok, dan Stok Akhir.</span>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" onclick="cetakTabAktif()" class="btn btn-outline-dark btn-sm px-3">
                <i class="bi bi-printer me-1"></i> Cetak Laporan Ini
            </button>
            <button type="button" onclick="cetakSemuaLaporan()" class="btn btn-dark btn-sm px-3">
                <i class="bi bi-file-earmark-text me-1"></i> Cetak Rangkuman (6 Laporan)
            </button>
        </div>
    </div>

    <!-- Kop Laporan (Hanya muncul saat dicetak) -->
    @php
        $namaBulanPrint = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',   '05' => 'Mei',      '06' => 'Juni',
            '07' => 'Juli',    '08' => 'Agustus',  '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];
        $labelBulan = (!empty($bulan) && isset($namaBulanPrint[$bulan])) ? $namaBulanPrint[$bulan] : 'Semua Bulan';
        $labelTahun = (!empty($tahun) && $tahun !== 'semua') ? $tahun : 'Semua Tahun';
    @endphp
    <div class="d-none d-print-block mb-4 pb-2" style="border-bottom: 3px double #000;">
        <div class="text-center">
            <h3 class="fw-bold mb-0" style="letter-spacing: 1px;">TB. MAJU JAYA</h3>
            <div class="small">Toko Material &amp; Bahan Bangunan</div>
        </div>
        <hr style="border-top: 1px solid #000; margin: 8px 0;">
        <div class="print-flex">
            <div>
                <h5 id="print-judul-laporan" class="fw-bold mb-0">LAPORAN LABA / RUGI</h5>
                <div class="small">Periode: <strong>{{ $labelBulan }} ({{ $labelTahun }})</strong></div>
            </div>
            <div class="text-end small">
                <div>Dicetak oleh: <strong>{{ auth()->user()->name ?? 'Administrator Toko' }}</strong></div>
                <div>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }} WIB</div>
            </div>
        </div>
    </div>

    <!-- Filter Per Bulan & Tahun -->
    <div class="card border-0 shadow-sm mb-3 d-print-none">
        <div class="card-body py-2">
            <form action="{{ route('admin.laporan.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Filter Bulan</label>
                    <select name="bulan" class="form-select form-select-sm">
                        <option value="">-- Semua Bulan --</option>
                        @php
                            $daftarBulan = [
                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                '04' => 'April',   '05' => 'Mei',      '06' => 'Juni',
                                '07' => 'Juli',    '08' => 'Agustus',  '09' => 'September',
                                '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                            ];
                        @endphp
                        @foreach($daftarBulan as $kode => $namaBln)
                            <option value="{{ $kode }}" {{ $bulan == $kode ? 'selected' : '' }}>{{ $namaBln }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Filter Tahun</label>
                    <select name="tahun" class="form-select form-select-sm">
                        <option value="semua" {{ $tahun == 'semua' ? 'selected' : '' }}>-- Semua Tahun --</option>
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-funnel me-1"></i> Tampilkan
                    </button>
                    <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Kartu Ringkasan Laba / Rugi -->
    <div id="ringkasan-laba-rugi" class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="text-muted small">Hasil Penjualan</div>
                    <h4 class="fw-bold text-primary mb-1">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h4>
                    <span class="badge bg-light text-dark border">{{ $penjualans->count() }} Transaksi Penjualan</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="text-muted small">Total Pembelian</div>
                    <h4 class="fw-bold text-dark mb-1">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</h4>
                    <span class="badge bg-light text-dark border">{{ $pembelians->count() }} Transaksi Pembelian</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 {{ $labaRugi >= 0 ? 'border-success' : 'border-danger' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Laporan Laba / Rugi</span>
                        <span class="badge {{ $labaRugi >= 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ $statusLaba }}
                        </span>
                    </div>
                    <h4 class="fw-bold {{ $labaRugi >= 0 ? 'text-success' : 'text-danger' }} mb-1">
                        {{ $labaRugi < 0 ? '-' : '' }}Rp {{ number_format(abs($labaRugi), 0, ',', '.') }}
                    </h4>
                    <div class="text-muted" style="font-size: 11px;">Rumus: Hasil Penjualan - Total Pembelian</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigasi -->
    <ul class="nav nav-tabs mb-3 d-print-none" id="laporanTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#pane-labarugi" type="button" role="tab">
                1. Laporan Laba / Rugi
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#pane-riwayat-stok" type="button" role="tab">
                2. Riwayat Stok ({{ $riwayatStok->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#pane-stok-akhir" type="button" role="tab">
                3. Stok Akhir ({{ $produks->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#pane-penjualan" type="button" role="tab">
                4. Data Penjualan ({{ $penjualans->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#pane-pembelian" type="button" role="tab">
                5. Data Pembelian ({{ $pembelians->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#pane-pengeluaran" type="button" role="tab">
                6. Data Pengeluaran ({{ $pengeluarans->count() }})
            </button>
        </li>
    </ul>

    <div class="tab-content" id="laporanTabContent">
        <!-- TAB 1: LAPORAN LABA / RUGI -->
        <div class="tab-pane fade show active" id="pane-labarugi" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-2">
                    1. Rekapitulasi Laba / Rugi
                    ({{ $bulan ? ($daftarBulan[$bulan] ?? $bulan) : 'Semua Bulan' }} {{ $tahun === 'semua' ? 'Semua Tahun' : $tahun }})
                </div>
                <div class="card-body">
                    <table class="table table-bordered align-middle mb-0">
                        <tbody>
                            <tr>
                                <td class="fw-semibold" width="60%">Total Hasil Penjualan</td>
                                <td class="text-end text-primary fw-bold">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Total Pembelian Produk</td>
                                <td class="text-end text-danger fw-bold">- Rp {{ number_format($totalPembelian, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="{{ $labaRugi >= 0 ? 'table-success' : 'table-danger' }}">
                                <td class="fw-bold fs-6">
                                    Selisih (Hasil Penjualan - Total Pembelian) —
                                    <span class="badge {{ $labaRugi >= 0 ? 'bg-success' : 'bg-danger' }}">{{ $statusLaba }}</span>
                                </td>
                                <td class="text-end fw-bold fs-6 {{ $labaRugi >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $labaRugi < 0 ? '-' : '' }}Rp {{ number_format(abs($labaRugi), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="text-muted small mt-2">
                        * Keterangan: Jika hasil bernilai positif (+) maka status <strong>LABA</strong>, jika bernilai negatif (-) maka status <strong>RUGI</strong>.
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: RIWAYAT STOK (BARANG TERJUAL, PEMBELIAN, KELUAR) -->
        <div class="tab-pane fade" id="pane-riwayat-stok" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-2">2. Riwayat Stok (Pembelian, Barang Terjual &amp; Barang Keluar)</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Tanggal</th>
                                    <th>Nama Produk</th>
                                    <th>Aktivitas Stok</th>
                                    <th class="text-center">Jumlah</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riwayatStok as $i => $rw)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $rw->tanggal ? \Carbon\Carbon::parse($rw->tanggal)->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="fw-semibold">{{ $rw->produk }}</td>
                                    <td>
                                        @if($rw->tipe === 'masuk')
                                            <span class="badge bg-success">{{ $rw->jenis }}</span>
                                        @elseif($rw->tipe === 'terjual')
                                            <span class="badge bg-primary">{{ $rw->jenis }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ $rw->jenis }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold {{ $rw->tipe === 'masuk' ? 'text-success' : 'text-danger' }}">
                                        {{ $rw->tipe === 'masuk' ? '+' : '-' }}{{ $rw->qty }} {{ $rw->satuan }}
                                    </td>
                                    <td class="small text-muted">{{ $rw->keterangan }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">Belum ada riwayat mutasi stok pada periode ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: STOK AKHIR -->
        <div class="tab-pane fade" id="pane-stok-akhir" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-2">3. Laporan Stok Akhir Material / Produk</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Kategori</th>
                                    <th>Nama Produk</th>
                                    <th class="text-end">Harga Beli</th>
                                    <th class="text-end">Harga Jual</th>
                                    <th class="text-center">Stok Akhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($produks as $i => $p)
                                @php
                                    $namaPrd = $p->nama_produk ?? $p->nama ?? '-';
                                    $satPrd  = $p->satuan_dasar ?? $p->satuan ?? 'Pcs';
                                    $hrgBeli = $p->harga_beli ?? 0;
                                    $hrgJual = $p->harga_jual ?? $p->harga ?? 0;
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $p->nama_kategori ?? 'Umum' }}</span></td>
                                    <td class="fw-semibold">{{ $namaPrd }}</td>
                                    <td class="text-end">Rp {{ number_format($hrgBeli, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($hrgJual, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $p->stok <= 10 ? 'bg-danger' : 'bg-success' }}">
                                            {{ $p->stok }} {{ $satPrd }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">Data produk masih kosong.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: DATA PENJUALAN -->
        <div class="tab-pane fade" id="pane-penjualan" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-2">4. Data Transaksi Penjualan</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Tanggal</th>
                                    <th>No. Nota / Transaksi</th>
                                    <th>Data Pembeli</th>
                                    <th>Status</th>
                                    <th class="text-end">Total Penjualan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($penjualans as $i => $trx)
                                @php
                                    $kodeTrx  = $trx->nomor_transaksi ?? $trx->kode_transaksi ?? $trx->no_faktur ?? ('TRX-' . $trx->id);
                                    $pembeli  = $trx->nama_pembeli ?? $trx->pembeli ?? $trx->pelanggan ?? 'Pelanggan Umum';
                                    $totalTrx = $trx->total_harga ?? $trx->grand_total ?? $trx->total ?? 0;
                                    $status   = $trx->status ?? 'selesai';
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $trx->created_at ? \Carbon\Carbon::parse($trx->created_at)->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="fw-semibold">{{ $kodeTrx }}</td>
                                    <td>{{ $pembeli }}</td>
                                    <td>
                                        <span class="badge {{ $status === 'batal' ? 'bg-danger' : 'bg-success' }}">{{ ucfirst($status) }}</span>
                                    </td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($totalTrx, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">Belum ada data penjualan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 5: DATA PEMBELIAN -->
        <div class="tab-pane fade" id="pane-pembelian" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-2">5. Data Pembelian Produk ke Distributor</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Tanggal</th>
                                    <th>No. Pembelian</th>
                                    <th>Distributor</th>
                                    <th class="text-end">Total Pembelian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pembelians as $i => $beli)
                                @php
                                    $noBeli   = $beli->nomor_pembelian ?? $beli->kode_pembelian ?? ('PB-' . $beli->id);
                                    $distObj  = isset($beli->distributor_id) ? ($distributorMap[$beli->distributor_id] ?? null) : null;
                                    $namaDist = $distObj ? ($distObj->nama_distributor ?? $distObj->nama ?? '-') : 'Umum / Tanpa Distributor';
                                    $totBeli  = $beli->total_harga ?? $beli->total ?? 0;
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $beli->created_at ? \Carbon\Carbon::parse($beli->created_at)->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="fw-semibold">{{ $noBeli }}</td>
                                    <td>{{ $namaDist }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($totBeli, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">Belum ada data pembelian.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 6: DATA PENGELUARAN -->
        <div class="tab-pane fade" id="pane-pengeluaran" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-2">6. Data Pengeluaran Operasional</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pengeluarans as $i => $out)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $out->created_at ? \Carbon\Carbon::parse($out->created_at)->format('d/m/Y H:i') : '-' }}</td>
                                    <td>{{ $out->keterangan ?? $out->deskripsi ?? '-' }}</td>
                                    <td class="text-end fw-semibold text-danger">Rp {{ number_format($out->nominal ?? $out->jumlah ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">Belum ada data pengeluaran.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Tanda Tangan (Hanya muncul saat dicetak) -->
    <div class="d-none d-print-block mt-5" style="break-inside: avoid;">
        <div class="print-flex text-center pt-3">
            <div style="width: 220px;">
                <p class="mb-5">Mengetahui,<br><strong>Pemilik Toko</strong></p>
                <p class="mb-0 mt-4">( _______________________ )</p>
            </div>
            <div style="width: 220px;">
                <p class="mb-5">Dibuat Oleh,<br><strong>Administrator Toko</strong></p>
                <p class="mb-0 mt-4">( {{ auth()->user()->name ?? 'Administrator Toko' }} )</p>
            </div>
        </div>
    </div>
</div>

<script>
    const printJudul = document.getElementById('print-judul-laporan');
    const ringkasanLabaRugi = document.getElementById('ringkasan-laba-rugi');

    const mapJudulTab = {
        '#pane-labarugi': 'LAPORAN LABA / RUGI',
        '#pane-riwayat-stok': 'LAPORAN RIWAYAT MUTASI STOK',
        '#pane-stok-akhir': 'LAPORAN STOK AKHIR MATERIAL / PRODUK',
        '#pane-penjualan': 'LAPORAN DATA TRANSAKSI PENJUALAN',
        '#pane-pembelian': 'LAPORAN DATA PEMBELIAN PRODUK',
        '#pane-pengeluaran': 'LAPORAN DATA PENGELUARAN OPERASIONAL'
    };

    function sesuaikanTampilanTabAktif() {
        document.body.classList.remove('print-all-reports');
        const activeTab = document.querySelector('#laporanTab .nav-link.active');
        const target = activeTab ? activeTab.getAttribute('data-bs-target') : '#pane-labarugi';

        if (printJudul) {
            printJudul.textContent = mapJudulTab[target] || 'LAPORAN LABA / RUGI & RIWAYAT STOK';
        }

        if (ringkasanLabaRugi) {
            if (target === '#pane-labarugi') {
                ringkasanLabaRugi.classList.remove('d-print-none');
            } else {
                ringkasanLabaRugi.classList.add('d-print-none');
            }
        }
    }

    function cetakTabAktif() {
        sesuaikanTampilanTabAktif();
        window.print();
    }

    function cetakSemuaLaporan() {
        document.body.classList.add('print-all-reports');
        if (printJudul) {
            printJudul.textContent = 'RANGKUMAN LAPORAN KEUANGAN, STOK & TRANSAKSI';
        }
        if (ringkasanLabaRugi) {
            ringkasanLabaRugi.classList.remove('d-print-none');
        }
        window.print();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#laporanTab button[data-bs-toggle="tab"]').forEach(function (btn) {
            btn.addEventListener('shown.bs.tab', sesuaikanTampilanTabAktif);
        });

        window.addEventListener('afterprint', sesuaikanTampilanTabAktif);
        sesuaikanTampilanTabAktif();
    });
</script>
@endsection