<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calculation;
use App\Models\CalculationDetail;
use App\Models\DestinationCalculationDetail;
use App\Models\Customer;
use App\Models\RateReport;
use App\Models\RoeSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RateCalculatorController extends Controller
{
    
    public function index()
    {
        $customers = Customer::orderBy('name')->get();
        $history = Calculation::latest()->take(10)->get();

        return view('admin.rate.rate_step1', compact('customers', 'history'));
    }

    public function storeCustomer(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
    ]);

    $customer = Customer::create($validated);

    return redirect()
        ->route('rate.index')
        ->with('success', 'Customer added successfully!')
        ->with('new_customer_id', $customer->id);
}


    public function calculate(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'from_location' => 'required|string|max:255',
            'to_location' => 'required|string|max:255',
            'port' => 'nullable|string',
            'cbm' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'actual_weight' => 'nullable|numeric',
            'condition' => 'nullable|in:new,used',
            'additional_packing' => 'sometimes|in:0,1',
        ]);

        $data['additional_packing'] = (bool) ($data['additional_packing'] ?? false);

        try {
            $config = config('rate_rules');

            $volumetric_weight = $data['cbm']
                ? $data['cbm'] * 1000
                : (($data['length'] ?? 0) * ($data['width'] ?? 0) * ($data['height'] ?? 0)) / $config['volumetric_divisor'];

            $chargeable = max($data['actual_weight'] ?? 0, $volumetric_weight);

            $distance = 100; // placeholder, since we removed pincode logic
            $perKg = $this->ratePerKgByDistance($distance, $config['distance_slabs']);

            $base = $chargeable * $perKg;
            $extras = $data['additional_packing'] ? $config['extra_packing_flat'] : 0;

            $subtotal = max($base + $extras, $config['minimum_charge']);
            $tax = round($subtotal * ($config['tax_percent'] / 100), 2);
            $total = round($subtotal + $tax, 2);

            $customer = Customer::find($data['customer_id']);

            $calc = Calculation::create([
                ...$data,
                'customer_name' => $customer->name ?? null,
                'volumetric_weight' => $volumetric_weight,
                'chargeable_weight' => $chargeable,
                'distance_km' => $distance,
                'breakdown' => compact('perKg', 'base', 'extras', 'subtotal', 'tax'),
                'total_amount' => $total,
            ]);

            return back()->with('success', 'Rate calculated!')->with('last_calc', $calc?->id)->withInput();
        } catch (\Throwable $e) {
            Log::error('Rate calculation failed: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong while calculating the rate.')->withInput();
        }
    }

    private function ratePerKgByDistance($km, $slabs)
    {
        foreach ($slabs as $slab) {
            if ($km <= $slab['max_km']) {
                return $slab['per_kg'];
            }
        }
        return end($slabs)['per_kg'];
    }


public function rateStep1()
{
    $customers = \App\Models\Customer::all();

    // Check if returning user already has a calculation in progress
    $calc = null;
    if (session()->has('calc_id')) {
        $calc = \App\Models\Calculation::find(session('calc_id'));
    }

    $rateReports = RateReport::latest()->take(50)->get();
    return view('admin.rate.rate_step1', compact('customers', 'calc', 'rateReports'));
}

