<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCatalogueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|uuid|exists:categories,id',
            'title'       => 'sometimes|string',
            'slug'        => 'sometimes|string',
            'description' => 'sometimes|string',
            'image'       => 'sometimes|string',
            'price'       => 'sometimes|numeric|min:0|decimal:0,2',
            'stock'       => 'sometimes|integer|min:0',
            'status'      => 'sometimes|in:active,inactive',
        ];
    }
}