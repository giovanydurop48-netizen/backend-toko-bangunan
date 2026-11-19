<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProdukWebController extends Controller
{
    public function index(Request $request)
    {
        $params = $request->query();
        if ($request->filled('q') && !$request->filled('search')) {
            $params['search'] = $request->q;
        }

        return redirect()->route('admin.produk.index', $params);
    }

    public function create()
    {
        return redirect()->route('admin.produk.index');
    }

    public function store(Request $request)
    {
        return app(AdminController::class)->produkStore($request);
    }

    public function edit($id)
    {
        return redirect()->route('admin.produk.index');
    }

    public function update(Request $request, $id)
    {
        return app(AdminController::class)->produkUpdate($request, $id);
    }

    public function destroy($id)
    {
        return app(AdminController::class)->produkDestroy($id);
    }
}