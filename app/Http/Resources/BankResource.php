<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bankName' => $this->bank_name,
            'logo' => $this->logo,
            'accountNumber' => $this->account_number,
            'balance' => (float) $this->balance,
            'branch' => $this->branch,
            'createdDate' => $this->created_at?->format('d-F-Y'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
