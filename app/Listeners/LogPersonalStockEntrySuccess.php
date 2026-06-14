<?php

namespace App\Listeners;

use App\Events\PersonalStockEntrySaved;
use Illuminate\Support\Facades\Log;

class LogPersonalStockEntrySuccess
{
    /**
     * Handle the event.
     */
    public function handle(PersonalStockEntrySaved $event): void
    {
        Log::info(sprintf(
            'ERP: Purchased Stock Entry saved successfully. Supplier: "%s", Container/Invoice No: "%s", Items Count: %d',
            $event->stockEntry->supplier_name,
            $event->stockEntry->container_no,
            $event->stockEntry->items()->count()
        ));
    }
}
