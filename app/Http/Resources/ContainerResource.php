<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContainerResource extends JsonResource
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
            'container_no' => $this->no,
            'no' => $this->no,
            'type' => $this->type,
            'material_type' => $this->type,
            'bales' => $this->bales,
            'total_bales' => $this->bales,
            'weightLbs' => $this->weightLbs,
            'weight_lbs' => $this->weightLbs,
            'weightKg' => $this->weightKg,
            'weight_kg' => $this->weightKg,
            'actual_weight' => $this->actual_weight,
            'per_bundle_lbs' => $this->per_bundle_lbs,
            'price' => $this->price,
            'company' => $this->company,
            'company_name' => $this->company,
            'date' => $this->date,
            'description' => $this->description,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
