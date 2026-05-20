<?php

namespace App\Http\Requests\Api\Container;

use Illuminate\Foundation\Http\FormRequest;

class StoreContainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $containerId = $this->route('container') ? $this->route('container')->id : 'NULL';
        
        return [
            'no' => 'required|string|unique:containers,no,' . $containerId,
            'type' => 'required|string',
            'bales' => 'required|integer|min:1',
            'weightLbs' => 'required|numeric|gt:0',
            'weightKg' => 'required|numeric|gt:0',
            'actual_weight' => 'nullable|numeric|gt:0',
            'per_bundle_lbs' => 'nullable|numeric|gt:0',
            'price' => 'required|numeric|gt:0',
            'company' => 'required|string',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ];
    }
}
