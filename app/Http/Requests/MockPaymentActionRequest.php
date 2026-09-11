<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MockPaymentActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pastikan order milik user yang sedang login
        $order = \App\Models\Order::find($this->route('orderId'));

        return $order && $order->user_id === auth()->id();
    }

    public function rules(): array
    {
        // Tidak ada input body yang perlu divalidasi,
        // aksi ditentukan dari endpoint (approve/reject)
        return [];
    }

    public function messages(): array
    {
        return [];
    }

    protected function failedAuthorization(): never
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Anda tidak memiliki akses ke order ini.'
        );
    }
}