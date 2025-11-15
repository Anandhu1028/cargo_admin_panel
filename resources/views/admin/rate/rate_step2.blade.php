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

                        @foreach ($rules as $group => $items)
                        @php $groupKey = strtolower($group); @endphp

                        <div class="mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold text-primary mb-0">
                                        {{ strtoupper(str_replace('_', ' ', $group)) }}
                                    </h6>
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
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>

                                            <tbody class="group-body text-center" data-group="{{ $groupKey }}">
                                                @foreach ($items as $row)
                                                @php
                                                $particular = strtolower($row['particular'] ?? '');

                                                $isLabour = str_contains($particular, 'labour');
                                                $isOther = str_contains($particular, 'other charges');
                                                $isSpecial = str_contains($particular, 'special services');
                                                $isPacking = str_contains($particular, 'packing & materials');
                                                $isStorage = str_contains($particular, 'storage');
                                                $isSurrender = str_contains($particular, 'surrender');

                                                $unit = $row['unit'] ?? '-';
                                                $rate = $row['rate'] ?? 0;

                                                // ROE Logic
                                                if ($isOther) {
                                                $roe = 1;
                                                } elseif (str_contains($particular, 'ocean freight')) {
                                                $roe = ($oceanROE > 0) ? $oceanROE : 1;
                                                } else {
                                                $roe = ($row['roe'] > 0) ? $row['roe'] : $globalROE;
                                                }

                                                $qty = $row['qty'] ?? ($calc->cbm ?? 1);

                                                $amount = round($qty * $rate * $roe, 2);
                                                @endphp

                                                {{-- Skip irrelevant collection rows --}}
                                                @if (($calc->cbm <= 3 && str_contains($particular, '3 tone' )) ||
                                                    ($calc->cbm > 3 && str_contains($particular, 'van up to 3')))
                                                    @continue
                                                    @endif

                                                    <tr class="calc-row" data-roe="{{ $roe }}"
                                                        data-particular="{{ $particular }}">

                                                        {{-- PARTICULARS --}}
                                                        <td class="text-start ps-3">{{ $row['particular'] }}</td>

                                                        {{-- UNIT --}}
                                                        <td>
                                                            @if ($isPacking)
                                                            CBM

                                                            @elseif ($isOther)
                                                            <input type="text"
                                                                name="other_unit[{{ $group }}][{{ $row['particular'] }}]"
                                                                value="{{ $unit !== '-' ? $unit : '' }}"
                                                                placeholder="Enter unit"
                                                                class="form-control form-control-sm short-input text-center">

                                                            @elseif ($isSpecial)
                                                            <input type="text"
                                                                name="special_desc[{{ $group }}][{{ $row['particular'] }}]"
                                                                value="{{ $row['special_desc'] ?? '' }}"
                                                                placeholder="Enter unit"
                                                                class="form-control form-control-sm short-input text-center">

                                                            @else
                                                            {{ $unit }}
                                                            <input type="hidden"
                                                                name="unit[{{ $group }}][{{ $row['particular'] }}]"
                                                                value="{{ $unit }}">
                                                            @endif
                                                        </td>


                                                        {{-- QTY --}}
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
                                                                value="{{ $calc->cbm }}" data-field="qty">

                                                            @else
                                                            {{ number_format($qty, 2) }}
                                                            <input type="hidden"
                                                                name="qty[{{ $group }}][{{ $row['particular'] }}]"
                                                                value="{{ $qty }}" data-field="qty">
                                                            @endif
                                                        </td>

                                                        {{-- RATE --}}
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
                                                                value="{{ $rate }}" data-field="rate">
                                                            @endif
                                                        </td>

                                                        {{-- ROE --}}
                                                        <td>{{ number_format($roe, 4) }}</td>

                                                        {{-- AMOUNT --}}
                                                        <td class="text-end pe-3">
                                                            <input type="text" readonly
                                                                class="form-control-plaintext text-end fw-semibold amount-field"
                                                                value="{{ number_format($amount, 2) }} AED">
                                                        </td>

                                                    </tr>

                                                    @endforeach

                                                    {{-- GROUP TOTAL --}}
                                                    <tr class="table-primary fw-bold group-total-row">
                                                        <td colspan="5" class="text-end">
                                                            TOTAL {{ strtoupper(str_replace('_', ' ', $group)) }}
                                                        </td>
                                                        <td class="text-end pe-3"><span class="group-total">0.00
                                                                AED</span></td>
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

    document.addEventListener("DOMContentLoaded", function () {
        recalcTotals(); // calculate totals instantly on page load
    });

    // When user types qty or rate → recalc that row + totals
    document.addEventListener("input", function (e) {
        const input = e.target;

        if (!input.dataset.field) {
            recalcTotals();
            return;
        }

        const row = input.closest(".calc-row");
        if (!row) return;

        const qty = parseFloat(row.querySelector('[data-field="qty"]')?.value) || 0;
        const rate = parseFloat(row.querySelector('[data-field="rate"]')?.value) || 0;
        let roe = parseFloat(row.dataset.roe) || 1;

        if (roe <= 0) roe = 1;

        const amount = qty * rate * roe;

        row.querySelector(".amount-field").value =
            amount.toLocaleString("en-IN", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + " AED";

        recalcTotals();
    });


    // GLOBAL TOTAL CALCULATION (always run)
    function recalcTotals() {
        document.querySelectorAll(".group-body").forEach(group => {
            let total = 0;

            group.querySelectorAll(".amount-field").forEach(a => {
                const num = parseFloat(a.value.replace(/[^0-9.]/g, "")) || 0;
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