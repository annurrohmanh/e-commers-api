<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class LoginUserRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'email' => Str::lower($this->email),
        ]);
    }

    public function authorize(): bool
    { 
        return true; 
    }

    public function rules(): array
    {
        return [
            'email'    => 'required|email',
            'password' => 'required|string'
        ];
    }
}