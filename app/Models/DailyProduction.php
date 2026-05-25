<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyProduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'small_bale_id',
        'name',
        'bales',
        'weight',
        'supplier',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function smallBale(): BelongsTo
    {
        return $this->belongsTo(SmallBale::class);
    }
}
