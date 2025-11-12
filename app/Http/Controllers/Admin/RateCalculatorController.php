<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calculation;
use App\Models\CalculationDetail;
use App\Models\DestinationCalculationDetail;
use App\Models\Customer;
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

    return view('admin.rate.rate_step1', compact('customers', 'calc'));
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

    // 🔹 Build lookup of saved details
    $saved = $calc->details->keyBy(function ($d) {
        return strtolower(trim($d->group_name)) . '|' . strtolower(trim($d->particular));
    });

    $filtered = [];

    foreach ($rules as $group => $items) {
        $filtered[$group] = [];

        foreach ($items as $item) {
            $particular = trim($item['particular']);
            $key = strtolower(trim($group)) . '|' . strtolower($particular);

            $unit = $item['unit'] ?? '-';
            $rate = (float) ($item['rate'] ?? 0);
            $roe  = (float) ($item['roe'] ?? 1);
            $qty  = (float) ($item['qty'] ?? 1);
            $amount = 0;

            // Skip irrelevant rows (van / 3 tone logic)
            if (str_contains($particular, 'collection - van up to 3 cbm') && $cbm > 3) continue;
            if (str_contains($particular, 'collection - 3 tone up to 15 cbm') && $cbm <= 3) continue;

            // 🔹 If we already have saved data, use it
            if ($saved->has($key)) {
                $d = $saved[$key];
                $item['unit']   = $d->unit;
                $item['qty']    = $d->qty;
                $item['rate']   = $d->rate;
                $item['roe']    = $d->roe;
                $item['amount'] = $d->amount;
            } else {
                // Otherwise, compute default qty and amount
                $isCollection = str_contains($particular, 'collection - van up to 3 cbm') ||
                                str_contains($particular, 'collection - 3 tone up to 15 cbm');
                $isLabour  = str_contains(strtolower($particular), 'labour');
                $isOther   = str_contains(strtolower($particular), 'other charges');
                $isSpecial = str_contains(strtolower($particular), 'special services');
                $isPacking = str_contains(strtolower($particular), 'packing & materials');

                if ($isLabour) {
                    $qty = 0;
                    $amount = 0;
                } elseif ($isPacking) {
                    $qty = $cbm;
                    $amount = round($rate * $cbm * $roe, 2);
                } elseif ($isOther || $isSpecial) {
                    $qty = $cbm ?: 1;
                    $amount = 0;
                } elseif ($unit === 'CBM' || $isCollection) {
                    $qty = $cbm ?: 1;
                    $amount = round($rate * $qty * $roe, 2);
                } else {
                    $amount = round($rate * $qty * $roe, 2);
                }

                $item['unit']   = $unit;
                $item['qty']    = $qty;
                $item['rate']   = $rate;
                $item['roe']    = $roe;
                $item['amount'] = $amount;
            }

            $filtered[$group][] = $item;
        }
    }

    return view('admin.rate.rate_step2', [
        'calc' => $calc,
        'rules' => $filtered,
    ]);
}



