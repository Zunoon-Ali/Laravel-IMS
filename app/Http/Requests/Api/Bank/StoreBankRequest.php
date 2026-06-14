<?php

namespace App\Http\Requests\Api\Bank;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankRequest extends FormRequest
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
            'bankName' => 'required|string|max:255',
            'logo' => 'nullable|string|max:1000',
            'accountNumber' => 'nullable|string|max:255',
            'balance' => 'required|numeric',
            'branch' => 'nullable|string|max:255',
        ];
    }
}
