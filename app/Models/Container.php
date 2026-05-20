<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    use HasFactory;

    protected $fillable = [
        'no',
        'type',
        'bales',
        'weightLbs',
        'per_bundle_lbs',
        'weightKg',
        'actual_weight',
        'price',
        'company',
        'date',
        'description',
    ];

    public function openedBales()
    {
        return $this->hasMany(OpenedBale::class);
    }
}
