<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules($request): array
    {
        return [
            'is_client'    => 'required',
            'email'        => 'required|email|exists:users,email',
            'password'     => 'required',
            'device_token' => Rule::requiredIf(function() use ($request) {
                return $request->input('is_client') == 1;
            })
        ];
    }
}
