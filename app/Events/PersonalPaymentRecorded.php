<?php

namespace App\Events;

use App\Models\PersonalPaymentReceived;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonalPaymentRecorded
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly PersonalPaymentReceived $paymentReceived
    ) {}
}
