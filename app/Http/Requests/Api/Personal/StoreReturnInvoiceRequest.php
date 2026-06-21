<?php

namespace App\Http\Requests\Api\Personal;

use Illuminate\Foundation\Http\FormRequest;

class StoreReturnInvoiceRequest extends FormRequest
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
            'invoice_no'           => 'required|string|max:255',
            'customer_name'        => 'required|string|max:255',
            'to_name'              => 'required|string|max:255',
            'date_returned'        => 'required|date',
            'description'          => 'nullable|string',
            'total_amount'         => 'required|numeric|min:0',
            'paid_amount'          => 'required|numeric|min:0',
            'due_amount'           => 'required|numeric|min:0',
            'notes'                => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.item_name'    => 'required|string|max:255',
            'items.*.small_bales'  => 'required|boolean',
            'items.*.big_bales'    => 'required|boolean',
            'items.*.no_of_bales'  => 'required|numeric|min:0',
            'items.*.amount'       => 'required|numeric|min:0',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'items.required'            => 'At least one return item is required.',
            'items.min'                 => 'Please add at least one return item.',
            'items.*.item_name.required'=> 'Each return item must have a name.',
            'items.*.no_of_bales.required' => 'Number of bales is required for each item.',
            'items.*.amount.required'   => 'Amount is required for each item.',
        ];
    }
}
