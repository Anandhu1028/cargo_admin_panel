<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoeSettings extends Model
{
    protected $table = 'roe_settings';

    protected $fillable = [
        'destination',
        'roe_value',
        'description',
    ];

    protected $casts = [
        'roe_value' => 'float',
    ];

    public static function getByDestination($destination)
    {
        return static::where('destination', $destination)->first();
    }

    public static function getDefaultRoe()
    {
        // Get the first ROE setting as default
        $first = static::first();
        return $first ? $first->roe_value : 0.0439; // Default 1 INR = 0.0439 AED
    }
}
