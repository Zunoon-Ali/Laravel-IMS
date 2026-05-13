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
        return [
            'no' => 'required|string|unique:containers,no,' . ($this->container ? $this->container->id : 'NULL'),
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
