<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateReport extends Model
{
    protected $table = 'rate_reports';

    protected $fillable = [
        'calculation_id',
        'customer_name',
        'total_amount',
    ];
}
