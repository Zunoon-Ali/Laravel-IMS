<?php

namespace App\Listeners;

use App\Events\PersonalPaymentRecorded;
use Illuminate\Support\Facades\Log;

class LogPersonalPaymentSuccess
{
    /**
     * Handle the event.
     */
    public function handle(PersonalPaymentRecorded $event): void
    {
        Log::info(sprintf(
            'ERP: Payment recorded successfully. Customer: "%s", Invoice No: "%s", Amount: PKR %s',
            $event->paymentReceived->customer_name,
            $event->paymentReceived->invoice_no,
            number_format($event->paymentReceived->total_amount, 2)
        ));
    }
}
