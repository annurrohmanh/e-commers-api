<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|min:4|max:8|regex:/^[a-z0-9_.]+$/|without_spaces|unique:users,username',
            'email' => 'sometimes|email|unique:users,email',
            'phone' => 'sometimes|string',
            'address' => 'sometimes|string',
            'password' => [
                'sometimes',
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
