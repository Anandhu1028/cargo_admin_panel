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

    // ROE settings
    $roeSetting = RoeSettings::where('destination', 'DESTINATION')->first();
    $globalROE = (float) ($roeSetting->roe_value ?? 1);
    $oceanROE  = isset($roeSetting->ocean_freight_roe) && $roeSetting->ocean_freight_roe > 0
                ? (float) $roeSetting->ocean_freight_roe
                : $globalROE;

    // Saved DB rows indexed by unique key
    $saved = $calc->details->keyBy(function ($d) {
        return strtolower(trim($d->group_name)) . '|' . strtolower(trim($d->particular));
    });

    $filtered = [];

    // --------------------------
    // BUILD DEFAULT + SAVED ROWS
    // --------------------------
    foreach ($rules as $group => $items) {
        $filtered[$group] = [];

        foreach ($items as $item) {

            $particular = strtolower(trim($item['particular']));
            $key = strtolower(trim($group)) . '|' . $particular;

            // DEFAULT VALUES
            $unit = $item['unit'] ?? '-';
            $rate = (float) ($item['rate'] ?? 0);
            $roe  = (float) ($item['roe'] ?? $globalROE);
            $qty  = (float) ($item['qty'] ?? 1);

            // Ocean freight override
            if (str_contains($particular, 'ocean freight')) {
                $roe = $oceanROE;
            }

            // Auto skip based on CBM
            if (str_contains($particular, 'van up to 3') && $cbm > 3) continue;
            if (str_contains($particular, '3 tone')      && $cbm <= 3) continue;

            // ==============
            // IF SAVED IN DB
            // ==============
            if ($saved->has($key)) {
                $d = $saved[$key];

                $filtered[$group][] = [
                    'id'         => $d->id,
                    'particular' => $item['particular'],
                    'unit'       => $d->unit,
                    'qty'        => $d->qty,
                    'rate'       => $d->rate,
                    'roe'        => $d->roe,
                    'amount'     => round($d->qty * $d->rate * $d->roe, 2),
                    'is_custom'  => $d->is_custom,
                ];

                continue;
            }

            // DEFAULT QTY LOGIC (ONLY FOR NON-SAVED ROWS)
            if (str_contains($particular, 'packing'))  $qty = $cbm;
            if (str_contains($particular, 'labour'))   $qty = 1;
            if (str_contains($particular, 'storage'))  $qty = $cbm;

            // ADD DEFAULT ROW
            $filtered[$group][] = [
                'id'         => null,
                'particular' => $item['particular'],
                'unit'       => $unit,
                'qty'        => $qty,
                'rate'       => $rate,
                'roe'        => $roe,
                'amount'     => round($qty * $rate * $roe, 2),
                'is_custom'  => 0,
            ];
        }
    }

    // ---------------------------
    // ADD CUSTOM DB ROWS SEPARATELY
    // ---------------------------
    foreach ($calc->details->where('is_custom', 1) as $d) {

        $g = $d->group_name;
        $p = strtolower(trim($d->particular));

        if (!isset($filtered[$g])) $filtered[$g] = [];

        // Avoid duplicating rows already listed
        $exists = false;
        foreach ($filtered[$g] as $row) {
            if (strtolower(trim($row['particular'])) === $p) {
                $exists = true;
                break;
            }
        }
        if ($exists) continue;

        // Add custom row
        $filtered[$g][] = [
            'id'         => $d->id,
            'particular' => $d->particular,
            'unit'       => $d->unit,
            'qty'        => $d->qty,
            'rate'       => $d->rate,
            'roe'        => $d->roe,
            'amount'     => round($d->qty * $d->rate * $d->roe, 2),
            'is_custom'  => 1,
        ];
    }

    // ---------------------------
    // RETURN VALUES TO THE BLADE
    // ---------------------------
    return view('admin.rate.rate_step2', [
        'calc'       => $calc,
        'rules'      => $filtered, // <-- FIXED: BLADE EXPECTS $rules
        'globalROE'  => $globalROE,
        'oceanROE'   => $oceanROE,
        'rateReports' => RateReport::latest()->take(50)->get(),
    ]);
}





/**
 * rateStep2Store - Clear & Reinsert strategy
 *
 * Reads all special fields and new rows; uses amount = qty * rate * roe (multiplication)
 * Deletes all existing calculation_details for this calculation, then inserts the new set.
 */
