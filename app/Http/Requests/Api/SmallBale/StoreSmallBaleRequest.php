<?php

namespace App\Http\Requests\Api\SmallBale;

use Illuminate\Foundation\Http\FormRequest;

class StoreSmallBaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'stock' => 'nullable|integer',
            'production' => 'nullable|integer',
            'sale' => 'nullable|integer',
            'amount' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'weight_lbs' => 'nullable|numeric',
            'rate' => 'nullable|numeric',
            'date' => 'required|date',
            'supplier' => 'nullable|string',
            'category' => 'nullable|string',
            'warehouseLocation' => 'nullable|string',
            'sku' => 'nullable|string',
            'status' => 'nullable|string',
            'quantity' => 'nullable|integer',
            'notes' => 'nullable|string',
            'image' => 'nullable|string',
        ];
    }
}
