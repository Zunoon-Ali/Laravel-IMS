<?php

namespace App\DTOs;

class PersonalStockEntryDTO
{
    public function __construct(
        public readonly string $supplierName,
        public readonly ?string $containerNo,
        public readonly ?string $serialNo,
        public readonly string $dateAdded,
        public readonly ?string $notes,
        public readonly array $smallBaleItems = [],
        public readonly array $bigBaleItems = []
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            supplierName: $data['supplier_name'],
            containerNo: $data['container_no'] ?? null,
            serialNo: $data['serial_no'] ?? null,
            dateAdded: $data['date_added'],
            notes: $data['notes'] ?? null,
            smallBaleItems: $data['small_bale_items'] ?? [],
            bigBaleItems: $data['big_bale_items'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'supplier_name' => $this->supplierName,
            'container_no' => $this->containerNo,
            'serial_no' => $this->serialNo,
            'date_added' => $this->dateAdded,
            'notes' => $this->notes,
        ];
    }
}
