<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmallBaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stock' => $this->stock,
            'total_stock' => $this->stock,
            'production' => $this->production,
            'total_production' => $this->production,
            'sale' => $this->sale,
            'total_sale' => $this->sale,
            'amount' => $this->amount,
            'total_amount' => $this->amount,
            'weight' => $this->weight,
            'weight_kg' => $this->weight,
            'weight_lbs' => $this->weight_lbs ?: round($this->weight * 2.20462, 2),
            'rate' => $this->rate,
            'date' => $this->date,
            'supplier' => $this->supplier,
            'category' => $this->category,
            'warehouseLocation' => $this->warehouseLocation,
            'sku' => $this->sku,
            'status' => $this->status,
            'quantity' => $this->quantity,
            'notes' => $this->notes,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
