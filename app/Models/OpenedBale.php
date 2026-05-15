<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenedBale extends Model
{
    use HasFactory;

    protected $fillable = [
        'container_id',
        'containerNo',
        'date',
        'opened',
        'remaining',
        'stockLbs',
        'remainingLbs',
        'openValue',
        'remainingValue',
    ];

    public function container()
    {
        return $this->belongsTo(Container::class);
    }
}