public function rateStep2Store(Request $request, $calcId)
{
    $calc = Calculation::findOrFail($calcId);
    $rules = config('rate_rules.origin');
    $cbm   = (float) ($calc->cbm ?? 0);
    $detailsData = [];

    foreach ($rules as $group => $items) {
        foreach ($items as $item) {
            $particular = $item['particular'];
            $key = strtolower($particular);
            $unit = $item['unit'] ?? '-';
            $roe  = (float) ($item['roe'] ?? 1);
            $rate = (float) ($item['rate'] ?? 0);
            $qty  = (float) ($item['qty'] ?? 1);
            $amount = 0;

            // Skip irrelevant rows
            if (str_contains($key, 'van up to 3') && $cbm > 3) continue;
            if (str_contains($key, '3 tone') && $cbm <= 3) continue;

            // Always check if user entered new qty / rate
            $userQty  = $request->input("qty.$group.$particular");
            $userRate = $request->input("rate.$group.$particular");

            if ($userQty !== null && $userQty !== '') $qty = (float) $userQty;
            if ($userRate !== null && $userRate !== '') $rate = (float) $userRate;

            // Conditional logic
            if (str_contains($key, 'labour')) {
                $qty = (float) $request->input("labour_qty.$group.$particular", $qty);
                $amount = round($rate * $qty * $roe, 2);
            } elseif (str_contains($key, 'packing & materials')) {
                $unit = $request->input("packing_unit.$group.$particular", $unit);
                $qty  = $cbm;
                $amount = round($rate * $qty * $roe, 2);
            } elseif (str_contains($key, 'other charges')) {
                $unit = $request->input("other_unit.$group.$particular", $unit);
                $rate = (float) $request->input("other_rate.$group.$particular", $rate);
                $qty  = $cbm ?: 1;
                $amount = round($rate * $qty * $roe, 2);
            } elseif (str_contains($key, 'special services')) {
                $rate = (float) $request->input("special_rate.$group.$particular", $rate);
                $qty  = $cbm ?: 1;
                $amount = round($rate * $qty * $roe, 2);
            } elseif ($unit === 'CBM') {
                $qty = $cbm ?: 1;
                $amount = round($rate * $qty * $roe, 2);
            } else {
                $amount = round($rate * $qty * $roe, 2);
            }

            $detailsData[] = [
                'calculation_id' => $calc->id,
                'group_name'     => $group,
                'particular'     => $particular,
                'unit'           => $unit,
                'qty'            => $qty,
                'rate'           => $rate,
                'roe'            => $roe,
                'amount'         => $amount,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }
    }

    DB::transaction(function () use ($calc, $detailsData) {
        // Delete existing details and reinsert
        $calc->details()->delete();
        CalculationDetail::insert($detailsData);

        // ✅ Update total & ensure customer_name always stays synced
        $calc->update([
            'total_amount'  => array_sum(array_column($detailsData, 'amount')),
            'customer_name' => optional($calc->customer)->name, // sync from relation
        ]);
    });

    return redirect()->route('rate.step3', $calc->id)
        ->with('success', 'Origin charges saved successfully (with correct qty and rate).');
}



    


public function rateStep3($calcId)
{
    $calc = Calculation::with('destinationDetails')->findOrFail($calcId);

    // ✅ Correct AED → INR ROE (editable later)
    $defaultRoe = 22.80;   // 1 AED = 22.80 INR (set daily or pull from API if you wish)

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
                $item['rate'] = $saved->rate;   // AED
                $item['roe']  = $saved->roe;    // ₹ per AED
                $item['amount'] = $saved->amount; // INR
            } else {
                $item['qty'] = $cbm ?: 1;
                $item['amount'] = round($item['rate'] * $item['qty'] * $item['roe'], 2); // AED → INR
            }
        }
    }

    return view('admin.rate.rate_step3', compact('calc', 'rules'));
}



//  Step 3 Store
public function rateStep3Store(Request $request, $calcId)
{
    $calc = Calculation::findOrFail($calcId);
    $rules = $request->input('rules', []);
    $cbm   = (float) ($calc->cbm ?? 1);

    $detailsData = [];
    $total = 0;

    foreach ($rules as $group => $items) {
        foreach ($items as $item) {
            $particular = $item['particular'] ?? '';
            $unit  = $item['unit'] ?? '-';
            $qty   = (float) ($item['qty'] ?? 1);
            $rate  = (float) ($item['rate'] ?? 0);   // AED
            $roe   = (float) ($item['roe'] ?? 22.80); // ₹ per AED
            $amountInINR = round($rate * $qty * $roe, 2); // Convert to INR

            $detailsData[] = [
                'calculation_id' => $calc->id,
                'customer_name'  => $calc->customer_name,
                'group_name'     => $group,
                'particular'     => $particular,
                'unit'           => $unit,
                'qty'            => $qty,
                'rate'           => $rate,          // AED
                'roe'            => $roe,           // ₹ per AED
                'amount'         => $amountInINR,   // INR
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            $total += $amountInINR;
        }
    }

    DB::transaction(function () use ($calc, $detailsData, $total) {
        $calc->destinationDetails()->delete();
        if ($detailsData) DestinationCalculationDetail::insert($detailsData);
        $calc->update(['final_amount' => $total]);
    });

    return redirect()->route('rate.report.full', $calc->id)
        ->with('success', 'All destination charges saved successfully!');
}



public function rateReportFull($id)
{
    $calc = Calculation::with(['details', 'destinationDetails'])->findOrFail($id);

    // Grouping for display
    $originGroups = $calc->details->groupBy('group_name');
    $destinationGroups = $calc->destinationDetails->groupBy('group_name');

    return view('admin.rate.rate_report_full', [
        'calc' => $calc,
        'originGroups' => $originGroups,
        'destinationGroups' => $destinationGroups,
    ]);
}


}