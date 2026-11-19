<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PembelianStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'distributor_id' => 'required|exists:distributor,id',
            'metode_pembayaran' => 'required|in:tunai,transfer',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produk,id',
            'items.*.harga_beli' => 'required|numeric|gt:0',
            'items.*.jumlah' => 'required|numeric|gt:0',
        ];
    }
}