<?php

namespace App\DTOs;

class PersonalPaymentSentDTO
{
    public function __construct(
        public readonly string $customerName,
        public readonly string $toName,
        public readonly string $dateSent,
        public readonly float $cashAmount,
        public readonly float $totalAmount,
        public readonly float $paidAmount,
        public readonly float $dueAmount,
        public readonly ?string $description,
        public readonly ?string $notes,
        public readonly array $cheques = [],
        public readonly array $onlines = []
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            customerName: $data['customer_name'],
            toName: $data['to_name'],
            dateSent: $data['date_sent'],
            cashAmount: (float) ($data['cash_amount'] ?? 0),
            totalAmount: (float) $data['total_amount'],
            paidAmount: (float) $data['paid_amount'],
            dueAmount: (float) $data['due_amount'],
            description: $data['description'] ?? null,
            notes: $data['notes'] ?? null,
            cheques: $data['cheques'] ?? [],
            onlines: $data['onlines'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'customer_name' => $this->customerName,
            'to_name' => $this->toName,
            'date_sent' => $this->dateSent,
            'cash_amount' => $this->cashAmount,
            'total_amount' => $this->totalAmount,
            'paid_amount' => $this->paidAmount,
            'due_amount' => $this->dueAmount,
            'description' => $this->description,
            'notes' => $this->notes,
        ];
    }
}