public function rateStep1Store(Request $request)
{
    $request->validate([
        'customer_id' => 'required',
        'from_location' => 'required',
        'to_location' => 'required',
        'cbm' => 'required|numeric',
    ]);

    $customer = \App\Models\Customer::find($request->customer_id);

    // 🔹 Update existing calculation if session exists, otherwise create a new one
    if (session()->has('calc_id') && \App\Models\Calculation::find(session('calc_id'))) {
        $calc = \App\Models\Calculation::find(session('calc_id'));
        $calc->update([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'from_location' => $request->from_location,
            'to_location' => $request->to_location,
            'port' => $request->port,
            'cbm' => $request->cbm,
            'actual_weight' => $request->actual_weight,
            'condition' => $request->condition,
            'additional_packing' => $request->has('additional_packing'),
        ]);
    } else {
        $calc = \App\Models\Calculation::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'from_location' => $request->from_location,
            'to_location' => $request->to_location,
            'port' => $request->port,
            'cbm' => $request->cbm,
            'actual_weight' => $request->actual_weight,
            'condition' => $request->condition,
            'additional_packing' => $request->has('additional_packing'),
        ]);
        session(['calc_id' => $calc->id]);
    }

    return redirect()->route('rate.step2', $calc->id);
}
public function rateStep2($calcId)
{
    $calc = Calculation::with('details')->findOrFail($calcId);
    $rules = config('rate_rules.origin');
    $cbm   = (float) ($calc->cbm ?? 0);

    // Load ROE settings
   $roeSetting = RoeSettings::where('destination', 'DESTINATION')->first();

$globalROE = (float) ($roeSetting->roe_value ?? 1);

// OCEAN FREIGHT ALWAYS DEFAULTS TO 0 (unless DB has a positive override)
$oceanROE = isset($roeSetting->ocean_freight_roe) && $roeSetting->ocean_freight_roe > 0
    ? (float) $roeSetting->ocean_freight_roe
    : 0;




    // Lookup saved rows (keyed by group|particular)
    $saved = $calc->details->keyBy(function ($d) {
        return strtolower(trim($d->group_name)) . '|' . strtolower(trim($d->particular));
    });

    $filtered = [];

    foreach ($rules as $group => $items) {
        $filtered[$group] = [];

        foreach ($items as $item) {
            $particular = strtolower(trim($item['particular'] ?? ''));
            $key = strtolower(trim($group)) . '|' . $particular;

            // base/default values from config
            $unit = $item['unit'] ?? '-';
            $rate = (float) ($item['rate'] ?? 0);
            $roe  = (float) ($item['roe'] ?? $globalROE);
            $qty  = (float) ($item['qty'] ?? 1);

            // SPECIAL: Ocean Freight should use oceanROE override
            if (str_contains($particular, 'ocean freight')) {
                $roe = $oceanROE;
            }

            // Skip irrelevant rows (van / 3 tone logic)
            if (str_contains($particular, 'van up to 3') && $cbm > 3) continue;
            if (str_contains($particular, '3 tone') && $cbm <= 3) continue;

            // If we have saved user data for this row, use saved values but
            // re-calc amount using the *current* ROE (ocean override applied above).
            if ($saved->has($key)) {
                $d = $saved[$key];

                // Use saved unit/rate/qty but apply oceanROE override if needed
                $savedUnit = $d->unit;
                $savedQty  = (float) $d->qty;
                $savedRate = (float) $d->rate;
                $savedRoe  = (float) ($d->roe ?: $globalROE);

                if (str_contains($particular, 'ocean freight')) {
                    // force ocean override for ocean freight rows
                    $savedRoe = $oceanROE;
                }

                $amount = ($savedRate * $savedQty) / ($savedRoe > 0 ? $savedRoe : 1);

                $item['unit']   = $savedUnit;
                $item['qty']    = $savedQty;
                $item['rate']   = $savedRate;
                $item['roe']    = $savedRoe;
                $item['amount'] = round($amount, 2);

                $filtered[$group][] = $item;
                continue;
            }

            // Determine default qty / rate behaviour for rows with no saved data
            $isPacking = str_contains($particular, 'packing & materials');
            $isLabour  = str_contains($particular, 'labour');
            $isOther   = str_contains($particular, 'other charges');
            $isSpecial = str_contains($particular, 'special services');
            $isStorage = str_contains($particular, 'storage');

            if ($isPacking) {
                // Packing uses CBM from step1
                $qty = $cbm;
            } elseif ($isLabour) {
                $qty = 1;
            } elseif ($isOther) {
                // other charges: leave qty 0 and rate 0 by default so user fills them
                $qty = 0;
                $rate = 0;
            } elseif ($isSpecial) {
                // special services — default to qty 1, rate left for user
                $qty = 1;
                // keep configured rate (could be 0)
            } elseif ($isStorage) {
                // storage typically uses CBM
                $qty = $cbm;
            } else {
                $qty = 1;
            }

            // Always calculate AED using the canonical formula: (rate * qty) / roe
            $amount = ($rate * $qty) / ($roe > 0 ? $roe : 1);

            $item['unit']   = $unit;
            $item['qty']    = $qty;
            $item['rate']   = $rate;
            $item['roe']    = $roe;
            $item['amount'] = round($amount, 2);

            $filtered[$group][] = $item;
        }
    }

    $rateReports = RateReport::latest()->take(50)->get();

    return view('admin.rate.rate_step2', [
        'calc' => $calc,
        'rules' => $filtered,
        'rateReports' => $rateReports,
        'globalROE' => $globalROE,
        'oceanROE' => $oceanROE,
    ]);
}



