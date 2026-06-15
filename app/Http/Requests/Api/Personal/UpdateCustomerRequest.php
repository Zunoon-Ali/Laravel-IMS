<?php

namespace App\Http\Requests\Api\Personal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'phone'           => 'required|string|max:255',
            'email'           => 'nullable|string|email|max:255',
            'status'          => 'required|string|max:255',
            'city'            => 'nullable|string|max:255',
            'address'         => 'nullable|string',
            'opening_balance' => 'nullable|numeric',
            'notes'           => 'nullable|string',
        ];
    }
}
