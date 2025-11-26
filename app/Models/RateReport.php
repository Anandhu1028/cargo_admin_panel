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
         'total_charges',
        'report_data',
    ];

    protected $casts = [
        'report_data' => 'array',
        'total_amount' => 'float',
    ];

    /**
     * Scope to get latest report per customer
     */
    public function scopeDistinctByCustomer($query)
    {
        return $query->selectRaw('*, MAX(created_at) as latest_created')
            ->groupBy('customer_name')
            ->orderByDesc('latest_created');
    }
}
