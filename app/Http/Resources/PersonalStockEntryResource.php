<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalStockEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_name' => $this->supplier_name,
            'container_no' => $this->container_no,
            'serial_no' => $this->serial_no,
            'date_added' => $this->date_added->format('Y-m-d'),
            'notes' => $this->notes,
            'items' => $this->relationLoaded('items') ? $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'bale_type' => $item->bale_type,
                    'no_of_bales' => $item->no_of_bales,
                    'item_name' => $item->item_name,
                    'company' => $item->company,
                    'weight' => (float) $item->weight,
                    'rate' => (float) $item->rate,
                ];
            }) : [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
