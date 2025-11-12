<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calculation extends Model
{
    protected $table = 'calculations';

    protected $fillable = [
        'customer_id',
        'customer_name',
        'from_pincode', 'from_location',
        'to_pincode', 'to_location',
        'port',
        'cbm', 'length', 'width', 'height',
        'actual_weight',
        'condition',
        'additional_packing',
        'volumetric_weight',
        'chargeable_weight',
        'distance_km',
        'breakdown',
        'total_amount',

        // ✅ new extended fields
        'type',
        'charges_breakdown',
        'per_cbm',
        'profit',
        'tax_amount',
        'final_amount',
        'weight_unit',
    ];

    protected $casts = [
        'additional_packing' => 'boolean',
        'breakdown'          => 'array',
        'charges_breakdown'  => 'array',
        'cbm'                => 'float',
        'actual_weight'      => 'float',
        'total_amount'       => 'float',
        'per_cbm'            => 'float',
        'profit'             => 'float',
        'tax_amount'         => 'float',
        'final_amount'       => 'float',
    ];

    // ✅ Relationship to Customer
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    // ✅ Relationship to details (Step 2 line items)
    public function details()
    {
        return $this->hasMany(\App\Models\CalculationDetail::class, 'calculation_id');
    }

    // ✅ Computed final total
    public function getFinalTotalAttribute()
    {
        $base = $this->total_amount ?? 0;
        $profit = $this->profit ?? 0;
        $tax = $this->tax_amount ?? 0;

        return round($base + $profit + $tax, 2);
    }

    // ✅ Helper: check if this is LCL
    public function isLcl()
    {
        return $this->type === 'lcl';
    }

    public function destinationDetails()
{
    return $this->hasMany(\App\Models\DestinationCalculationDetail::class, 'calculation_id');
}


}
