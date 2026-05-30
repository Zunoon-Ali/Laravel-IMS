<?php

namespace App\DTOs;

class PersonalReturnInvoiceDTO
{
    public function __construct(
        public readonly string $invoiceNo,
        public readonly string $customerName,
        public readonly string $toName,
        public readonly string $dateReturned,
        public readonly ?string $description,
        public readonly float $totalAmount,
        public readonly float $paidAmount,
        public readonly float $dueAmount,
        public readonly ?string $notes,
        public readonly array $items = []
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            invoiceNo: $data['invoice_no'],
            customerName: $data['customer_name'],
            toName: $data['to_name'],
            dateReturned: $data['date_returned'],
            description: $data['description'] ?? null,
            totalAmount: (float) $data['total_amount'],
            paidAmount: (float) $data['paid_amount'],
            dueAmount: (float) $data['due_amount'],
            notes: $data['notes'] ?? null,
            items: $data['items'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'invoice_no' => $this->invoiceNo,
            'customer_name' => $this->customerName,
            'to_name' => $this->toName,
            'date_returned' => $this->dateReturned,
            'description' => $this->description,
            'total_amount' => $this->totalAmount,
            'paid_amount' => $this->paidAmount,
            'due_amount' => $this->dueAmount,
            'notes' => $this->notes,
        ];
    }
}
