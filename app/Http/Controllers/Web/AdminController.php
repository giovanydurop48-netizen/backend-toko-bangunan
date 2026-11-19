<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    private function getProductTable(): string
    {
        return 'produk';
    }

    private function getCategoryTable(): string
    {
        return Schema::hasTable('kategoris') ? 'kategoris' : 'kategori';
    }

    private function getSaleTable(): string
    {
        return Schema::hasTable('penjualans') ? 'penjualans' : 'penjualan';
    }

    private function ensureGudangTable(): string
    {
        $table = 'gudang_pembelians';
        if (!Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('distributor_id')->nullable();
                $t->unsignedBigInteger('produk_id')->nullable();
                $t->string('nama_barang');
                $t->string('satuan', 50)->default('Pcs');
                $t->decimal('jumlah', 12, 2)->default(0);
                $t->decimal('harga_beli', 15, 2)->default(0);
                $t->boolean('sudah_di_produk')->default(false);
                $t->timestamps();
            });
        }
        return $table;
    }

    private function ensureDefaultCategories(): void
    {
        $catTable  = $this->getCategoryTable();
        $prodTable = $this->getProductTable();

        if (!Schema::hasTable($catTable)) {
            return;
        }

        $colName  = Schema::hasColumn($catTable, 'nama_kategori') ? 'nama_kategori' : 'nama';
        $defaults = ['Bahan Mentah', 'Bahan Siap Pakai'];
        $validIds = [];

        foreach ($defaults as $namaKat) {
            $rows = DB::table($catTable)
                ->whereRaw("LOWER(TRIM({$colName})) = ?", [mb_strtolower($namaKat)])
                ->orderBy('id', 'asc')
                ->get();

            if ($rows->isEmpty()) {
                $insertData = [
                    $colName     => $namaKat,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (Schema::hasColumn($catTable, 'kode_kategori')) {
                    $insertData['kode_kategori'] = 'KAT-' . rand(100, 999);
                }
                $keepId = DB::table($catTable)->insertGetId($insertData);
            } else {
                $keepId = $rows->first()->id;
                DB::table($catTable)->where('id', $keepId)->update([
                    $colName     => $namaKat,
                    'updated_at' => now(),
                ]);
            }

            $validIds[$namaKat] = $keepId;
        }

        $idBahanMentah = $validIds['Bahan Mentah'];

        if (Schema::hasTable($prodTable)) {
            if (Schema::hasColumn($prodTable, 'kategori_id')) {
                DB::table($prodTable)
                    ->whereNotIn('kategori_id', array_values($validIds))
                    ->update(['kategori_id' => $idBahanMentah]);
            }
            if (Schema::hasColumn($prodTable, 'nama_kategori')) {
                DB::table($prodTable)
                    ->whereRaw("LOWER(TRIM(nama_kategori)) = ?", ['barang mentah'])
                    ->update(['nama_kategori' => 'Bahan Mentah']);
            }
        }

        DB::table($catTable)
            ->whereNotIn('id', array_values($validIds))
            ->delete();
    }

    // 1. DASHBOARD ADMIN
    public function dashboard()
    {
        $prodTable = $this->getProductTable();
        $saleTable = $this->getSaleTable();

        $totalProduk = Schema::hasTable($prodTable) ? DB::table($prodTable)->count() : 0;
        $stokMenipis = Schema::hasTable($prodTable)
            ? DB::table($prodTable)->where('stok', '<=', 10)->orderBy('stok', 'asc')->get()
            : collect();

        $totalUser = DB::table('users')->count();
        $transaksiHariIni = Schema::hasTable($saleTable)
            ? DB::table($saleTable)->whereDate('created_at', today())->count()
            : 0;

        return view('admin.dashboard', compact(
            'totalProduk',
            'stokMenipis',
            'totalUser',
            'transaksiHariIni'
        ));
    }

    // 2. PRODUK & KATEGORI
    public function produkIndex(Request $request)
    {
        $this->ensureDefaultCategories();

        $prodTable   = $this->getProductTable();
        $catTable    = $this->getCategoryTable();
        $gudangTable = $this->ensureGudangTable();

        $kategoris = Schema::hasTable($catTable)
            ? DB::table($catTable)->orderBy('id', 'desc')->get()
            : collect();

        $query   = DB::table($prodTable);
        $keyword = $request->input('search') ?? $request->input('q');

        if (!empty($keyword)) {
            $colNama = Schema::hasColumn($prodTable, 'nama_produk') ? 'nama_produk' : 'nama';
            $query->where(function ($q) use ($prodTable, $colNama, $keyword) {
                $q->where($colNama, 'like', '%' . $keyword . '%');
                if (Schema::hasColumn($prodTable, 'nama_kategori')) {
                    $q->orWhere('nama_kategori', 'like', '%' . $keyword . '%');
                }
                if (Schema::hasColumn($prodTable, 'kode_produk')) {
                    $q->orWhere('kode_produk', 'like', '%' . $keyword . '%');
                }
            });
        }

        if ($request->filled('kategori_id') && Schema::hasColumn($prodTable, 'kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        $produks = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        $jumlahBelumProduk = DB::table($gudangTable)
            ->where('sudah_di_produk', false)
            ->where('jumlah', '>', 0)
            ->count();

        return view('admin.produk.index', compact('produks', 'kategoris', 'jumlahBelumProduk'));
    }

    public function produkCreate(Request $request)
    {
        $this->ensureDefaultCategories();

        $catTable    = $this->getCategoryTable();
        $gudangTable = $this->ensureGudangTable();

        $kategoris = Schema::hasTable($catTable)
            ? DB::table($catTable)->orderBy('id', 'desc')->get()
            : collect();

        $barangDibeli = DB::table($gudangTable)
            ->where('sudah_di_produk', false)
            ->where('jumlah', '>', 0)
            ->orderBy('id', 'desc')
            ->get();

        $selectedGudangId = $request->query('gudang_id');

        return view('admin.produk.create', compact('kategoris', 'barangDibeli', 'selectedGudangId'));
    }

    public function produkStore(Request $request)
    {
        $request->validate([
            'nama'       => 'required|string|max:255',
            'harga_jual' => 'required|numeric|min:0',
            'stok'       => 'required|numeric|min:0',
        ]);

        $table       = $this->getProductTable();
        $catTable    = $this->getCategoryTable();
        $gudangTable = $this->ensureGudangTable();
        $data        = ['created_at' => now(), 'updated_at' => now()];

        if (Schema::hasColumn($table, 'kode_produk')) {
            $data['kode_produk'] = $request->kode_produk ?: 'PRD-' . rand(10000, 99999);
        }
        if (Schema::hasColumn($table, 'nama_produk')) {
            $data['nama_produk'] = $request->nama;
        } elseif (Schema::hasColumn($table, 'nama')) {
            $data['nama'] = $request->nama;
        }

        $namaKategori = $request->nama_kategori;
        if ($request->filled('kategori_id') && Schema::hasTable($catTable)) {
            $kat = DB::table($catTable)->where('id', $request->kategori_id)->first();
            if ($kat) {
                $namaKategori = $kat->nama_kategori ?? $kat->nama ?? $namaKategori;
            }
        }
        if (Schema::hasColumn($table, 'kategori_id')) {
            $data['kategori_id'] = $request->kategori_id ?: null;
        }
        if (Schema::hasColumn($table, 'nama_kategori')) {
            $data['nama_kategori'] = $namaKategori ?: 'Bahan Siap Pakai';
        }

        $hargaBeli = (float) ($request->harga_beli ?? 0);
        if (Schema::hasColumn($table, 'harga_beli_terakhir')) {
            $data['harga_beli_terakhir'] = $hargaBeli;
        }
        if (Schema::hasColumn($table, 'hpp_rata_rata')) {
            $data['hpp_rata_rata'] = $hargaBeli;
        }
        if (Schema::hasColumn($table, 'harga_beli')) {
            $data['harga_beli'] = $hargaBeli;
        }

        if (Schema::hasColumn($table, 'harga_jual')) {
            $data['harga_jual'] = $request->harga_jual;
        } elseif (Schema::hasColumn($table, 'harga')) {
            $data['harga'] = $request->harga_jual;
        }
        if (Schema::hasColumn($table, 'stok')) {
            $data['stok'] = $request->stok;
        }
        if (Schema::hasColumn($table, 'satuan')) {
            $data['satuan'] = $request->satuan ?: 'Pcs';
        }
        if (Schema::hasColumn($table, 'satuan_dasar')) {
            $data['satuan_dasar'] = $request->satuan ?: 'Pcs';
        }

        if (Schema::hasColumn($table, 'kategori_id') && empty($data['kategori_id']) && $request->filled('nama_kategori')) {
            if (Schema::hasTable($catTable)) {
                $katCol = Schema::hasColumn($catTable, 'nama_kategori') ? 'nama_kategori' : 'nama';
                $kat = DB::table($catTable)->where($katCol, $request->nama_kategori)->first();
                $data['kategori_id'] = $kat ? $kat->id : DB::table($catTable)->insertGetId([
                    $katCol      => $request->nama_kategori,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::transaction(function () use ($request, $table, $gudangTable, $data) {
            $produkId = DB::table($table)->insertGetId($data);

            if ($request->filled('gudang_id')) {
                DB::table($gudangTable)
                    ->where('id', $request->gudang_id)
                    ->update([
                        'produk_id'       => $produkId,
                        'sudah_di_produk' => true,
                        'updated_at'      => now(),
                    ]);
            } else {
                DB::table($gudangTable)
                    ->whereRaw('LOWER(nama_barang) = ?', [mb_strtolower(trim($request->nama))])
                    ->where('sudah_di_produk', false)
                    ->update([
                        'produk_id'       => $produkId,
                        'sudah_di_produk' => true,
                        'updated_at'      => now(),
                    ]);
            }
        });

        return redirect()->route('admin.produk.index')->with('success', 'Barang hasil pembelian berhasil ditambahkan ke katalog produk kasir.');
    }

    public function produkEdit($id)
    {
        $prodTable = $this->getProductTable();
        $catTable  = $this->getCategoryTable();

        $produk = DB::table($prodTable)->where('id', $id)->first();
        abort_if(!$produk, 404);

        $kategoris = Schema::hasTable($catTable)
            ? DB::table($catTable)->orderBy('id', 'desc')->get()
            : collect();

        return view('admin.produk.edit', compact('produk', 'kategoris'));
    }

    public function produkUpdate(Request $request, $id)
    {
        $request->validate([
            'nama'       => 'required|string|max:255',
            'harga_jual' => 'required|numeric|min:0',
            'stok'       => 'required|numeric|min:0',
        ]);

        $table    = $this->getProductTable();
        $catTable = $this->getCategoryTable();
        $data     = ['updated_at' => now()];

        if (Schema::hasColumn($table, 'nama_produk')) {
            $data['nama_produk'] = $request->nama;
        } elseif (Schema::hasColumn($table, 'nama')) {
            $data['nama'] = $request->nama;
        }

        $namaKategori = $request->nama_kategori;
        if ($request->filled('kategori_id') && Schema::hasTable($catTable)) {
            $kat = DB::table($catTable)->where('id', $request->kategori_id)->first();
            if ($kat) {
                $namaKategori = $kat->nama_kategori ?? $kat->nama ?? $namaKategori;
            }
        }
        if (Schema::hasColumn($table, 'kategori_id')) {
            $data['kategori_id'] = $request->kategori_id ?: null;
        }
        if (Schema::hasColumn($table, 'nama_kategori') && $namaKategori !== null) {
            $data['nama_kategori'] = $namaKategori;
        }

        $hargaBeli = (float) ($request->harga_beli ?? 0);
        if (Schema::hasColumn($table, 'harga_beli_terakhir')) {
            $data['harga_beli_terakhir'] = $hargaBeli;
        }
        if (Schema::hasColumn($table, 'hpp_rata_rata')) {
            $data['hpp_rata_rata'] = $hargaBeli;
        }
        if (Schema::hasColumn($table, 'harga_beli')) {
            $data['harga_beli'] = $hargaBeli;
        }

        if (Schema::hasColumn($table, 'harga_jual')) {
            $data['harga_jual'] = $request->harga_jual;
        } elseif (Schema::hasColumn($table, 'harga')) {
            $data['harga'] = $request->harga_jual;
        }
        if (Schema::hasColumn($table, 'stok')) {
            $data['stok'] = $request->stok;
        }
        if (Schema::hasColumn($table, 'satuan')) {
            $data['satuan'] = $request->satuan ?: 'Pcs';
        }
        if (Schema::hasColumn($table, 'satuan_dasar')) {
            $data['satuan_dasar'] = $request->satuan ?: 'Pcs';
        }

        if (Schema::hasColumn($table, 'kategori_id') && empty($data['kategori_id']) && $request->filled('nama_kategori')) {
            if (Schema::hasTable($catTable)) {
                $katCol = Schema::hasColumn($catTable, 'nama_kategori') ? 'nama_kategori' : 'nama';
                $kat = DB::table($catTable)->where($katCol, $request->nama_kategori)->first();
                $data['kategori_id'] = $kat ? $kat->id : DB::table($catTable)->insertGetId([
                    $katCol      => $request->nama_kategori,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table($table)->where('id', $id)->update($data);

        return redirect()->route('admin.produk.index')->with('success', 'Data produk berhasil diperbarui.');
    }

    public function produkDestroy($id)
    {
        DB::table($this->getProductTable())->where('id', $id)->delete();
        return back()->with('success', 'Produk berhasil dihapus.');
    }

    public function kategoriStore(Request $request)
    {
        $request->validate(['nama' => 'required|string|max:150']);
        $table   = $this->getCategoryTable();
        $colName = Schema::hasColumn($table, 'nama_kategori') ? 'nama_kategori' : 'nama';

        $data = [
            $colName     => $request->nama,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn($table, 'kode_kategori')) {
            $data['kode_kategori'] = 'KAT-' . rand(100, 999);
        }

        DB::table($table)->insert($data);
        return back()->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function kategoriDestroy($id)
    {
        DB::table($this->getCategoryTable())->where('id', $id)->delete();
        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    // 3. STOK MASUK (PEMBELIAN DARI LUAR), BARANG KELUAR & DISTRIBUTOR
    public function stokIndex()
    {
        $prodTable   = $this->getProductTable();
        $gudangTable = $this->ensureGudangTable();

        $produks = DB::table($prodTable)->orderBy('id', 'desc')->get();

        $gudangBelumProduk = DB::table($gudangTable)
            ->where('sudah_di_produk', false)
            ->where('jumlah', '>', 0)
            ->orderBy('id', 'desc')
            ->get();

        $distTable    = Schema::hasTable('distributors') ? 'distributors' : 'distributor';
        $distributors = Schema::hasTable($distTable) ? DB::table($distTable)->get() : collect();

        $beliTable  = Schema::hasTable('pembelians') ? 'pembelians' : 'pembelian';
        $pembelians = Schema::hasTable($beliTable)
            ? DB::table($beliTable)->orderBy('id', 'desc')->limit(20)->get()
            : collect();

        $keluarTable   = Schema::hasTable('barang_keluars') ? 'barang_keluars' : 'barang_keluar';
        $barangKeluars = Schema::hasTable($keluarTable)
            ? DB::table($keluarTable)->orderBy('id', 'desc')->limit(20)->get()
            : collect();

        return view('admin.stok.index', compact(
            'produks',
            'gudangBelumProduk',
            'distributors',
            'pembelians',
            'barangKeluars'
        ));
    }

    public function distributorStore(Request $request)
    {
        $request->validate(['nama' => 'required|string|max:255']);
        $table   = Schema::hasTable('distributors') ? 'distributors' : 'distributor';
        $colNama = Schema::hasColumn($table, 'nama_distributor') ? 'nama_distributor' : 'nama';

        $data = [
            $colNama     => $request->nama,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn($table, 'kode_distributor')) {
            $data['kode_distributor'] = $request->kode_distributor ?: 'DST-' . rand(1000, 9999);
        }
        if (Schema::hasColumn($table, 'telepon')) {
            $data['telepon'] = $request->telepon;
        } elseif (Schema::hasColumn($table, 'no_telp')) {
            $data['no_telp'] = $request->telepon;
        }
        if (Schema::hasColumn($table, 'alamat')) {
            $data['alamat'] = $request->alamat;
        }

        DB::table($table)->insert($data);
        return back()->with('success', 'Distributor berhasil ditambahkan.');
    }

    public function pembelianStore(Request $request)
    {
        $items = $request->input('items', []);
        $request->merge(['items' => $items]);

        $request->validate([
            'distributor_id'       => 'nullable|integer',
            'items'                => 'required|array|min:1',
            'items.*.nama_barang'  => 'required|string|max:255',
            'items.*.satuan'       => 'nullable|string|max:50',
            'items.*.harga_beli'   => 'required|numeric|min:0',
            'items.*.jumlah'       => 'required|numeric|min:0.01',
        ], [
            'items.required'               => 'Minimal isi 1 barang yang dibeli.',
            'items.*.nama_barang.required' => 'Nama barang wajib diisi pada setiap baris.',
            'items.*.jumlah.min'           => 'Jumlah beli minimal 1.',
        ]);

        DB::transaction(function () use ($request, $items) {
            $prodTable       = $this->getProductTable();
            $gudangTable     = $this->ensureGudangTable();
            $beliTable       = Schema::hasTable('pembelians') ? 'pembelians' : 'pembelian';
            $detailBeliTable = Schema::hasTable('detail_pembelians')
                ? 'detail_pembelians'
                : (Schema::hasTable('detail_pembelian') ? 'detail_pembelian' : null);

            $colNamaPrd = Schema::hasColumn($prodTable, 'nama_produk') ? 'nama_produk' : 'nama';

            $grandTotal = 0;
            $totalQty   = 0;
            foreach ($items as $item) {
                $hargaBeli  = (float) $item['harga_beli'];
                $qty        = (float) $item['jumlah'];
                $totalQty   += $qty;
                $grandTotal += ($hargaBeli * $qty);
            }

            $pembelianId = null;
            if (Schema::hasTable($beliTable)) {
                $headerData = ['created_at' => now(), 'updated_at' => now()];

                if (Schema::hasColumn($beliTable, 'nomor_pembelian')) {
                    $headerData['nomor_pembelian'] = 'PB-' . date('YmdHis') . '-' . rand(100, 999);
                } elseif (Schema::hasColumn($beliTable, 'kode_pembelian')) {
                    $headerData['kode_pembelian'] = 'PB-' . date('YmdHis') . '-' . rand(100, 999);
                }
                if (Schema::hasColumn($beliTable, 'tanggal_pembelian')) {
                    $headerData['tanggal_pembelian'] = now();
                } elseif (Schema::hasColumn($beliTable, 'tanggal')) {
                    $headerData['tanggal'] = now();
                }
                if (Schema::hasColumn($beliTable, 'distributor_id')) {
                    $headerData['distributor_id'] = $request->distributor_id ?: null;
                }
                if (Schema::hasColumn($beliTable, 'jumlah')) {
                    $headerData['jumlah'] = $totalQty;
                }
                if (Schema::hasColumn($beliTable, 'total_harga')) {
                    $headerData['total_harga'] = $grandTotal;
                } elseif (Schema::hasColumn($beliTable, 'total_bayar')) {
                    $headerData['total_bayar'] = $grandTotal;
                } elseif (Schema::hasColumn($beliTable, 'total')) {
                    $headerData['total'] = $grandTotal;
                }
                if (Schema::hasColumn($beliTable, 'status')) {
                    $headerData['status'] = 'selesai';
                }
                if (Schema::hasColumn($beliTable, 'user_id')) {
                    $headerData['user_id'] = Auth::id();
                }

                $pembelianId = DB::table($beliTable)->insertGetId($headerData);
            }

            foreach ($items as $item) {
                $namaBarang = trim($item['nama_barang']);
                $satuan     = trim($item['satuan'] ?? '') ?: 'Pcs';
                $hargaBeli  = (float) $item['harga_beli'];
                $jumlah     = (float) $item['jumlah'];

                $existingProduk = DB::table($prodTable)
                    ->whereRaw("LOWER({$colNamaPrd}) = ?", [mb_strtolower($namaBarang)])
                    ->first();

                if ($existingProduk) {
                    DB::table($prodTable)->where('id', $existingProduk->id)->increment('stok', $jumlah);

                    $updateProd = ['updated_at' => now()];
                    if ($hargaBeli > 0) {
                        if (Schema::hasColumn($prodTable, 'harga_beli_terakhir')) {
                            $updateProd['harga_beli_terakhir'] = $hargaBeli;
                        }
                        if (Schema::hasColumn($prodTable, 'hpp_rata_rata')) {
                            $updateProd['hpp_rata_rata'] = $hargaBeli;
                        }
                        if (Schema::hasColumn($prodTable, 'harga_beli')) {
                            $updateProd['harga_beli'] = $hargaBeli;
                        }
                    }
                    DB::table($prodTable)->where('id', $existingProduk->id)->update($updateProd);

                    if ($detailBeliTable && $pembelianId) {
                        $detailData = ['created_at' => now(), 'updated_at' => now()];
                        if (Schema::hasColumn($detailBeliTable, 'pembelian_id')) {
                            $detailData['pembelian_id'] = $pembelianId;
                        }
                        if (Schema::hasColumn($detailBeliTable, 'produk_id')) {
                            $detailData['produk_id'] = $existingProduk->id;
                        }
                        if (Schema::hasColumn($detailBeliTable, 'jumlah')) {
                            $detailData['jumlah'] = $jumlah;
                        }
                        if (Schema::hasColumn($detailBeliTable, 'harga_beli')) {
                            $detailData['harga_beli'] = $hargaBeli;
                        } elseif (Schema::hasColumn($detailBeliTable, 'harga')) {
                            $detailData['harga'] = $hargaBeli;
                        }
                        if (Schema::hasColumn($detailBeliTable, 'subtotal')) {
                            $detailData['subtotal'] = $hargaBeli * $jumlah;
                        }
                        DB::table($detailBeliTable)->insert($detailData);
                    }
                } else {
                    $existingGudang = DB::table($gudangTable)
                        ->whereRaw('LOWER(nama_barang) = ?', [mb_strtolower($namaBarang)])
                        ->where('sudah_di_produk', false)
                        ->first();

                    if ($existingGudang) {
                        DB::table($gudangTable)->where('id', $existingGudang->id)->update([
                            'distributor_id' => $request->distributor_id ?: $existingGudang->distributor_id,
                            'satuan'         => $satuan,
                            'jumlah'         => (float) $existingGudang->jumlah + $jumlah,
                            'harga_beli'     => $hargaBeli > 0 ? $hargaBeli : $existingGudang->harga_beli,
                            'updated_at'     => now(),
                        ]);
                    } else {
                        DB::table($gudangTable)->insert([
                            'distributor_id'  => $request->distributor_id ?: null,
                            'produk_id'       => null,
                            'nama_barang'     => $namaBarang,
                            'satuan'          => $satuan,
                            'jumlah'          => $jumlah,
                            'harga_beli'      => $hargaBeli,
                            'sudah_di_produk' => false,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }
                }
            }
        });

        $jumlahItem = count($items);
        return back()->with('success', "Pembelian {$jumlahItem} barang dari luar berhasil disimpan! Silakan ke menu Produk -> Tambah Produk untuk memasukkannya ke daftar jual kasir.");
    }

    public function barangKeluarStore(Request $request)
    {
        $request->validate([
            'produk_id'  => 'required|integer',
            'jumlah'     => 'required|numeric|min:0.01',
            'keterangan' => 'required|string|max:255',
        ]);

        $prodTable = $this->getProductTable();
        $produk    = DB::table($prodTable)->where('id', $request->produk_id)->first();

        if (!$produk || $produk->stok < $request->jumlah) {
            return back()->with('error', 'Stok produk tidak mencukupi untuk dikurangi.');
        }

        DB::transaction(function () use ($request, $prodTable) {
            DB::table($prodTable)->where('id', $request->produk_id)->decrement('stok', $request->jumlah);

            $keluarTable = Schema::hasTable('barang_keluars') ? 'barang_keluars' : 'barang_keluar';
            if (Schema::hasTable($keluarTable)) {
                $data = ['created_at' => now(), 'updated_at' => now()];
                if (Schema::hasColumn($keluarTable, 'produk_id')) {
                    $data['produk_id'] = $request->produk_id;
                }
                if (Schema::hasColumn($keluarTable, 'jumlah')) {
                    $data['jumlah'] = $request->jumlah;
                }
                if (Schema::hasColumn($keluarTable, 'keterangan')) {
                    $data['keterangan'] = $request->keterangan;
                }
                if (Schema::hasColumn($keluarTable, 'user_id')) {
                    $data['user_id'] = Auth::id();
                }
                if (Schema::hasColumn($keluarTable, 'nomor_barang_keluar')) {
                    $data['nomor_barang_keluar'] = 'BK-' . now()->format('YmdHis') . '-' . rand(100, 999);
                }
                if (Schema::hasColumn($keluarTable, 'tanggal')) {
                    $data['tanggal'] = $request->tanggal ?? now()->toDateString();
                }
                if (Schema::hasColumn($keluarTable, 'hpp_saat_ini')) {
    $produkTable = Schema::hasTable('produks') ? 'produks' : 'produk';
    $produk = DB::table($produkTable)->where('id', $request->produk_id)->first();
    $data['hpp_saat_ini'] = $request->hpp_saat_ini ?? ($produk->harga_beli ?? $produk->hpp ?? 0);
}
if (Schema::hasColumn($keluarTable, 'total_beban')) {
    $hpp = $data['hpp_saat_ini'] ?? 0;
    $data['total_beban'] = ($request->jumlah ?? 0) * $hpp;
}
                DB::table($keluarTable)->insert($data);
            }
        });

        return back()->with('success', 'Barang keluar dicatat dan stok berhasil dikurangi.');
    }

    // 4. MONITORING & PEMBATALAN TRANSAKSI POS
    public function transaksiIndex()
    {
        $saleTable  = $this->getSaleTable();
        $transaksis = Schema::hasTable($saleTable)
            ? DB::table($saleTable)->orderBy('id', 'desc')->limit(50)->get()
            : collect();

        return view('admin.transaksi.index', compact('transaksis'));
    }

    public function transaksiBatal($id)
    {
        $saleTable   = $this->getSaleTable();
        $detailTable = Schema::hasTable('detail_penjualans')
            ? 'detail_penjualans'
            : (Schema::hasTable('detail_penjualan') ? 'detail_penjualan' : 'detail_transaksis');
        $prodTable   = $this->getProductTable();

        $transaksi = DB::table($saleTable)->where('id', $id)->first();
        if (!$transaksi) {
            return back()->with('error', 'Transaksi tidak ditemukan.');
        }

        if (isset($transaksi->status) && $transaksi->status === 'batal') {
            return back()->with('error', 'Transaksi ini sudah dibatalkan sebelumnya.');
        }

        DB::transaction(function () use ($id, $saleTable, $detailTable, $prodTable) {
            if (Schema::hasTable($detailTable)) {
                $fkCol   = Schema::hasColumn($detailTable, 'penjualan_id') ? 'penjualan_id' : 'transaksi_id';
                $qtyCol  = Schema::hasColumn($detailTable, 'jumlah') ? 'jumlah' : 'qty';
                $details = DB::table($detailTable)->where($fkCol, $id)->get();

                foreach ($details as $item) {
                    if (isset($item->produk_id)) {
                        DB::table($prodTable)->where('id', $item->produk_id)->increment('stok', $item->$qtyCol);
                    }
                }
            }

            if (Schema::hasColumn($saleTable, 'status')) {
                DB::table($saleTable)->where('id', $id)->update([
                    'status'     => 'batal',
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Transaksi #' . $id . ' berhasil dibatalkan dan stok dikembalikan.');
    }

    // 5. KEUANGAN (PENGELUARAN & PEMASUKAN LAIN)
    public function keuanganIndex()
    {
        $outTable = Schema::hasTable('pengeluarans') ? 'pengeluarans' : 'pengeluaran';
        $inTable  = Schema::hasTable('pemasukan_lains') ? 'pemasukan_lains' : 'pemasukan_lain';

        $pengeluarans   = Schema::hasTable($outTable) ? DB::table($outTable)->orderBy('id', 'desc')->limit(25)->get() : collect();
        $pemasukanLains = Schema::hasTable($inTable) ? DB::table($inTable)->orderBy('id', 'desc')->limit(25)->get() : collect();

        return view('admin.keuangan.index', compact('pengeluarans', 'pemasukanLains'));
    }

    public function pengeluaranStore(Request $request)
    {
        $request->validate([
            'keterangan' => 'required|string|max:255',
            'nominal'    => 'required|numeric|min:1',
        ]);

        $table = Schema::hasTable('pengeluarans') ? 'pengeluarans' : 'pengeluaran';
        $data  = ['created_at' => now(), 'updated_at' => now()];

        if (Schema::hasColumn($table, 'nomor_pengeluaran')) {
            $data['nomor_pengeluaran'] = 'PG-' . date('YmdHis') . '-' . rand(100, 999);
        } elseif (Schema::hasColumn($table, 'kode_pengeluaran')) {
            $data['kode_pengeluaran'] = 'PG-' . date('YmdHis') . '-' . rand(100, 999);
        }
        if (Schema::hasColumn($table, 'tanggal_pengeluaran')) {
            $data['tanggal_pengeluaran'] = now();
        } elseif (Schema::hasColumn($table, 'tanggal')) {
            $data['tanggal'] = now();
        }
        if (Schema::hasColumn($table, 'nama_pengeluaran')) {
            $data['nama_pengeluaran'] = $request->nama ?? $request->keterangan;
        } elseif (Schema::hasColumn($table, 'nama')) {
            $data['nama'] = $request->nama ?? $request->keterangan;
        }
        if (Schema::hasColumn($table, 'keterangan')) {
            $data['keterangan'] = $request->keterangan;
        }
        if (Schema::hasColumn($table, 'deskripsi')) {
            $data['deskripsi'] = $request->keterangan;
        }
        if (Schema::hasColumn($table, 'nominal')) {
            $data['nominal'] = $request->nominal;
        } elseif (Schema::hasColumn($table, 'jumlah')) {
            $data['jumlah'] = $request->nominal;
        }
        if (Schema::hasColumn($table, 'user_id')) {
            $data['user_id'] = Auth::id();
        }

        DB::table($table)->insert($data);
        return back()->with('success', 'Pengeluaran operasional berhasil dicatat.');
    }

    public function pemasukanLainStore(Request $request)
    {
        $request->validate([
            'keterangan' => 'required|string|max:255',
            'nominal'    => 'required|numeric|min:1',
        ]);

        $table = Schema::hasTable('pemasukan_lains') ? 'pemasukan_lains' : 'pemasukan_lain';
        $data  = ['created_at' => now(), 'updated_at' => now()];

        if (Schema::hasColumn($table, 'nomor_pemasukan')) {
            $data['nomor_pemasukan'] = 'PM-' . date('YmdHis') . '-' . rand(100, 999);
        } elseif (Schema::hasColumn($table, 'kode_pemasukan')) {
            $data['kode_pemasukan'] = 'PM-' . date('YmdHis') . '-' . rand(100, 999);
        }
        if (Schema::hasColumn($table, 'tanggal_pemasukan')) {
            $data['tanggal_pemasukan'] = now();
        } elseif (Schema::hasColumn($table, 'tanggal')) {
            $data['tanggal'] = now();
        }
        if (Schema::hasColumn($table, 'nama_pemasukan')) {
            $data['nama_pemasukan'] = $request->nama ?? $request->keterangan;
        } elseif (Schema::hasColumn($table, 'nama')) {
            $data['nama'] = $request->nama ?? $request->keterangan;
        }
        if (Schema::hasColumn($table, 'keterangan')) {
            $data['keterangan'] = $request->keterangan;
        }
        if (Schema::hasColumn($table, 'deskripsi')) {
            $data['deskripsi'] = $request->keterangan;
        }
        if (Schema::hasColumn($table, 'nominal')) {
            $data['nominal'] = $request->nominal;
        } elseif (Schema::hasColumn($table, 'jumlah')) {
            $data['jumlah'] = $request->nominal;
        }
        if (Schema::hasColumn($table, 'user_id')) {
            $data['user_id'] = Auth::id();
        }

        DB::table($table)->insert($data);
        return back()->with('success', 'Pemasukan tambahan berhasil dicatat.');
    }

    // 6. MANAJEMEN USER
    public function userIndex()
    {
        $users = DB::table('users')->orderBy('id', 'desc')->get();
        return view('admin.users.index', compact('users'));
    }

    public function userStore(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|in:admin,kasir,owner',
        ]);

        $data = [
            'nama'       => $request->nama,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => $request->role,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'username')) {
            $data['username'] = $request->username ?: explode('@', $request->email)[0];
        }

        DB::table('users')->insert($data);
        return back()->with('success', 'Akun pengguna baru berhasil dibuat.');
    }

    public function userUpdate(Request $request, $id)
    {
        $request->validate([
            'nama'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role'  => 'required|in:admin,kasir,owner',
        ]);

        $data = [
            'nama'       => $request->nama,
            'email'      => $request->email,
            'role'       => $request->role,
            'updated_at' => now(),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        DB::table('users')->where('id', $id)->update($data);
        return back()->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function userDestroy($id)
    {
        if (Auth::id() == $id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }
        DB::table('users')->where('id', $id)->delete();
        return back()->with('success', 'Akun pengguna berhasil dihapus.');
    }

    // 7. LAPORAN LABA/RUGI, RIWAYAT STOK & STOK AKHIR
    public function laporanIndex(Request $request)
    {
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun', date('Y'));

        $prodTable   = $this->getProductTable();
        $saleTable   = $this->getSaleTable();
        $beliTable   = Schema::hasTable('pembelians') ? 'pembelians' : 'pembelian';
        $keluarTable = Schema::hasTable('barang_keluars') ? 'barang_keluars' : 'barang_keluar';
        $outTable    = Schema::hasTable('pengeluarans') ? 'pengeluarans' : 'pengeluaran';
        $distTable   = Schema::hasTable('distributors') ? 'distributors' : 'distributor';

        $produks = Schema::hasTable($prodTable)
            ? DB::table($prodTable)->orderBy('id', 'desc')->get()
            : collect();
        $produkMap = $produks->keyBy('id');

        $distributorMap = Schema::hasTable($distTable)
            ? DB::table($distTable)->get()->keyBy('id')
            : collect();

        $applyFilter = function ($query, $col = 'created_at') use ($bulan, $tahun) {
            if (!empty($tahun) && $tahun !== 'semua') {
                $query->whereYear($col, $tahun);
            }
            if (!empty($bulan)) {
                $query->whereMonth($col, $bulan);
            }
            return $query;
        };

        $penjualans = collect();
        if (Schema::hasTable($saleTable)) {
            $qJual = DB::table($saleTable)->orderBy('id', 'desc');
            $applyFilter($qJual, 'created_at');
            $penjualans = $qJual->get();
        }
        $totalPenjualan = $penjualans->sum(function ($j) {
            if (isset($j->status) && $j->status === 'batal') {
                return 0;
            }
            return (float) ($j->total_bayar ?? $j->total_harga ?? $j->grand_total ?? $j->total ?? 0);
        });

        $pembelians = collect();
        if (Schema::hasTable($beliTable)) {
            $qBeli = DB::table($beliTable)->orderBy('id', 'desc');
            $applyFilter($qBeli, 'created_at');
            $pembelians = $qBeli->get();
        }
        $totalPembelian = $pembelians->sum(function ($b) {
            return (float) ($b->total_bayar ?? $b->total_harga ?? $b->total ?? 0);
        });

        $labaRugi   = $totalPenjualan - $totalPembelian;
        $statusLaba = $labaRugi >= 0 ? 'LABA' : 'RUGI';

        $pengeluarans = collect();
        if (Schema::hasTable($outTable)) {
            $qOut = DB::table($outTable)->orderBy('id', 'desc');
            $applyFilter($qOut, 'created_at');
            $pengeluarans = $qOut->get();
        }
        $totalPengeluaran = $pengeluarans->sum(function ($p) {
            return (float) ($p->nominal ?? $p->jumlah ?? 0);
        });

        $riwayatStok = collect();

        $detailBeliTable = Schema::hasTable('detail_pembelians')
            ? 'detail_pembelians'
            : (Schema::hasTable('detail_pembelian') ? 'detail_pembelian' : null);

        if ($detailBeliTable && Schema::hasTable($beliTable)) {
            $qMasuk = DB::table($detailBeliTable)
                ->join($beliTable, $detailBeliTable . '.pembelian_id', '=', $beliTable . '.id')
                ->select(
                    $detailBeliTable . '.produk_id',
                    $detailBeliTable . '.jumlah',
                    $beliTable . '.created_at',
                    $beliTable . '.nomor_pembelian'
                );
            $applyFilter($qMasuk, $beliTable . '.created_at');

            foreach ($qMasuk->get() as $row) {
                $prd = $produkMap[$row->produk_id] ?? null;
                $riwayatStok->push((object) [
                    'tanggal'    => $row->created_at,
                    'produk'     => $prd ? ($prd->nama_produk ?? $prd->nama ?? '-') : 'Produk #' . $row->produk_id,
                    'satuan'     => $prd ? ($prd->satuan_dasar ?? $prd->satuan ?? 'Pcs') : '',
                    'jenis'      => 'Pembelian (Stok +)',
                    'tipe'       => 'masuk',
                    'qty'        => (float) ($row->jumlah ?? 0),
                    'keterangan' => 'Pembelian ' . ($row->nomor_pembelian ?? ''),
                ]);
            }
        }

        $detailJualTable = Schema::hasTable('detail_penjualans')
            ? 'detail_penjualans'
            : (Schema::hasTable('detail_transaksis') ? 'detail_transaksis' : (Schema::hasTable('detail_penjualan') ? 'detail_penjualan' : null));

        if ($detailJualTable && Schema::hasTable($saleTable)) {
            $fkCol  = Schema::hasColumn($detailJualTable, 'penjualan_id') ? 'penjualan_id' : 'transaksi_id';
            $qtyCol = Schema::hasColumn($detailJualTable, 'jumlah') ? 'jumlah' : 'qty';

            $qTerjual = DB::table($detailJualTable)
                ->join($saleTable, $detailJualTable . '.' . $fkCol, '=', $saleTable . '.id')
                ->select(
                    $detailJualTable . '.produk_id',
                    $detailJualTable . '.' . $qtyCol . ' as qty_jual',
                    $saleTable . '.created_at',
                    $saleTable . '.id as trx_id'
                );
            if (Schema::hasColumn($saleTable, 'status')) {
                $qTerjual->where($saleTable . '.status', '!=', 'batal');
            }
            $applyFilter($qTerjual, $saleTable . '.created_at');

            foreach ($qTerjual->get() as $row) {
                $prd = $produkMap[$row->produk_id] ?? null;
                $riwayatStok->push((object) [
                    'tanggal'    => $row->created_at,
                    'produk'     => $prd ? ($prd->nama_produk ?? $prd->nama ?? '-') : 'Produk #' . $row->produk_id,
                    'satuan'     => $prd ? ($prd->satuan_dasar ?? $prd->satuan ?? 'Pcs') : '',
                    'jenis'      => 'Penjualan POS (Stok -)',
                    'tipe'       => 'keluar',
                    'qty'        => (float) ($row->qty_jual ?? 0),
                    'keterangan' => 'Transaksi POS #' . $row->trx_id,
                ]);
            }
        }

        if (Schema::hasTable($keluarTable)) {
            $qKeluar = DB::table($keluarTable)->orderBy('id', 'desc');
            $applyFilter($qKeluar, 'created_at');

            foreach ($qKeluar->get() as $row) {
                $prd = isset($row->produk_id) ? ($produkMap[$row->produk_id] ?? null) : null;
                $riwayatStok->push((object) [
                    'tanggal'    => $row->created_at,
                    'produk'     => $prd ? ($prd->nama_produk ?? $prd->nama ?? '-') : 'Produk #' . ($row->produk_id ?? '-'),
                    'satuan'     => $prd ? ($prd->satuan_dasar ?? $prd->satuan ?? 'Pcs') : '',
                    'jenis'      => 'Barang Keluar / Rusak (Stok -)',
                    'tipe'       => 'keluar',
                    'qty'        => (float) ($row->jumlah ?? 0),
                    'keterangan' => $row->keterangan ?? 'Barang keluar',
                ]);
            }
        }

        $riwayatStok = $riwayatStok->sortByDesc('tanggal')->values();

        return view('admin.laporan.index', compact(
            'bulan',
            'tahun',
            'produks',
            'distributorMap',
            'penjualans',
            'totalPenjualan',
            'pembelians',
            'totalPembelian',
            'labaRugi',
            'statusLaba',
            'pengeluarans',
            'totalPengeluaran',
            'riwayatStok'
        ));
    }
}