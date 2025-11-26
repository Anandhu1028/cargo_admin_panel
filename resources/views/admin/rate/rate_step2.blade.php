@extends('admin.layouts.app')
@section('title', 'Rate Calculator - Step 2 (Origin Charges)')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="container-fluid">

            <!-- 🔹 Page Title -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                        <h4 class="page-title">STEP 2 - 3 </h4>
                        <div>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                                <li class="breadcrumb-item active">Origin Charges</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🔹 Main Card -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">LCL CHARGES VIA KOCHI SEA PORT CALCULATION</h5>
                    <p class="small text-muted mb-4">
                        Destination Port: <strong>{{ strtoupper($calc->port) }}</strong> &nbsp;&nbsp; |
                        &nbsp;&nbsp; Customer: <strong>{{ $calc->customer_name }}</strong> &nbsp;&nbsp; |
                        &nbsp;&nbsp; CBM: <strong id="cbm-val">{{ number_format($calc->cbm ?? 0, 2) }}</strong>
                    </p>

                    <form method="POST" action="{{ route('rate.step2.store', $calc->id) }}">
                        @csrf

                        <div id="deleted_rows"></div>

                        @foreach ($rules as $group => $items)
                        @php $groupKey = strtolower($group); @endphp

                        <div class="mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold text-primary mb-0">
                                        {{ strtoupper(str_replace('_', ' ', $group)) }}
                                    </h6>
                                    <button type="button" class="btn add-row-btn" data-group="{{ $groupKey }}" style="background:linear-gradient(90deg,#141824,#1e2736 60%,#253043);
                                            color:#fff;border:none;padding:6px 14px;
                                            font-weight:600;border-radius:8px;letter-spacing:0.4px;
                                            box-shadow:0 4px 12px rgba(20,24,36,0.4);
                                            transition:all .3s ease; font-size:13px;"
                                        onmouseover="this.style.background='linear-gradient(90deg,#1e2736,#2b374f 60%,#34445e)';this.style.transform='translateY(-2px)'"
                                        onmouseout="this.style.background='linear-gradient(90deg,#141824,#1e2736 60%,#253043)';this.style.transform='translateY(0)'">

                                        <i class="fas fa-plus" style="color:#00c6ff; margin-right:5px;"></i>
                                        Add Row
                                    </button>

                                </div>

                                <div class="card-body p-0 mt-">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light text-center">
                                                <tr>
                                                    <th>Particulars</th>
                                                    <th>Unit</th>
                                                    <th>Qty</th>
                                                    <th>Rate</th>
                                                    <th>ROE</th>
                                                    <th class="text-end pe-4 no-left-pad">Amount</th>

                                                    <th>Total Charge</th>

                                                </tr>
                                            </thead>

                                            <tbody class="group-body text-center" data-group="{{ $groupKey }}">

@foreach ($items as $row)
@php
    $particular = strtolower($row['particular'] ?? '');

    $isLabour    = str_contains($particular, 'labour');
    $isOther     = str_contains($particular, 'other charges');
    $isSpecial   = str_contains($particular, 'special services');
    $isPacking   = str_contains($particular, 'packing & materials');
    $isStorage   = str_contains($particular, 'storage');
    $isSurrender = str_contains($particular, 'surrender');

    $unit = $row['unit'] ?? '-';
    $rate = $row['rate'] ?? 0;

    if ($isOther) {
        $roe = 1;
    } elseif (str_contains($particular, 'ocean freight')) {
        $roe = ($oceanROE > 0) ? $oceanROE : 1;
    } else {
        $roe = ($row['roe'] > 0) ? $row['roe'] : $globalROE;
    }

    $qty    = $row['qty'] ?? ($calc->cbm ?? 1);
    $amount = round($qty * $rate * $roe, 2);
@endphp

@if (($calc->cbm <= 3 && str_contains($particular, '3 tone')) ||
    ($calc->cbm > 3  && str_contains($particular, 'van up to 3')))
    @continue
@endif

