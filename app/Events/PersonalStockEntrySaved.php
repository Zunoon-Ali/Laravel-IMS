<?php

namespace App\Events;

use App\Models\PersonalStockEntry;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonalStockEntrySaved
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly PersonalStockEntry $stockEntry
    ) {}
}
