<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalStockEntryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'personal_stock_entry_id',
        'bale_type',
        'no_of_bales',
        'item_name',
        'company',
        'weight',
        'rate',
    ];

    protected $casts = [
        'no_of_bales' => 'integer',
        'weight' => 'float',
        'rate' => 'float',
    ];

    /**
     * Get the parent stock entry.
     */
    public function stockEntry(): BelongsTo
    {
        return $this->belongsTo(PersonalStockEntry::class, 'personal_stock_entry_id');
    }
}
