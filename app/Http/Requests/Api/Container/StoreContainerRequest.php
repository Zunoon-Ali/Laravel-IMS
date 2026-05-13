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
            'bales' => 'required|integer',
            'weightLbs' => 'required|numeric',
            'weightKg' => 'required|numeric',
            'price' => 'required|numeric',
            'company' => 'required|string',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ];
    }
}
