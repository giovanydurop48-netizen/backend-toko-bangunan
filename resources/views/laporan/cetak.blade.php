<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Penjualan Toko Bangunan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
        }
        body { padding: 30px; background: #fff; }
    </style>
</head>
<body onload="window.print()">
    <div class="text-center mb-4">
        <h2 class="fw-bold mb-1">TOKO BANGUNAN POS</h2>
        <p class="mb-0">Laporan Rekapitulasi Penjualan Kasir</p>
        <small class="text-muted">Periode: {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}</small>
    </div>

    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Kode TRX</th>
                <th>Kasir</th>
                <th>Diskon (%)</th>
                <th>Total Omset (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi as $index => $trx)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $trx->kode_transaksi ?? 'TRX-'.$trx->id }}</td>
                <td>{{ $trx->user->name ?? 'Kasir' }}</td>
                <td>{{ $trx->diskon_persen }}%</td>
                <td class="text-end">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-end">TOTAL KESELURUHAN</th>
                <th class="text-end fw-bold">Rp {{ number_format($totalOmset, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="row mt-5">
        <div class="col-8"></div>
        <div class="col-4 text-center">
            <p>Penanggung Jawab / Owner,</p>
            <br><br><br>
            <p class="fw-bold">( _______________________ )</p>
        </div>
    </div>

    <div class="no-print mt-4 text-center">
        <button onclick="window.print()" class="btn btn-primary">Cetak Sekarang</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>
</body>
</html>