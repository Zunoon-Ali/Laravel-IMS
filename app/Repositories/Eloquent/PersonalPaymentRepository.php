<?php

namespace App\Repositories\Eloquent;

use App\Models\PersonalPaymentReceived;
use App\Repositories\Contracts\PersonalPaymentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PersonalPaymentRepository implements PersonalPaymentRepositoryInterface
{
    public function getAllWithRelations(): Collection
    {
        // Eager load cheques and online receipts to optimize database query performance
        return PersonalPaymentReceived::with(['cheques', 'onlines'])->orderBy('date_received', 'desc')->orderBy('id', 'desc')->get();
    }

    public function create(array $data): PersonalPaymentReceived
    {
        return PersonalPaymentReceived::create($data);
    }

    public function getLatest(): ?PersonalPaymentReceived
    {
        return PersonalPaymentReceived::latest('id')->first();
    }
}
