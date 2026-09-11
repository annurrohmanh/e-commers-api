<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Pilihan Checkout: wajib salah satu antara catalogue_id ATAU cart_id
            'catalogue_id'        => ['nullable', 'required_without:cart_id', 'uuid', 'exists:catalogue,id'],
            'quantity'            => ['nullable', 'required_with:catalogue_id', 'integer', 'min:1', 'max:100'],
            'cart_id'             => ['nullable', 'required_without:catalogue_id', 'uuid', 'exists:carts,id'],

            // Informasi Shipping
            'destination_city_id' => ['required', 'string'],
            'courier_code'        => ['required', 'string', 'in:jne,jnt,sicepat'],
            'service_code'        => ['required', 'string'],
        ];
    }
}