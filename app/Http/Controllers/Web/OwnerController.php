<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class OwnerController extends Controller
{
    private function resolveTable(array $candidates): ?string
    {
        $firstExisting = null;
        foreach ($candidates as $table) {
            if (Schema::hasTable($table)) {
                if ($firstExisting === null) {
                    $firstExisting = $table;
                }
                if (DB::table($table)->count() > 0) {
                    return $table;
                }
            }
        }
        return $firstExisting;
    }

    private function resolveDateColumn(string $table): ?string
    {
        foreach (['created_at', 'tanggal_penjualan', 'tanggal'] as $col) {
            if (Schema::hasColumn($table, $col)) {
                return $col;
            }
        }
        return null;
    }

    private function sumColumn(?string $table, array $colCandidates, ?string $dateStart = null, ?string $dateEnd = null): float
    {
        if (!$table) return 0;

        $col = null;
        foreach ($colCandidates as $c) {
            if (Schema::hasColumn($table, $c)) {
                $col = $c;
                break;
            }
        }
        if (!$col) return 0;

        $query = DB::table($table);

        // Kecualikan transaksi yang dibatalkan jika ada kolom status
        if (Schema::hasColumn($table, 'status')) {
            $query->whereNotIn('status', ['batal', 'dibatalkan', 'cancelled']);
        }

        $dateCol = $this->resolveDateColumn($table);
        if ($dateStart && $dateEnd && $dateCol) {
            $query->whereBetween($dateCol, [
                Carbon::parse($dateStart)->startOfDay(),
                Carbon::parse($dateEnd)->endOfDay(),
            ]);
        }

        return (float) $query->sum($col);
    }

    public function dashboard()
    {
        $today = Carbon::today()->toDateString();
        $startMonth = Carbon::now()->startOfMonth()->toDateString();
        $endMonth = Carbon::now()->endOfMonth()->toDateString();

        $tblTransaksi   = $this->resolveTable(['penjualans', 'penjualan', 'transaksis', 'transaksi']);
        $tblPemasukan   = $this->resolveTable(['pemasukan_lains', 'pemasukan_lain', 'kas_masuks', 'kas_masuk']);
        $tblPengeluaran = $this->resolveTable(['pengeluarans', 'pengeluaran']);
        $tblProduk      = $this->resolveTable(['produks', 'produk']);

        $totalCols = ['total_bayar', 'total_harga', 'grand_total', 'total', 'total_kotor', 'bayar'];

        $omzetHariIni = $this->sumColumn($tblTransaksi, $totalCols, $today, $today);
        $omzetBulanIni = $this->sumColumn($tblTransaksi, $totalCols, $startMonth, $endMonth);
        $pemasukanLainBulanIni = $this->sumColumn($tblPemasukan, ['nominal', 'jumlah'], $startMonth, $endMonth);
        $pengeluaranBulanIni = $this->sumColumn($tblPengeluaran, ['nominal', 'jumlah'], $startMonth, $endMonth);

        $labaBersihBulanIni = ($omzetBulanIni + $pemasukanLainBulanIni) - $pengeluaranBulanIni;

        $transaksiTerbaru = $tblTransaksi
            ? DB::table($tblTransaksi)->orderByDesc('id')->limit(6)->get()
            : collect();

        $stokMenipis = $tblProduk
            ? DB::table($tblProduk)->where('stok', '<=', 10)->orderBy('stok', 'asc')->limit(6)->get()
            : collect();

        return view('owner.dashboard', compact(
            'omzetHariIni',
            'omzetBulanIni',
            'pemasukanLainBulanIni',
            'pengeluaranBulanIni',
            'labaBersihBulanIni',
            'transaksiTerbaru',
            'stokMenipis'
        ));
    }

    public function laporan(Request $request)
    {
        $dari = $request->input('dari', Carbon::now()->startOfMonth()->toDateString());
        $sampai = $request->input('sampai', Carbon::today()->toDateString());

        $tblTransaksi   = $this->resolveTable(['penjualans', 'penjualan', 'transaksis', 'transaksi']);
        $tblPemasukan   = $this->resolveTable(['pemasukan_lains', 'pemasukan_lain', 'kas_masuks', 'kas_masuk']);
        $tblPengeluaran = $this->resolveTable(['pengeluarans', 'pengeluaran']);

        $totalCols = ['total_bayar', 'total_harga', 'grand_total', 'total', 'total_kotor', 'bayar'];

        $totalPenjualan = $this->sumColumn($tblTransaksi, $totalCols, $dari, $sampai);
        $totalPemasukanLain = $this->sumColumn($tblPemasukan, ['nominal', 'jumlah'], $dari, $sampai);
        $totalPengeluaran = $this->sumColumn($tblPengeluaran, ['nominal', 'jumlah'], $dari, $sampai);
        $labaBersih = ($totalPenjualan + $totalPemasukanLain) - $totalPengeluaran;

        $dateColTransaksi = $tblTransaksi ? $this->resolveDateColumn($tblTransaksi) : null;
        $listTransaksi = $tblTransaksi
            ? DB::table($tblTransaksi)
                ->when($dateColTransaksi, function ($q) use ($dateColTransaksi, $dari, $sampai) {
                    $q->whereBetween($dateColTransaksi, [Carbon::parse($dari)->startOfDay(), Carbon::parse($sampai)->endOfDay()]);
                })
                ->orderByDesc('id')
                ->get()
            : collect();

        $dateColPengeluaran = $tblPengeluaran ? $this->resolveDateColumn($tblPengeluaran) : null;
        $listPengeluaran = $tblPengeluaran
            ? DB::table($tblPengeluaran)
                ->when($dateColPengeluaran, function ($q) use ($dateColPengeluaran, $dari, $sampai) {
                    $q->whereBetween($dateColPengeluaran, [Carbon::parse($dari)->startOfDay(), Carbon::parse($sampai)->endOfDay()]);
                })
                ->orderByDesc('id')
                ->get()
            : collect();

        $dateColPemasukan = $tblPemasukan ? $this->resolveDateColumn($tblPemasukan) : null;
        $listPemasukanLain = $tblPemasukan
            ? DB::table($tblPemasukan)
                ->when($dateColPemasukan, function ($q) use ($dateColPemasukan, $dari, $sampai) {
                    $q->whereBetween($dateColPemasukan, [Carbon::parse($dari)->startOfDay(), Carbon::parse($sampai)->endOfDay()]);
                })
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('owner.laporan', compact(
            'dari',
            'sampai',
            'totalPenjualan',
            'totalPemasukanLain',
            'totalPengeluaran',
            'labaBersih',
            'listTransaksi',
            'listPengeluaran',
            'listPemasukanLain'
        ));
    }

    public function transaksi()
    {
        $tblTransaksi = $this->resolveTable(['penjualans', 'penjualan', 'transaksis', 'transaksi']);
        $transaksis = $tblTransaksi
            ? DB::table($tblTransaksi)->orderByDesc('id')->paginate(15)
            : collect();

        return view('owner.transaksi', compact('transaksis'));
    }

    public function stok()
    {
        $tblProduk = $this->resolveTable(['produks', 'produk']);
        $produks = $tblProduk
            ? DB::table($tblProduk)->orderBy('stok', 'asc')->get()
            : collect();

        $totalAsetModal = $produks->sum(function ($item) {
            $hargaModal = $item->harga_beli_terakhir ?? $item->hpp_rata_rata ?? $item->harga_beli ?? 0;
            return ($item->stok ?? 0) * $hargaModal;
        });

        $potensiOmzet = $produks->sum(function ($item) {
            return ($item->stok ?? 0) * ($item->harga_jual ?? $item->harga ?? 0);
        });

        return view('owner.stok', compact('produks', 'totalAsetModal', 'potensiOmzet'));
    }
}