<?php

namespace App\Repositories\Contracts;

use App\Models\PersonalPaymentSent;
use Illuminate\Database\Eloquent\Collection;

interface PersonalPaymentSentRepositoryInterface
{
    public function getAllWithRelations(): Collection;
    public function create(array $data): PersonalPaymentSent;
    public function getLatest(): ?PersonalPaymentSent;
}
