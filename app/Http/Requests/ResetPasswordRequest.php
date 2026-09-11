<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class ResetPasswordRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'email' => Str::lower($this->email),
        ]);
    }

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'token'    => 'required|string',
            'password' => [
                'required',
                'string',
                'without_spaces',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }
}