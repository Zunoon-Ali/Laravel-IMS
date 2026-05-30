<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalReturnInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_no' => $this->invoice_no,
            'customer_name' => $this->customer_name,
            'to_name' => $this->to_name,
            'date_returned' => $this->date_returned->format('Y-m-d'),
            'description' => $this->description,
            'total_amount' => (float) $this->total_amount,
            'paid_amount' => (float) $this->paid_amount,
            'due_amount' => (float) $this->due_amount,
            'notes' => $this->notes,
            'items' => $this->relationLoaded('items') ? $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'small_bales' => (bool) $item->is_small_bales,
                    'big_bales' => (bool) $item->is_big_bales,
                    'no_of_bales' => (int) $item->no_of_bales,
                    'amount' => (float) $item->amount,
                ];
            }) : [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
