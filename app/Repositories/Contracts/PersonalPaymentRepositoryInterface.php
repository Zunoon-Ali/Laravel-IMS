<?php

namespace App\Repositories\Contracts;

use App\Models\PersonalPaymentReceived;
use Illuminate\Database\Eloquent\Collection;

interface PersonalPaymentRepositoryInterface
{
    public function getAllWithRelations(): Collection;
    public function create(array $data): PersonalPaymentReceived;
    public function getLatest(): ?PersonalPaymentReceived;
}
