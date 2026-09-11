<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'catalogue_id' => ['required', 'uuid', 'exists:catalogue,id'],
            'quantity'     => ['required', 'integer', 'min:1'],
        ];
    }
}