<tr class="calc-row"
    data-id="{{ $row['id'] }}"
    data-custom="{{ $row['is_custom'] }}"
    data-roe="{{ $roe }}"
    data-particular="{{ $particular }}">

    <td class="text-start ps-3">{{ $row['particular'] }}</td>

    <td>
        @if ($isPacking)
            CBM
        @elseif ($isOther)
            <input type="text"
                name="other_unit[{{ $group }}][{{ $row['particular'] }}]"
                value="{{ $unit !== '-' ? $unit : '' }}"
                class="form-control form-control-sm short-input text-center">
        @elseif ($isSpecial)
            <input type="text"
                name="special_desc[{{ $group }}][{{ $row['particular'] }}]"
                value="{{ $row['special_desc'] ?? '' }}"
                class="form-control form-control-sm short-input text-center">
        @else
            {{ $unit }}
            <input type="hidden"
                name="unit[{{ $group }}][{{ $row['particular'] }}]"
                value="{{ $unit }}">
        @endif
    </td>

    <td>
        @if ($isLabour)
            <input type="number" step="1" min="0"
                name="labour_qty[{{ $group }}][{{ $row['particular'] }}]"
                value="{{ $qty }}"
                class="form-control form-control-sm short-input text-end"
                data-field="qty">
        @elseif ($isOther)
            <input type="number" step="0.01" min="0"
                name="other_qty[{{ $group }}][{{ $row['particular'] }}]"
                value="{{ $qty }}"
                class="form-control form-control-sm short-input text-end"
                data-field="qty">
        @elseif ($isStorage)
            {{ number_format($calc->cbm, 2) }}
            <input type="hidden"
                name="storage_qty[{{ $group }}][{{ $row['particular'] }}]"
                value="{{ $calc->cbm }}"
                data-field="qty">
        @else
            {{ number_format($qty, 2) }}
            <input type="hidden"
                name="qty[{{ $group }}][{{ $row['particular'] }}]"
                value="{{ $qty }}"
                data-field="qty">
        @endif
    </td>

    <td>
        @if ($isOther)
            <input type="number" step="0.01" min="0"
                name="other_rate[{{ $group }}][{{ $row['particular'] }}]"
                value="{{ $rate }}"
                class="form-control form-control-sm short-input text-end"
                data-field="rate">
        @elseif ($isSpecial)
            <input type="number" step="0.01" min="0"
                name="special_rate[{{ $group }}][{{ $row['particular'] }}]"
                value="{{ $rate }}"
                class="form-control form-control-sm short-input text-end"
                data-field="rate">
        @elseif ($isSurrender)
            <input type="number" step="0.01" min="0"
                name="surrender_rate[{{ $group }}][{{ $row['particular'] }}]"
                value="{{ $rate }}"
                class="form-control form-control-sm short-input text-end"
                data-field="rate">
        @else
            {{ number_format($rate, 2) }}
            <input type="hidden"
                name="rate[{{ $group }}][{{ $row['particular'] }}]"
                value="{{ $rate }}"
                data-field="rate">
        @endif
    </td>

    <td>{{ number_format($roe, 4) }}</td>

    <td class="text-end pe-3">
        <input type="text" readonly
            class="form-control-plaintext text-end fw-semibold amount-field"
            value="{{ number_format($amount, 2) }} AED">
    </td>

    <td class="text-end pe-3">
        <input type="text" readonly
            class="form-control-plaintext text-end fw-semibold total-charge-field"
            value="{{ number_format(($amount * $calc->cbm) / 50, 2) }} AED">

       @if($row['is_custom'] == 1)
    <button type="button"
            class="delete-row-btn"
            data-id="{{ $row['id'] }}"
            style="border:none;background:none;padding:0;">
        <i class="fa-regular fa-trash-can"
           style="color:#d00000;cursor:pointer;font-size:16px;"></i>
    </button>
@endif



    </td>

</tr>
@endforeach


{{-- NEW ROW TEMPLATE --}}
<tr class="new-row-template d-none calc-row" data-id="" data-custom="1">

    <td>
        <input type="text" name="new_particular[{{ $group }}][]"
            class="form-control form-control-sm text-start"
            placeholder="Particular">
    </td>

    <td>
        <input type="text" name="new_unit[{{ $group }}][]"
            class="form-control form-control-sm text-center short-input"
            placeholder="Unit">
    </td>

    <td>
        <input type="number" step="0.01" min="0"
            name="new_qty[{{ $group }}][]"
            class="form-control form-control-sm text-end short-input"
            placeholder="Qty" data-field="qty">
    </td>

    <td>
        <input type="number" step="0.01" min="0"
            name="new_rate[{{ $group }}][]"
            class="form-control form-control-sm text-end short-input"
            placeholder="Rate" data-field="rate">
    </td>

    <td>
        <input type="number" step="0.0001" min="0"
            name="new_roe[{{ $group }}][]"
            class="form-control form-control-sm text-end short-input"
            data-field="roe" value="1">
    </td>

    <td class="text-end">
        <input type="text" readonly
            class="form-control-plaintext text-end fw-semibold amount-field"
            value="0.00 AED">
    </td>

    <td class="text-end">
        <input type="text" readonly
            class="form-control-plaintext text-end fw-semibold total-charge-field"
            value="0.00 AED">

        {{-- Not in DB yet — only remove from DOM via JS or form if you want --}}
        <button type="button"
            class="btn p-0 remove-new-row"
            style="background:none; border:none;">
            <i class="fa-regular fa-trash-can"
               style="color:#d00000; font-size:16px; margin-left:8px;"></i>
        </button>
    </td>

</tr>


