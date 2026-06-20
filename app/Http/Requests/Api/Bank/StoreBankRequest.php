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
            'accountNumber' => 'nullable|string|max:30|regex:/^[a-zA-Z0-9-]+$/',
            'balance' => 'required|numeric|min:0',
            'branch' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'accountNumber.regex' => 'The account number must be alphanumeric and contain no special characters except dashes.',
            'balance.min' => 'Opening balance must be positive or zero.',
        ];
    }
}
