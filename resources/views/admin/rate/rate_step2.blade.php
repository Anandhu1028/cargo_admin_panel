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

                                                $unit = $row['unit'] ?? '-';
                                                $rate = $row['rate'] ?? 0;
                                                $roe = $row['roe'] ?? 1;
                                                $qty = $row['qty'] ?? ($calc->cbm ?? 1);
                                                $amount = $row['amount'] ?? round($rate * $qty * $roe, 2);
                                                @endphp

                                                {{-- Skip irrelevant collection rows --}}
                                                @if (($calc->cbm <= 3 && str_contains($particular, '3 tone' )) ||
                                                    ($calc->cbm > 3 && str_contains($particular, 'van up to 3')))
                                                    @continue
                                                    @endif

                                                    <tr>
                                                        <td class="text-start ps-3">{{ $row['particular'] }}</td>

                                                        {{-- 🔸 UNIT COLUMN --}}
                                                        <td>
                                                            @if ($isPacking)
                                                            CBM

                                                            @elseif ($isOther)
                                                            <input type="text"
                                                                name="other_unit[{{ $group }}][{{ $row['particular'] }}]"
                                                                value="{{ old('other_unit.'. $group .'.'. $row['particular'], $unit !== '-' ? $unit : '') }}"
                                                                placeholder="Unit"
                                                                class="form-control form-control-sm short-input text-center other-unit-input"
                                                                data-roe="{{ $roe }}">
                                                            @elseif ($isSpecial)
                                                            <input type="text"
                                                                name="special_desc[{{ $group }}][{{ $row['particular'] }}]"
                                                                value="{{ old('special_desc.'. $group .'.'. $row['particular'], $row['special_desc'] ?? '') }}"
                                                                placeholder="unit"
                                                                class="form-control form-control-sm short-input text-center special-desc">
                                                            @else
                                                            {{ $unit }}
                                                            <input type="hidden"
                                                                name="unit[{{ $group }}][{{ $row['particular'] }}]"
                                                                value="{{ $unit }}">
                                                            @endif
                                                        </td>

                                                        {{-- 🔸 QTY COLUMN --}}
                                                        <td>
                                                            @if ($isLabour)
                                                            <input type="number" step="1" min="0"
                                                                name="labour_qty[{{ $group }}][{{ $row['particular'] }}]"
                                                                value="{{ old('labour_qty.'. $group .'.'. $row['particular'], $qty) }}"
                                                                class="form-control form-control-sm short-input text-end labour-input"
                                                                data-rate="{{ $rate }}" data-roe="{{ $roe }}">

                                                            @elseif ($isOther)
                                                            <input type="number" step="0.01" min="0"
                                                                name="other_qty[{{ $group }}][{{ $row['particular'] }}]"
                                                                value="{{ old('other_qty.'. $group .'.'. $row['particular'], $qty) }}" placeholder="Qty"
                                                                class="form-control form-control-sm short-input text-end other-qty-input"
                                                                data-roe="{{ $roe }}">

                                                            @elseif ($isStorage)
                                                            {{ number_format($calc->cbm, 2) }}
                                                            <input type="hidden"
                                                                name="storage_qty[{{ $group }}][{{ $row['particular'] }}]"
                                                                value="{{ $calc->cbm }}">

                                                            @else
                                                            {{ number_format($qty, 2) }}
                                                            <input type="hidden"
                                                                name="qty[{{ $group }}][{{ $row['particular'] }}]"
                                                                value="{{ $qty }}">
                                                            @endif
                                                        </td>


                                                        {{-- 🔸 RATE COLUMN --}}
                                                        <td>
                                                            @if ($isOther)
                                                            <input type="number" step="0.01" min="0"
                                                                name="other_rate[{{ $group }}][{{ $row['particular'] }}]"
                                                                value="{{ old('other_rate.'. $group .'.'. $row['particular'], $rate) }}" placeholder="Rate"
                                                                class="form-control form-control-sm short-input text-end other-rate-input"
                                                                data-roe="{{ $roe }}">
                                                            @elseif ($isSpecial)
                                                            <input type="number" step="0.01" min="0"
                                                                name="special_rate[{{ $group }}][{{ $row['particular'] }}]"
                                                                value="{{ old('special_rate.'. $group .'.'. $row['particular'], $rate) }}"
                                                                class="form-control form-control-sm short-input text-end special-rate-input"
                                                                data-cbm="{{ $calc->cbm }}" data-roe="{{ $roe }}">
                                                            @else
                                                            {{ number_format($rate, 2) }}
                                                            <input type="hidden"
                                                                name="rate[{{ $group }}][{{ $row['particular'] }}]"
                                                                value="{{ $rate }}">
                                                            @endif
                                                        </td>

                                                        {{-- 🔸 ROE --}}
                                                        <td>{{ number_format($roe, 4) }}</td>

                                                        {{-- 🔸 AMOUNT --}}
                                                        <td class="text-end pe-3">
                                                            @if ($isLabour)
                                                            <input type="text" readonly
                                                                class="form-control-plaintext text-end fw-semibold labour-amount"
                                                                value="{{ number_format($amount, 2) }}">
                                                            @elseif ($isPacking)
                                                            <input type="text" readonly
                                                                class="form-control-plaintext text-end fw-semibold packing-amount-display"
                                                                value="{{ number_format($amount, 2) }}">
                                                            @elseif ($isOther)
                                                            <input type="text" readonly
                                                                class="form-control-plaintext text-end fw-semibold other-amount-display"
                                                                value="{{ number_format($amount, 2) }}">
                                                            @elseif ($isSpecial)
                                                            <input type="text" readonly
                                                                class="form-control-plaintext text-end fw-semibold special-amount-display"
                                                                value="{{ number_format($amount, 2) }}">
                                                            @else
                                                            <input type="text" readonly
                                                                class="form-control-plaintext text-end fw-semibold static-amount"
                                                                value="{{ number_format($amount, 2) }}">
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach

                                                    {{-- TOTAL ROW --}}
                                                    <tr class="table-primary fw-bold group-total-row">
                                                        <td colspan="5" class="text-end">
                                                            TOTAL {{ strtoupper(str_replace('_', ' ', $group)) }}
                                                        </td>
                                                        <td class="text-end pe-3"><span class="group-total">0.00</span>
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

    document.querySelectorAll('.storage-rate-input').forEach(inp => {
        inp.addEventListener('input', function () {
            const row = this.closest('tr');
            const qty = toNumber(row.querySelector('input[name*="storage_qty"]')?.value || 0);
            const rate = toNumber(this.value);
            const roe = toNumber(this.dataset.roe || 1);

            row.querySelector('.storage-amount-display').value =
                formatNumber(qty * rate * roe);

            updateAllGroupTotals();
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const formatNumber = n => Number(n || 0).toLocaleString("en-IN", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const toNumber = s => parseFloat((s || "0").replace(/[^0-9.\-]/g, "")) || 0;

        const updateAllGroupTotals = () => {
            document.querySelectorAll('.group-body').forEach(g => {
                let total = 0;
                g.querySelectorAll('.static-amount, .labour-amount, .packing-amount-display, .other-amount-display, .special-amount-display')
                    .forEach(el => total += toNumber(el.value || el.textContent));
                const totalEl = g.querySelector('.group-total');
                if (totalEl) totalEl.textContent = formatNumber(total);
            });
        };

        updateAllGroupTotals();

        document.querySelectorAll('.labour-input').forEach(inp => inp.addEventListener('input', function () {
            const rate = toNumber(this.dataset.rate), roe = toNumber(this.dataset.roe), qty = toNumber(this.value);
            this.closest('tr').querySelector('.labour-amount').value = formatNumber(rate * qty * roe);
            updateAllGroupTotals();
        }));

        document.querySelectorAll('.other-qty-input, .other-rate-input').forEach(inp => {
            inp.addEventListener('input', function () {
                const row = this.closest('tr');
                const qty = toNumber(row.querySelector('.other-qty-input')?.value || 0);
                const rate = toNumber(row.querySelector('.other-rate-input')?.value || 0);
                const roe = toNumber(row.querySelector('.other-rate-input')?.dataset.roe || 1);
                row.querySelector('.other-amount-display').value = formatNumber(qty * rate * roe);
                updateAllGroupTotals();
            });
        });

        document.querySelectorAll('.special-rate-input').forEach(inp => inp.addEventListener('input', function () {
            const cbm = toNumber(this.dataset.cbm), roe = toNumber(this.dataset.roe), rate = toNumber(this.value);
            this.closest('tr').querySelector('.special-amount-display').value = formatNumber(cbm * rate * roe);
            updateAllGroupTotals();
        }));
    });
</script>
@endpush