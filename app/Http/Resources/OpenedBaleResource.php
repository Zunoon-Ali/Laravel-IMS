<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpenedBaleResource extends JsonResource
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
            'container_id' => $this->container_id,
            'container_no' => $this->containerNo,
            'containerNo' => $this->containerNo,
            'date' => $this->date,
            'opened' => $this->opened,
            'bales_opened' => $this->opened,
            'remaining' => $this->remaining,
            'bales_remaining' => $this->remaining,
            'stockLbs' => $this->stockLbs,
            'stock_open_lbs' => $this->stockLbs,
            'remainingLbs' => $this->remainingLbs,
            'remaining_stock_lbs' => $this->remainingLbs,
            'openValue' => $this->openValue,
            'open_stock_value' => $this->openValue,
            'remainingValue' => $this->remainingValue,
            'remaining_stock_value' => $this->remainingValue,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
