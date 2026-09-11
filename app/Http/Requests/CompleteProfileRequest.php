<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CompleteProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:4|max:8|regex:/^[a-z0-9_.]+$/|without_spaces|unique:users,username',
            'phone' => 'required|string',
            'address' => 'required|string',
        ];
    }
}