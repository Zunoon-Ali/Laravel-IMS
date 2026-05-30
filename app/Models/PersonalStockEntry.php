<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalStockEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_name',
        'container_no',
        'serial_no',
        'date_added',
        'notes',
    ];

    protected $casts = [
        'date_added' => 'date:Y-m-d',
    ];

    /**
     * Get the items purchased under this stock entry.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PersonalStockEntryItem::class, 'personal_stock_entry_id');
    }
}
