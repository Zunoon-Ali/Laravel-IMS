<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmallBale extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'stock',
        'production',
        'sale',
        'amount',
        'weight',
        'rate',
        'date',
        'supplier',
        'category',
        'warehouseLocation',
        'sku',
        'status',
        'quantity',
        'notes',
    ];
}