<tr class="table-primary group-total-row">
    <td colspan="6" class="text-end fw-bold">
        TOTAL {{ strtoupper(str_replace('_', ' ', $group)) }}
    </td>
    <td class="text-end pe-3 fw-bold">
        <span class="group-total">0.00 AED</span>
    </td>
</tr>

</tbody>




                                        </table>


                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <!-- 🔹 Footer Buttons -->
                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('rate.step1') }}" class="btn"
                                style="background:linear-gradient(90deg,#2b2f3a,#3a404e 60%,#454d60);color:#fff;border:none;padding:9px 22px;font-weight:600;border-radius:8px;letter-spacing:0.4px;box-shadow:0 4px 12px rgba(20,24,36,0.4);transition:all .3s ease"
                                onmouseover="this.style.background='linear-gradient(90deg,#3a404e,#464e62 60%,#505b70)';this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='linear-gradient(90deg,#2b2f3a,#3a404e 60%,#454d60)';this.style.transform='translateY(0)'">
                                <i class="fas fa-arrow-left me-2" style="color:#00c6ff;"></i> Previous
                            </a>

                            <button type="submit" class="btn"
                                style="background:linear-gradient(90deg,#141824,#1e2736 60%,#253043);color:#fff;border:none;padding:9px 22px;font-weight:600;border-radius:8px;letter-spacing:0.4px;box-shadow:0 4px 12px rgba(20,24,36,0.4);transition:all .3s ease"
                                onmouseover="this.style.background='linear-gradient(90deg,#1e2736,#2b374f 60%,#34445e)';this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='linear-gradient(90deg,#141824,#1e2736 60%,#253043)';this.style.transform='translateY(0)'">
                                Next <i class="fas fa-arrow-right ms-2" style="color:#00c6ff;"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<form id="deleteRowForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<style>
    .table td,
    .table th {
        vertical-align: middle !important;
    }

    .short-input {
        max-width: 90px !important;
        display: inline-block;
        padding-right: 4px;
    }

    .group-total-row td {
        background: #f3f7ff;
    }

    .table th:first-child,
    .table td:first-child {
        text-align: left !important;
        padding-left: 16px !important;
    }

    .table th:last-child,
    .table td:last-child {
        text-align: right !important;
        padding-right: 16px !important;
    }
</style>
@endsection

@push('scripts')
<script>