public function rateStep2Store(Request $request, $calcId)
{
    $calc = Calculation::findOrFail($calcId);

    $deletedIds = $request->input('delete_ids', []);

    $cbm = (float)$calc->cbm;
    $totalVolume = 50;

    $detailsData = [];

    $savedRows = $calc->details->keyBy(function ($d) {
        return strtolower(trim($d->group_name)) . '|' . strtolower(trim($d->particular));
    });

    $rules = config('rate_rules.origin');

    foreach ($rules as $group => $items) {
        foreach ($items as $item) {

            $p = $item['particular'];
            $key = strtolower(trim($group)) . '|' . strtolower(trim($p));

            // DO NOT RECREATE DELETED CUSTOM ROWS
            if ($savedRows->has($key)) {
                $rid = $savedRows[$key]->id;
                if (in_array($rid, $deletedIds)) {
                    continue;
                }
            }

            // Extract user input
            $qty  = $request->input("qty.$group.$p") 
                    ?? $request->input("labour_qty.$group.$p")
                    ?? $request->input("other_qty.$group.$p")
                    ?? $request->input("storage_qty.$group.$p")
                    ?? ($item['qty'] ?? 1);

            $rate = $request->input("rate.$group.$p") 
                    ?? $request->input("other_rate.$group.$p")
                    ?? $request->input("special_rate.$group.$p")
                    ?? $request->input("surrender_rate.$group.$p")
                    ?? ($item['rate'] ?? 0);

            $roe  = $request->input("roe.$group.$p") 
                    ?? ($item['roe'] ?? 1);

            if ($roe <= 0) $roe = 1;

            $amount = round($qty * $rate * $roe, 2);
            $total  = round(($amount * $cbm) / $totalVolume, 2);

            $isCustom = $savedRows->has($key) 
                        ? $savedRows[$key]->is_custom 
                        : 0;

            $detailsData[] = [
                'calculation_id' => $calcId,
                'group_name'     => $group,
                'particular'     => $p,
                'unit'           => $item['unit'] ?? '-',
                'qty'            => $qty,
                'rate'           => $rate,
                'roe'            => $roe,
                'amount'         => $amount,
                'total_charge'   => $total,
                'is_custom'      => $isCustom,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        // NEW CUSTOM ROWS
        if ($request->has("new_particular.$group")) {
            $parts = $request->input("new_particular.$group");

            foreach ($parts as $i => $pname) {

                if (!trim($pname)) continue;

                $qty  = (float) $request->input("new_qty.$group.$i");
                $rate = (float) $request->input("new_rate.$group.$i");
                $roe  = (float) $request->input("new_roe.$group.$i") ?: 1;

                $amount = round($qty * $rate * $roe, 2);
                $total  = round(($amount * $cbm) / $totalVolume, 2);

                $detailsData[] = [
                    'calculation_id' => $calcId,
                    'group_name'     => $group,
                    'particular'     => $pname,
                    'unit'           => $request->input("new_unit.$group.$i") ?? '-',
                    'qty'            => $qty,
                    'rate'           => $rate,
                    'roe'            => $roe,
                    'amount'         => $amount,
                    'total_charge'   => $total,
                    'is_custom'      => 1,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }
        }
    }

    DB::transaction(function () use ($calc, $detailsData, $deletedIds) {

        if (!empty($deletedIds)) {
            CalculationDetail::whereIn('id', $deletedIds)->delete();
        }

        CalculationDetail::insert($detailsData);
    });

    return redirect()->route('rate.step3', $calcId);
}



public function deleteStep2Row($id)
{
    $row = CalculationDetail::find($id);

    if (!$row || $row->is_custom != 1) {
        return response()->json(['success' => false]);
    }

    $row->delete();

    return response()->json(['success' => true]);
}







public function rateStep3($calcId)
{
    $calc = Calculation::with('destinationDetails')->findOrFail($calcId);

    // Fetch ROE from settings or fallback
    $destination = strtoupper($calc->port ?? 'KOCHI');
    $roeSettings = RoeSettings::where('destination', $destination)->first();
    $defaultRoe = $roeSettings ? $roeSettings->roe_value : 0.0439;

    // Default rule set (base structure)
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
            ['particular' => 'STORAGE', 'unit' => 'SHIPMENT', 'qty' => 1, 'rate' => 0, 'roe' => $defaultRoe],
            ['particular' => 'DUTY', 'unit' => 'SHIPMENT', 'qty' => 0, 'rate' => 0, 'roe' => $defaultRoe],
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

    // LOAD saved rows (group|particular)
    $saved = $calc->destinationDetails->keyBy(function ($d) {
        return strtolower($d->group_name . '|' . $d->particular);
    });

    // Merge saved data into default rows
    foreach ($rules as $group => &$items) {
        foreach ($items as &$item) {

            $key = strtolower($group . '|' . $item['particular']);

            if (isset($saved[$key])) {

                $row = $saved[$key];

                $item['unit']   = $row->unit;
                $item['qty']    = $row->qty;
                $item['rate']   = $row->rate;
                $item['roe']    = $row->roe;
                $item['amount'] = $row->amount;
            } else {
                // Fresh calculated row
                $qty = $item['qty'];
                $rate = $item['rate'];
                $roe = $item['roe'];

                $item['amount'] = round($rate * $qty * $roe, 2);
            }
        }
    }

    /** --------------------------------------------------------
     * FIX: Append saved rows that are NOT part of default rules
     * -------------------------------------------------------- */
    foreach ($calc->destinationDetails as $row) {

        $group = $row->group_name;

        // If group doesn't exist in default rules → create it
        if (!isset($rules[$group])) {
            $rules[$group] = [];
        }

        // Check if this particular already exists in rules
        $exists = false;
        foreach ($rules[$group] as $item) {
            if (strtolower($item['particular']) === strtolower($row->particular)) {
                $exists = true;
                break;
            }
        }

        // If not in default → append it
        if (!$exists) {
            $rules[$group][] = [
                'particular' => $row->particular,
                'unit'       => $row->unit,
                'qty'        => $row->qty,
                'rate'       => $row->rate,
                'roe'        => $row->roe,
                'amount'     => $row->amount,
                'is_custom' => $row->is_custom,

            ];
        }
    }

    /** END FIX */

    $rateReports = RateReport::latest()->take(50)->get();

    return view('admin.rate.rate_step3', compact('calc', 'rules', 'rateReports'));
}

public function rateStep3Store(Request $request, $calcId)
{
    $calc = Calculation::findOrFail($calcId);
    $cbm  = (float) ($calc->cbm ?? 1);

    //----------------------------------------------------------------------
    // 1. EXISTING RULES FROM FORM  (includes default rows + custom rows)
    //----------------------------------------------------------------------
    $rules = $request->input('rules', []);

    //----------------------------------------------------------------------
    // 2. ADD NEW ROWS (these automatically become custom)
    //----------------------------------------------------------------------
    $newParticular = $request->input('new_particular', []);
    $newUnit       = $request->input('new_unit', []);
    $newQty        = $request->input('new_qty', []);
    $newRate       = $request->input('new_rate', []);
    $newRoe        = $request->input('new_roe', []);

    foreach ($newParticular as $group => $rows) {
        foreach ($rows as $i => $p) {

            if (!trim($p)) continue; // skip empty row

            $rules[$group][] = [
                'particular' => $p,
                'unit'       => $newUnit[$group][$i] ?? '-',
                'qty'        => (float) ($newQty[$group][$i] ?? 1),
                'rate'       => (float) ($newRate[$group][$i] ?? 0),
                'roe'        => (float) ($newRoe[$group][$i] ?? 1),
                'is_custom'  => 1, // marking new rows as custom
            ];
        }
    }

    //----------------------------------------------------------------------
    // 3. BUILD INSERT PAYLOAD
    //----------------------------------------------------------------------
    $detailsData = [];
    $sumTotalCharge = 0;

    foreach ($rules as $group => $items) {
        foreach ($items as $item) {

            $particular = $item['particular'];
            $unit = $item['unit'];
            $qty  = (float) ($item['qty'] ?? 1);
            $rate = (float) ($item['rate'] ?? 0);
            $roe  = (float) ($item['roe'] ?? 1);

            $amountAED   = $roe > 0 ? ($rate * $qty) / $roe : 0;
            $totalCharge = ($amountAED * $cbm) / 50;

            $detailsData[] = [
                'calculation_id' => $calc->id,
                'customer_name'  => $calc->customer_name,

                'group_name'     => $group,
                'particular'     => $particular,
                'unit'           => $unit,
                'qty'            => $qty,
                'rate'           => $rate,
                'roe'            => $roe,
                'amount'         => $amountAED,
                'total_charge'   => $totalCharge,

                // keep custom flag if exists
                'is_custom'      => $item['is_custom'] ?? 0,

                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            $sumTotalCharge += $totalCharge;
        }
    }

    //----------------------------------------------------------------------
    // 4. SAVE TO DB
    //----------------------------------------------------------------------
    DB::transaction(function () use ($calc, $detailsData, $sumTotalCharge) {

        // remove old rows (missing ones = deleted)
        $calc->destinationDetails()->delete();

        // insert fresh rows
        DestinationCalculationDetail::insert($detailsData);

        // update final total
        $calc->update([
            'final_amount' => $sumTotalCharge,
        ]);
    });

    //----------------------------------------------------------------------
    // 5. BUILD REPORT (unchanged)
    //----------------------------------------------------------------------
    $originDB = $calc->details()->get();
    $destinationDB = $calc->destinationDetails()->get();

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

    $destinationGrouped = [];
    $destinationTotal = 0;

    foreach ($destinationDB as $d) {
        $g = $d->group_name;
        if (!isset($destinationGrouped[$g])) $destinationGrouped[$g] = [];

        $destinationTotal += $d->total_charge;

        $destinationGrouped[$g][] = [
            'particular'  => $d->particular,
            'unit'        => $d->unit,
            'qty'         => $d->qty,
            'rate'        => $d->rate,
            'roe'         => $d->roe,
            'amount'      => $d->amount,
            'total_charge'=> $d->total_charge,
        ];
    }

    $grandTotalAED = $originTotal + $destinationTotal;

    RateReport::create([
        'calculation_id' => $calc->id,
        'customer_name'  => $calc->customer_name,
        'total_amount'   => $grandTotalAED,

        'report_data'    => [
            'customer_info' => [
                'name'          => $calc->customer_name,
                'from_location' => $calc->from_location,
                'to_location'   => $calc->to_location,
                'port'          => $calc->port,
                'cbm'           => $calc->cbm,
                'created_at'    => $calc->created_at->format('d M Y'),
            ],

            'origin'            => $originGrouped,
            'origin_total'      => $originTotal,

            'destination'       => $destinationGrouped,
            'destination_total' => $destinationTotal,

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

    $originGroups = $calc->details->groupBy('group_name');
    $destinationGroups = $calc->destinationDetails->groupBy('group_name');

    $cbm = $calc->cbm ?? 0;
    $ratio = $cbm / 50;

    $originJson = [];
    $destinationJson = [];

    $originTotal = 0;
    $destTotal = 0;

    // ========= ORIGIN JSON BUILD =========
    foreach ($originGroups as $group => $rows) {

        $originJson[$group] = [];

        foreach ($rows as $r) {

            $amount = ($r->qty * $r->rate) / ($r->roe ?: 1);
            $totalCharge = $amount * $ratio;

            $originTotal += $totalCharge;

            $originJson[$group][] = [
                "qty" => (string) number_format($r->qty, 2),
                "roe" => (string) number_format($r->roe, 4),
                "rate" => (string) number_format($r->rate, 2),
                "unit" => $r->unit,
                "amount" => round($amount, 2),
                "particular" => $r->particular,
                "total_charge" => round($totalCharge, 2)
            ];
        }
    }

    // ========= DESTINATION JSON BUILD =========
    foreach ($destinationGroups as $group => $rows) {

        $destinationJson[$group] = [];

        foreach ($rows as $r) {

            $amount = ($r->qty * $r->rate) / ($r->roe ?: 1);
            $totalCharge = $amount * $ratio;

            $destTotal += $totalCharge;

            $destinationJson[$group][] = [
                "qty" => (string) number_format($r->qty, 2),
                "roe" => (string) number_format($r->roe, 4),
                "rate" => (string) number_format($r->rate, 2),
                "unit" => $r->unit,
                "amount" => round($amount, 2),
                "particular" => $r->particular,
                "total_charge" => round($totalCharge, 2)
            ];
        }
    }

    $grandTotal = $originTotal + $destTotal;

    // ========= FINAL JSON STRUCTURE =========
    $json = [
        "origin" => $originJson,
        "destination" => $destinationJson,
        "origin_total" => round($originTotal, 2),
        "destination_total" => round($destTotal, 2),
        "grand_total" => round($grandTotal, 2),
        "customer_info" => [
            "name" => $calc->customer_name,
            "cbm" => $cbm,
            "port" => $calc->port,
            "from_location" => $calc->from_location,
            "to_location" => $calc->to_location,
            "created_at" => $calc->created_at->format('d M Y')
        ]
    ];

    // ========= SAVE TO DB =========
    RateReport::create([
        'calculation_id' => $calc->id,
        'customer_name'  => $calc->customer_name,
        'total_amount'   => $grandTotal,
        'total_charges'  => $grandTotal,
        'report_data'    => json_encode($json)
    ]);

    // For frontend display
    return view('admin.rate.rate_report_full', compact(
        'calc',
        'originGroups',
        'destinationGroups'
    ));
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

public function deleteAllReports(Request $request)
{
    $count = RateReport::count();

    if ($count === 0) {
        return $request->expectsJson()
            ? response()->json(['message' => 'No reports to delete'], 404)
            : back()->with('error', 'No reports to delete.');
    }

    RateReport::query()->delete(); // uses model events, not hard truncate

    return $request->expectsJson()
        ? response()->json(['message' => 'All rate reports deleted successfully'])
        : back()->with('success', 'All rate reports deleted successfully.');
}



}