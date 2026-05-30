<?php

namespace App\Http\Requests\Api\Personal;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentReceivedRequest extends FormRequest
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
            'customer_name' => 'required|string|max:255',
            'to_name' => 'required|string|max:255',
            'date_received' => 'required|date',
            'cash_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'due_amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'cheques' => 'nullable|array',
            'cheques.*.bank_name' => 'required|string|max:255',
            'cheques.*.check_no' => 'required|string|max:255',
            'cheques.*.due_date' => 'required|date',
            'cheques.*.to_name' => 'required|string|max:255',
            'cheques.*.amount' => 'required|numeric|min:0',
            'onlines' => 'nullable|array',
            'onlines.*.bank_name' => 'required|string|max:255',
            'onlines.*.name' => 'required|string|max:255',
            'onlines.*.date' => 'required|date',
            'onlines.*.from' => 'required|string|max:255',
            'onlines.*.to' => 'required|string|max:255',
            'onlines.*.amount' => 'required|numeric|min:0',
        ];
    }
}