public function rateStep2Store(Request $request, $calcId)
{
    $calc = Calculation::findOrFail($calcId);
    $rules = config('rate_rules.origin');
    $cbm   = (float) ($calc->cbm ?? 0);

    // Load ROE settings
    $roeSetting = RoeSettings::where('destination', 'DESTINATION')->first();
    $globalROE  = (float) ($roeSetting->roe_value ?? 1);
    $oceanROE   = (float) ($roeSetting->ocean_freight_roe ?? $globalROE);

    $detailsData = [];

    foreach ($rules as $group => $items) {
        foreach ($items as $item) {

            $particular = $item['particular'];
            $key = strtolower(trim($particular));

            // Default config values
            $unit = $item['unit'] ?? '-';
            $rate = (float) ($item['rate'] ?? 0);
            $roe  = (float) ($item['roe'] ?? $globalROE);
            $qty  = (float) ($item['qty'] ?? 1);

            // Ocean Freight uses special ROE
            if (str_contains($key, 'ocean freight')) {
                $roe = $oceanROE;
            }

            // Skip irrelevant rows
            if (str_contains($key, 'van up to 3') && $cbm > 3) continue;
            if (str_contains($key, '3 tone') && $cbm <= 3) continue;

            // User inputs
            $userQty  = $request->input("qty.$group.$particular");
            $userRate = $request->input("rate.$group.$particular");

            if ($userQty  !== null && $userQty  !== '') $qty  = (float) $userQty;
            if ($userRate !== null && $userRate !== '') $rate = (float) $userRate;

            // Flags
            $isPacking = str_contains($key, 'packing & materials');
            $isLabour  = str_contains($key, 'labour');
            $isOther   = str_contains($key, 'other charges');
            $isSpecial = str_contains($key, 'special services');
            $isStorage = str_contains($key, 'storage');

            // ================================
            // FIXED CALCULATION LOGIC (AED)
            // amount = (rate × qty) ÷ roe
            // ================================

            if ($isPacking) {
                $unit = $request->input("packing_unit.$group.$particular", $unit);
                $qty  = $cbm;
            }

            elseif ($isLabour) {
                $qty = (float) $request->input("labour_qty.$group.$particular", 1);
            }

            elseif ($isOther) {
                $unit = $request->input("other_unit.$group.$particular", $unit);
                $rate = (float) $request->input("other_rate.$group.$particular", $rate);
                // qty stays zero unless you later allow editing
                $qty = 0;
            }

            elseif ($isSpecial) {
                $rate = (float) $request->input("special_rate.$group.$particular", $rate);
                $qty  = 1;
            }

            elseif ($isStorage) {
                $qty = $cbm;
            }

            else {
                $qty = 1;
            }

            // AED calculation (corrected)
            $amount = round(($rate * $qty) / ($roe ?: 1), 2);

            $detailsData[] = [
                'calculation_id' => $calc->id,
                'group_name'     => $group,
                'particular'     => $particular,
                'unit'           => $unit,
                'qty'            => $qty,
                'rate'           => $rate,
                'roe'            => $roe,
                'amount'         => $amount,   // AED stored
                'customer_name'  => optional($calc->customer)->name,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }
    }

    // Save to DB
    DB::transaction(function () use ($calc, $detailsData) {
        $calc->details()->delete();
        CalculationDetail::insert($detailsData);

        $calc->update([
            'total_amount'  => array_sum(array_column($detailsData, 'amount')),
            'customer_name' => optional($calc->customer)->name,
        ]);
    });

    

    return redirect()->route('rate.step3', $calc->id)
        ->with('success', 'Origin charges saved successfully.');
}



public function rateStep3($calcId)
{
    $calc = Calculation::with('destinationDetails')->findOrFail($calcId);

    // ✅ Fetch ROE from settings by destination, or use default
    $destination = strtoupper($calc->port ?? 'KOCHI');
    $roeSettings = RoeSettings::where('destination', $destination)->first();
    $defaultRoe = $roeSettings ? $roeSettings->roe_value : 0.0439;  // 1 INR = 0.0439 AED (default)

    $rules = [
        'Liner Charges' => [
            ['particular' => 'DO INCLUDING THC', 'unit' => 'CONTAINER', 'qty' => 1, 'rate' => 34684, 'roe' => $defaultRoe],
            ['particular' => 'DETENTION, IF ANY', 'unit' => 'CONTAINER', 'qty' => 1, 'rate' => 0, 'roe' => $defaultRoe],
        ],
        'Port Charges' => [
            ['particular' => 'DESTUFFING', 'unit' => 'CONTAINER', 'qty' => 1, 'rate' => 33600, 'roe' => $defaultRoe],
            ['particular' => 'CONSOLIDATION CHARGES', 'unit' => 'CONTAINER', 'qty' => 1, 'rate' => 4000, 'roe' => $defaultRoe],
            ['particular' => 'EDI FILING', 'unit' => 'CONTAINER', 'qty' => 1, 'rate' => 1500, 'roe' => $defaultRoe],
            ['particular' => 'CONTAINER SCANNING', 'unit' => 'CONTAINER', 'qty' => 1, 'rate' => 200, 'roe' => $defaultRoe],
            ['particular' => 'FORKLIFT CHARGES', 'unit' => 'HOUR', 'qty' => 1, 'rate' => 1490, 'roe' => $defaultRoe],
            ['particular' => 'STORAGE', 'unit' => 'SHIPMENT', 'qty' => 1, 'rate' => 0, 'roe' => $defaultRoe, 'extra' => '1 day 120 Rs.'],
            ['particular' => 'DUTY', 'unit' => 'SHIPMENT', 'qty' => 0, 'rate' => 0, 'roe' => $defaultRoe, 'extra' => '36% of total value'],
            ['particular' => 'OTHER CHARGES, IF ANY', 'unit' => '-', 'qty' => 0, 'rate' => 0, 'roe' => $defaultRoe],
        ],
        'CHA Charges' => [
            ['particular' => 'CLEARING CHARGES', 'unit' => 'CONTAINER', 'qty' => 1, 'rate' => 37000, 'roe' => $defaultRoe],
            ['particular' => 'DESTUFFING SUNDREIS', 'unit' => 'CONTAINER', 'qty' => 1, 'rate' => 3500, 'roe' => $defaultRoe],
            ['particular' => 'TRANSPORTATION BASIC', 'unit' => '-', 'qty' => 1, 'rate' => 4000, 'roe' => $defaultRoe],
            ['particular' => 'KOTTAYAM, HARIPPAD, TRICHUR, CHETTUVA', 'unit' => 'SHIPMENT', 'qty' => 1, 'rate' => 7000, 'roe' => $defaultRoe],
            ['particular' => 'ADDITIONAL', 'unit' => 'KM', 'qty' => 1, 'rate' => 48, 'roe' => $defaultRoe],
            ['particular' => 'OFFLOADING', 'unit' => 'CBM', 'qty' => 1, 'rate' => 750, 'roe' => $defaultRoe],
            ['particular' => 'UNBOXING, FITTING & FIXING', 'unit' => 'CBM', 'qty' => 1, 'rate' => 500, 'roe' => $defaultRoe],
            ['particular' => 'OTHER CHARGES, IF ANY', 'unit' => '-', 'qty' => 0, 'rate' => 0, 'roe' => $defaultRoe],
        ],
    ];

    $savedDetails = $calc->destinationDetails->keyBy(fn($d) =>
        strtolower($d->group_name . '|' . $d->particular)
    );

    $cbm = (float) ($calc->cbm ?? 1);

    foreach ($rules as $group => &$items) {
        foreach ($items as &$item) {
            $key = strtolower($group . '|' . $item['particular']);
            $saved = $savedDetails->get($key);
            if ($saved) {
                $item['unit'] = $saved->unit;
                $item['qty']  = $saved->qty;
                $item['rate'] = $saved->rate;   // INR
                $item['roe']  = $saved->roe;    // conversion factor (INR to AED)
                $item['amount'] = $saved->amount; // AED
            } else {
                $item['qty'] = $cbm ?: 1;
                $item['amount'] = round($item['rate'] * $item['qty'] * $item['roe'], 2); // INR * conversion = AED
            }
        }
    }

    $rateReports = RateReport::latest()->take(50)->get();
    return view('admin.rate.rate_step3', compact('calc', 'rules', 'rateReports'));
}



//  Step 3 Store
public function rateStep3Store(Request $request, $calcId)
{
    $calc = Calculation::findOrFail($calcId);
    $rules = $request->input('rules', []);
    $cbm   = (float) ($calc->cbm ?? 1);

    $detailsData = [];
    $totalInINR = 0;

    // Build destination rows (INR → AED)
    foreach ($rules as $group => $items) {
        foreach ($items as $item) {

            $particular = $item['particular'];
            $unit  = $item['unit'];
            $qty   = (float) ($item['qty'] ?? 1);
            $rate  = (float) ($item['rate'] ?? 0);      // INR
            $roe   = (float) ($item['roe'] ?? 1);       // ROE = INR per AED

            // Convert INR → AED
            // amountAED = (INR rate × qty) ÷ ROE
            $amountAED = $roe > 0 ? ($rate * $qty) / $roe : 0;

            $detailsData[] = [
                'calculation_id' => $calc->id,
                'customer_name'  => $calc->customer_name,
                'group_name'     => $group,
                'particular'     => $particular,
                'unit'           => $unit,
                'qty'            => $qty,
                'rate'           => $rate,
                'roe'            => $roe,
                'amount'         => $amountAED,   // AED
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            $totalInINR += $amountAED;
        }
    }

    // ===========================
    // SAVE TO DB (destination)
    // ===========================
    DB::transaction(function () use ($calc, $detailsData, $totalInINR) {
        // Overwrite destination rows
        $calc->destinationDetails()->delete();
        DestinationCalculationDetail::insert($detailsData);

        $calc->update([
            'final_amount' => $totalInINR,
        ]);
    });

    // ============================================================
    // NOW → RELOAD ORIGIN & DESTINATION FROM DB FOR HISTORY
    // ============================================================

    $originDB = $calc->details()->get();
    $destinationDB = $calc->destinationDetails()->get();

    // ---------- ORIGIN GROUP ----------
    $originGrouped = [];
    $originTotal = 0;

    foreach ($originDB as $d) {
        $g = $d->group_name;

        if (!isset($originGrouped[$g])) $originGrouped[$g] = [];

        $amountAED = ($d->rate * $d->qty) / ($d->roe ?: 1);
        $originTotal += $amountAED;

        $originGrouped[$g][] = [
            'particular' => $d->particular,
            'unit'       => $d->unit,
            'qty'        => $d->qty,
            'rate'       => $d->rate,
            'roe'        => $d->roe,
            'amount'     => $amountAED,
        ];
    }

    // ---------- DESTINATION GROUP ----------
    $destinationGrouped = [];
    $destinationTotal = 0;

    foreach ($destinationDB as $d) {
        $g = $d->group_name;

        if (!isset($destinationGrouped[$g])) $destinationGrouped[$g] = [];

        // amount = INR→AED
        $amountAED = ($d->rate * $d->qty) / ($d->roe ?: 1);
        $destinationTotal += $amountAED;

        $destinationGrouped[$g][] = [
            'particular' => $d->particular,
            'unit'       => $d->unit,
            'qty'        => $d->qty,
            'rate'       => $d->rate,
            'roe'        => $d->roe,
            'amount'     => $amountAED,
        ];
    }

    $grandTotalAED = $originTotal + $destinationTotal;

   // SAVE SNAPSHOT 
RateReport::create([
    'calculation_id' => $calc->id,
    'customer_name'  => $calc->customer_name,
    'total_amount'   => $grandTotalAED, // AED

    'report_data'    => [
        'customer_info' => [
            'name'          => $calc->customer_name,
            'from_location' => $calc->from_location,
            'to_location'   => $calc->to_location,
            'port'          => $calc->port,
            'cbm'           => $calc->cbm,
            'created_at'    => $calc->created_at->format('d M Y'),
        ],

        // ORIGIN (ALREADY CONVERTED TO AED)
        'origin'        => $originGrouped,
        'origin_total'  => $originTotal,

        // DESTINATION (ALREADY CONVERTED TO AED)
        'destination'       => $destinationGrouped,
        'destination_total' => $destinationTotal,

        // GRAND TOTAL (AED)
        'grand_total'       => $grandTotalAED,
    ],
]);


    return redirect()
        ->route('rate.report.full', $calc->id)
        ->with('success', 'Destination charges saved successfully!');
}



public function rateReportFull($id)
{
    $calc = Calculation::with(['details', 'destinationDetails'])->findOrFail($id);

    // Grouping for display
    $originGroups = $calc->details->groupBy('group_name');
    $destinationGroups = $calc->destinationDetails->groupBy('group_name');

    $rateReports = RateReport::latest()->take(50)->get();
    return view('admin.rate.rate_report_full', [
        'calc' => $calc,
        'originGroups' => $originGroups,
        'destinationGroups' => $destinationGroups,
        'rateReports' => $rateReports,
    ]);
}

/**
 * CLEANED HISTORY — Always shows final snapshots only.
 */
public function history()
{
    $reports = RateReport::orderBy('created_at', 'desc')->paginate(25);

    return view('admin.rate.history', compact('reports'));
}


/**
 * CLEAN DELETE
 */
public function deleteReport(Request $request, $id)
{
    $report = RateReport::find($id);

    if (!$report) {
        return $request->expectsJson()
            ? response()->json(['message' => 'Report not found'], 404)
            : back()->with('error', 'Report not found.');
    }

    $report->delete();

    return $request->expectsJson()
        ? response()->json(['message' => 'Rate report deleted successfully'])
        : back()->with('success', 'Rate report deleted successfully.');
}


}