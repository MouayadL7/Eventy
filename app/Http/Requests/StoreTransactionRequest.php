<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'exists:bookings,id'],
            'user_id'    => ['required', 'exists:users,id'],
            'transaction_type_id' => ['required', 'exists:transaction_types,id'],
            'transaction_status_id' => ['required', 'exists:transaction_statuses,id'],
            'balance' => ['required', 'numeric', 'min:1'],
        ];
    }
}
