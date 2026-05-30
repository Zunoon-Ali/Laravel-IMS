<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalPaymentReceivedResource extends JsonResource
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
            'date_received' => $this->date_received->format('Y-m-d'),
            'cash_amount' => (float) $this->cash_amount,
            'total_amount' => (float) $this->total_amount,
            'paid_amount' => (float) $this->paid_amount,
            'due_amount' => (float) $this->due_amount,
            'description' => $this->description,
            'notes' => $this->notes,
            'cheques' => $this->relationLoaded('cheques') ? $this->cheques->map(function ($cheque) {
                return [
                    'id' => $cheque->id,
                    'bank_name' => $cheque->bank_name,
                    'check_no' => $cheque->check_no,
                    'due_date' => $cheque->due_date->format('Y-m-d'),
                    'to_name' => $cheque->to_name,
                    'amount' => (float) $cheque->amount,
                ];
            }) : [],
            'onlines' => $this->relationLoaded('onlines') ? $this->onlines->map(function ($online) {
                return [
                    'id' => $online->id,
                    'bank_name' => $online->bank_name,
                    'name' => $online->name,
                    'date' => $online->payment_date->format('Y-m-d'),
                    'from' => $online->from_name,
                    'to' => $online->to_name,
                    'amount' => (float) $online->amount,
                ];
            }) : [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
