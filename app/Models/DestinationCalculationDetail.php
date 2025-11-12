<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DestinationCalculationDetail extends Model
{
   
    protected $fillable = [
        'calculation_id',
        'customer_name',
        'group_name',
        'particular',
        'unit',
        'qty',
        'rate',
        'roe',
        'amount',
        'extra',
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    public function calculation()
    {
        return $this->belongsTo(\App\Models\Calculation::class);
    }
}