document.querySelectorAll('.delete-row-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;

        const form = document.getElementById('deleteRowForm');
        form.action = `/admin/rate-calculator/step2/delete-row/${id}`;
        form.submit();
    });
});

    




    document.querySelectorAll(".add-row-btn").forEach(btn => {
        btn.addEventListener("click", function () {

            const group = this.dataset.group;
            const body = document.querySelector(`tbody[data-group="${group}"]`);
            const tpl = body.querySelector(".new-row-template");

            if (!tpl) {
                alert("No template row found for " + group);
                return;
            }

            const newRow = tpl.cloneNode(true);
            newRow.classList.remove("d-none", "new-row-template");
            newRow.classList.add("calc-row");

            body.insertBefore(newRow, body.querySelector(".group-total-row"));

            recalcTotals();
        });
    });


    document.addEventListener("DOMContentLoaded", function () {
        recalcTotals(); // calculate totals instantly on page load
    });
    document.addEventListener("input", function (e) {

        // When ROE changes
        if (e.target.dataset.field === "roe") {
            const row = e.target.closest(".calc-row");
            if (!row) return;

            // Update row.dataset.roe
            row.dataset.roe = parseFloat(e.target.value) || 1;

            // Trigger JS calculation same as qty/rate does
            const qty = parseFloat(row.querySelector('[data-field="qty"]')?.value) || 0;
            const rate = parseFloat(row.querySelector('[data-field="rate"]')?.value) || 0;
            const roe = parseFloat(e.target.value) || 1;

            const amount = qty * rate * roe;
            row.querySelector(".amount-field").value =
                amount.toLocaleString("en-IN", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + " AED";

            const cbm = parseFloat(document.getElementById("cbm-val").textContent) || 0;
            const totalChargeRow = (amount * cbm) / 50;

            row.querySelector(".total-charge-field").value =
                totalChargeRow.toLocaleString("en-IN", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + " AED";

            recalcTotals();
            return;
        }
    });



    // When user types qty, rate or roe → recalc that row + totals
    document.addEventListener("input", function (e) {
        const input = e.target;

        const row = input.closest(".calc-row");
        if (!row) return;

        // 🔹 FIX: Handle ROE update
        if (input.name && input.name.includes("new_roe")) {
            let newRoe = parseFloat(input.value) || 1;
            row.dataset.roe = newRoe;
        }

        // If not qty / rate / roe field → only update totals
        if (!input.dataset.field) {
            recalcTotals();
            return;
        }

        // Extract values
        const qty = parseFloat(row.querySelector('[data-field="qty"]')?.value) || 0;
        const rate = parseFloat(row.querySelector('[data-field="rate"]')?.value) || 0;

        // Read ROE from dataset (updated above)
        let roe = parseFloat(row.dataset.roe) || 1;
        if (roe <= 0) roe = 1;

        // Calculate amount
        const amount = qty * rate * roe;

        // Update amount field
        row.querySelector(".amount-field").value =
            amount.toLocaleString("en-IN", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + " AED";

        // Total charge calculation
        const cbm = parseFloat(document.getElementById("cbm-val").textContent) || 0;
        const totalChargeRow = (amount * cbm) / 50;

        row.querySelector(".total-charge-field").value =
            totalChargeRow.toLocaleString("en-IN", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + " AED";

        recalcTotals();
    });



    // GLOBAL TOTAL CALCULATION (always run)
    function recalcTotals() {
        document.querySelectorAll(".group-body").forEach(group => {
            let total = 0;

            group.querySelectorAll(".total-charge-field").forEach(tc => {
                const num = parseFloat(tc.value.replace(/[^0-9.]/g, "")) || 0;
                total += num;
            });


            const el = group.querySelector(".group-total");
            if (el) {
                el.textContent =
                    total.toLocaleString("en-IN", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + " AED";
            }
        });
    }




    document.querySelectorAll('.storage-rate-input').forEach(inp => {
        inp.addEventListener('input', function () {
            const row = this.closest('tr');
            const qty = parseFloat(row.querySelector('input[name*="storage_qty"]')?.value) || 0;
            const rate = parseFloat(this.value) || 0;
            const roe = parseFloat(this.dataset.roe) || 1;

            const amount = qty * rate * roe;

            row.querySelector('.storage-amount-display').value =
                amount.toLocaleString("en-IN", { minimumFractionDigits: 2 }) + ' AED';

            updateAllGroupTotals();
        });
    });

    document.addEventListener("DOMContentLoaded", function () {

        const formatNumber = n => Number(n || 0).toLocaleString("en-IN", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        const toNumber = s => parseFloat((s || "0").replace(/[^0-9.\-]/g, "")) || 0;

        window.updateAllGroupTotals = () => {
            document.querySelectorAll('.group-body').forEach(g => {
                let total = 0;
                g.querySelectorAll('.static-amount, .labour-amount, .packing-amount-display, .other-amount-display, .special-amount-display, .storage-amount-display, .surrender-amount-display')
                    .forEach(el => total += toNumber(el.value || el.textContent));
                const totalEl = g.querySelector('.group-total');
                if (totalEl) totalEl.textContent = formatNumber(total) + ' AED';
            });
        };

        // Initialize storage amounts
        document.querySelectorAll('.storage-amount-display').forEach(el => {
            const row = el.closest('tr');
            const qty = toNumber(row.querySelector('input[name*="storage_qty"]')?.value
                || row.querySelector('input[name*="qty"]')?.value);
            const rate = toNumber(row.querySelector('input[type="hidden"][name*="rate"]')?.value);
            const roe = toNumber(row.querySelector('input[type="hidden"][name*="rate"]')?.dataset.roe);

            el.value = formatNumber(qty * rate * roe) + ' AED';
        });

        // LABOUR
        document.querySelectorAll('.labour-input').forEach(inp => inp.addEventListener('input', function () {
            const rate = toNumber(this.dataset.rate);
            const roe = toNumber(this.dataset.roe);
            const qty = toNumber(this.value);

            const amount = rate * qty * roe;

            this.closest('tr').querySelector('.labour-amount').value =
                formatNumber(amount) + ' AED';

            updateAllGroupTotals();
        }));

        // OTHER CHARGES
        document.querySelectorAll('.other-qty-input, .other-rate-input').forEach(inp => {
            inp.addEventListener('input', function () {
                const row = this.closest('tr');
                const qty = toNumber(row.querySelector('.other-qty-input')?.value);
                const rate = toNumber(row.querySelector('.other-rate-input')?.value);
                const roe = toNumber(row.querySelector('.other-rate-input')?.dataset.roe);

                const amount = qty * rate * roe;

                row.querySelector('.other-amount-display').value =
                    formatNumber(amount) + ' AED';

                updateAllGroupTotals();
            });
        });

        // SPECIAL SERVICES
        document.querySelectorAll('.special-rate-input').forEach(inp => {
            inp.addEventListener('input', function () {
                const cbm = toNumber(this.dataset.cbm);
                const roe = toNumber(this.dataset.roe);
                const rate = toNumber(this.value);

                const amount = cbm * rate * roe;

                this.closest('tr').querySelector('.special-amount-display').value =
                    formatNumber(amount) + ' AED';

                updateAllGroupTotals();
            });
        });

    });
</script>
@endpush