<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PenjualanStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pembeli_id' => 'nullable|exists:pembeli,id',
            'metode_pembayaran' => 'required|in:tunai,transfer,qris',
            'referensi_pembayaran' => 'nullable|string|max:100',
            'bayar' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produk,id',
            'items.*.jumlah' => 'required|numeric|gt:0',
            'items.*.diskon_persen' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    // Validasi batas diskon kasir maksimal 10%
                    if (auth()->user()->role === 'kasir' && $value > 10) {
                        $fail('Kasir hanya diizinkan memberikan diskon maksimal 10%.');
                    }
                },
            ],
        ];
    }
}