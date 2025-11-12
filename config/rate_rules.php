<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LCL RATE CALCULATION RULES
    |--------------------------------------------------------------------------
    | Each section contains a list of rate rules grouped by type.
    | Every rule can define:
    | - particular, unit, qty, rate, roe
    | - field: which input/field this row depends on
    | - amt_formula: formula to compute amount (evaluated dynamically)
    | - manual: true if user must enter amount manually
    | - type: optional (e.g. 'percentage')
    */

    'origin' => [

        'packing_collection' => [
            [
                'particular'   => 'Collection - Van up to 3 CBM',
                'unit'         => 'Fleet',
                'qty'          => 1,
                'rate'         => 100.00,
                'roe'          => 1.0000,
                'field'        => 'FROM',
                'amt_formula'  => 'rate * qty * roe',
                'condition'    => 'cbm <= 3',
            ],
            [
                'particular'   => 'Collection - 3 Tone up to 15 CBM',
                'unit'         => 'Fleet',
                'qty'          => 1,
                'rate'         => 200.00,
                'roe'          => 1.0000,
                'field'        => 'CBM/VOLUME/ITEM DETAILS',
                'amt_formula'  => 'rate * qty * roe',
                'condition'    => 'cbm > 3 && cbm <= 15',
            ],
            [
                'particular'   => 'Labour',
                'unit'         => 'Nos',
                'qty'          => 1,
                'rate'         => 125.00,
                'roe'          => 1.0000,
                'field'        => 'VOLUME',
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Packing & Materials',
                'unit'         => 'CBM',
                'qty'          => 1,
                'rate'         => 45.00,
                'roe'          => 1.0000,
                'field'        => 'VOLUME/CBM',
                'amt_formula'  => 'rate * qty * roe * cbm',
            ],
            [
                'particular'   => 'Other Charges (If Any)',
                'unit'         => null,

                'qty'          => 1,
                'rate'         => 50.00,
                'roe'          => 1.0000,
                'field'        => 'SPECIAL_PACKING_NEEDED',
                'amt_formula'  => 'rate * qty * roe',
                'manual'       => true,
            ],
        ],

        'liner_charges' => [
            [
                'particular'   => 'Ocean Freight',
                'unit'         => 'Container',
                'qty'          => 1,
                'rate'         => 150.00,
                'roe'          => 3.6850,
                'field'        => 'MODE_OF_TRANSPORT',
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'BL',
                'unit'         => 'Shipment',
                'qty'          => 1,
                'rate'         => 575.00,
                'roe'          => 1.0000,
                'field'        => 'DOCUMENTS',
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Seal',
                'unit'         => 'Container',
                'qty'          => 1,
                'rate'         => 30.00,
                'roe'          => 1.0000,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Surrender / Courier',
                'unit'         => 'Shipment',
                'qty'          => 1,
                'rate'         => 0.00,
                'roe'          => 1.0000,
                'amt_formula'  => 'manual',
                'manual'       => true,
            ],
            [
                'particular'   => 'Other Charges (If Any)',
                'amt_formula'  => 'rate * qty * roe',
                'manual'       => true,
            ],
        ],

        'transportation' => [
            [
                'particular'   => 'Container Transport',
                'unit'         => 'Trailer',
                'qty'          => 1,
                'rate'         => 550.00,
                'roe'          => 1.0000,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Token',
                'unit'         => 'Trailer',
                'qty'          => 1,
                'rate'         => 50.00,
                'roe'          => 1.0000,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'VGM',
                'unit'         => 'Trailer',
                'qty'          => 1,
                'rate'         => 65.00,
                'roe'          => 1.0000,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Other Charges (If Any)',
                'amt_formula'  => 'rate * qty * roe',
                'manual'       => true,
            ],
        ],

        'wh' => [
            [
                'particular'   => 'Container Loading',
                'unit'         => 'Container',
                'qty'          => 1,
                'rate'         => 500.00,
                'roe'          => 1.0000,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Special Services (If Any)',
                'qty'          => 1,
                'rate'         => 0.00,
                'roe'          => 1.0000,
                'amt_formula'  => 'manual',
                'manual'       => true,
            ],
            [
                'particular'   => 'Storage',
                'unit'         => 'CBM',
                'qty'          => 0,
                'rate'         => 60.00,
                'roe'          => 1.0000,
                'note'         => '₹120/day — enter days manually',
                'amt_formula'  => 'manual',
                'manual'       => true,
            ],
        ],

        'boe_docs' => [
            [
                'particular'   => 'TLUC',
                'unit'         => 'Container',
                'qty'          => 1,
                'rate'         => 314.00,
                'roe'          => 1.0000,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'BOE',
                'unit'         => 'Shipment',
                'qty'          => 1,
                'rate'         => 120.00,
                'roe'          => 1.0000,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'THC & DPC',
                'unit'         => 'Container Size',
                'qty'          => 1,
                'rate'         => 1155.00,
                'roe'          => 1.0000,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Service Charges',
                'unit'         => 'Shipment',
                'qty'          => 1,
                'rate'         => 78.75,
                'roe'          => 1.0000,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Other Charges (If Any)',
                'amt_formula'  => 'rate * qty * roe',
                'manual'       => true,
            ],
        ],
    ],

    'destination' => [

        'liner_charges' => [
            [
                'particular'   => 'DO Including THC',
                'unit'         => 'Container',
                'qty'          => 1,
                'rate'         => 34684.00,
                'roe'          => 0.0418,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Detention (If Any)',
                'unit'         => 'Container',
                'qty'          => 1,
                'rate'         => 0.00,
                'roe'          => 0.0418,
                'amt_formula'  => 'manual',
                'manual'       => true,
            ],
        ],

        'port_charges' => [
            [
                'particular'   => 'Destuffing',
                'unit'         => 'Container',
                'qty'          => 1,
                'rate'         => 33600.00,
                'roe'          => 0.0418,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Consolidation Charges',
                'unit'         => 'Container',
                'qty'          => 1,
                'rate'         => 4000.00,
                'roe'          => 0.0418,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'EDI Filing',
                'unit'         => 'Container',
                'qty'          => 1,
                'rate'         => 1500.00,
                'roe'          => 0.0418,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Container Scanning',
                'unit'         => 'Container',
                'qty'          => 1,
                'rate'         => 200.00,
                'roe'          => 0.0418,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Forklift Charges',
                'unit'         => 'Hour',
                'qty'          => 1,
                'rate'         => 1490.00,
                'roe'          => 0.0418,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Storage (days)',
                'unit'         => 'Shipment',
                'qty'          => 1,
                'rate'         => 120.00,
                'roe'          => 1.0000,
                'note'         => 'Enter number of days -> amt = rate * days',
                'amt_formula'  => 'manual',
                'manual'       => true,
            ],
            [
                'particular'   => 'Duty (36% of total value)',
                'unit'         => 'Shipment',
                'qty'          => 1,
                'rate'         => 36.00,
                'roe'          => 1.0000,
                'type'         => 'percentage',
                'amt_formula'  => 'percentage(36, subtotal_destination)',
            ],
            [
                'particular'   => 'Other Charges (If Any)',
                'amt_formula'  => 'rate * qty * roe',
                'manual'       => true,
            ],
        ],

        'cha_charges' => [
            [
                'particular'   => 'Clearing Charges',
                'unit'         => 'Container',
                'qty'          => 1,
                'rate'         => 37000.00,
                'roe'          => 0.0418,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Destuffing Sundries',
                'unit'         => 'Container',
                'qty'          => 1,
                'rate'         => 3500.00,
                'roe'          => 0.0418,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Transportation Basic',
                'unit'         => 'Shipment',
                'qty'          => 1,
                'rate'         => 4000.00,
                'roe'          => 0.0418,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Delivery to Kottayam/Harippad/Trichur/Chettuva',
                'unit'         => 'Shipment',
                'qty'          => 1,
                'rate'         => 7000.00,
                'roe'          => 0.0418,
                'amt_formula'  => 'rate * qty * roe',
            ],
            [
                'particular'   => 'Additional (per KM)',
                'unit'         => 'KM',
                'qty'          => 1,
                'rate'         => 48.00,
                'roe'          => 0.0418,
                'amt_formula'  => 'rate * extra_km * roe',
                'manual'       => true,
            ],
            [
                'particular'   => 'Offloading',
                'unit'         => 'CBM',
                'qty'          => 1,
                'rate'         => 750.00,
                'roe'          => 0.0418,
                'amt_formula'  => 'rate * cbm * roe',
            ],
            [
                'particular'   => 'Unboxing, Fitting & Fixing',
                'unit'         => 'CBM',
                'qty'          => 1,
                'rate'         => 500.00,
                'roe'          => 0.0418,
                'amt_formula'  => 'rate * cbm * roe',
            ],
            [
                'particular'   => 'Other Charges (If Any)',
                'amt_formula'  => 'rate * qty * roe',
                'manual'       => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ADDITIONAL GENERAL RATE RULES
    |--------------------------------------------------------------------------
    | Used for volumetric and distance-based rate calculations
    | (e.g. air, surface, or express cargo modes)
    */

    'volumetric_divisor' => 5000,
    'minimum_charge'     => 1000,
    'extra_packing_flat' => 500,
    'profit_percent'     => 10,
    'tax_percent'        => 18,

    'distance_slabs' => [
        ['max_km' => 100,   'per_kg' => 5],
        ['max_km' => 500,   'per_kg' => 6],
        ['max_km' => 1000,  'per_kg' => 8],
        ['max_km' => 2000,  'per_kg' => 10],
        ['max_km' => 99999, 'per_kg' => 12],
    ],

];
