<?php

namespace App\Http\Requests\Api\Personal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'status' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'opening_balance' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ];
    }
}
