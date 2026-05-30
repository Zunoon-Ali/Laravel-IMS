<?php

namespace App\Http\Requests\Api\Personal;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockEntryRequest extends FormRequest
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
            'supplier_name' => 'required|string|max:255',
            'container_no' => 'nullable|string|max:255',
            'serial_no' => 'nullable|string|max:255',
            'date_added' => 'required|date',
            'notes' => 'nullable|string',
            'small_bale_items' => 'nullable|array',
            'small_bale_items.*.no_of_bales' => 'required|numeric|min:0',
            'small_bale_items.*.item_name' => 'required|string|max:255',
            'small_bale_items.*.company' => 'required|string|max:255',
            'small_bale_items.*.weight' => 'required|numeric|min:0',
            'small_bale_items.*.rate' => 'required|numeric|min:0',
            'big_bale_items' => 'nullable|array',
            'big_bale_items.*.no_of_bales' => 'required|numeric|min:0',
            'big_bale_items.*.item_name' => 'required|string|max:255',
            'big_bale_items.*.company' => 'required|string|max:255',
            'big_bale_items.*.weight' => 'required|numeric|min:0',
            'big_bale_items.*.rate' => 'required|numeric|min:0',
        ];
    }
}
