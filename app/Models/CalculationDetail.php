<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CalculationDetail extends Model
{
     use HasFactory;

    protected $fillable = [
        'calculation_id',
        'group_name',
         'customer_name',
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
        return $this->belongsTo(Calculation::class, 'calculation_id');
    }
}
