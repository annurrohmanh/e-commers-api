<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCatalogueRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }
    
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'     => 'required|uuid|exists:users,id',
            'category_id' => 'required|uuid|exists:categories,id',
            'title'       => 'required|string',
            'slug'        => 'required|string',
            'weight'      => 'required|integer',
            'description' => 'required|string',
            'image'       => 'nullable|string',
            'price'       => 'required|numeric|min:0|decimal:0,2',
            'stock'       => 'required|integer|min:0',
            'status'      => 'nullable|in:active,inactive',
        ];
    }
}