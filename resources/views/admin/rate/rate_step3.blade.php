@extends('admin.layouts.app')
@section('title', 'Rate Calculator - Step 3 (Destination / Kochin Locals)')

@section('content')
<div class="page-wrapper">
  <div class="page-content">
    <div class="container-fluid">

      <!-- 🔹 Header -->
      <div class="row">
        <div class="col-sm-12">
          <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">STEP 3 - 3 </h4>
            <div>
              <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                <li class="breadcrumb-item active">Destination / Kochin Locals</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 🔹 Main Card -->
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="fw-bold mb-3">DESTINATION / KOCHIN LOCALS CALCULATION</h5>
          <p class="small text-muted mb-4">
            Destination Port: <strong>{{ strtoupper($calc->port) }}</strong> &nbsp;&nbsp; |
            &nbsp;&nbsp; Customer: <strong>{{ $calc->customer_name }}</strong> &nbsp;&nbsp; |
            &nbsp;&nbsp; CBM: <strong id="cbm-val">{{ number_format($calc->cbm ?? 0, 2) }}</strong>
          </p>

          <form method="POST" action="{{ route('rate.step3.store', $calc->id) }}">
            @csrf

            @foreach ($rules as $group => $items)
            @php $groupKey = strtolower($group); @endphp

            <div class="mb-4">
              <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                  <h6 class="fw-bold text-primary mb-0">{{ strtoupper(str_replace('_', ' ', $group)) }}</h6>
                </div>

                <div class="card-body p-0 mt-1">
                  <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                      <thead class="table-light text-center">
                        <tr>
                          <th>Particulars</th>
                          <th>Unit</th>
                          <th>Qty</th>
                          <th>Rate → INR</th>

                          <th>ROE</th>
                          <th>Amount → AED</th>
                        </tr>
                      </thead>

                      <tbody class="group-body text-center" data-group="{{ $groupKey }}">
                        @foreach ($items as $row)
                        @php
                        $particular = strtolower($row['particular'] ?? '');
                        $unit = $row['unit'] ?? '-';
                        $rate = $row['rate'] ?? 0;
                        $roe = $row['roe'] ?? 1;
                        $cbm = $calc->cbm ?? 1;

                        $isDetention = str_contains($particular, 'detention');
                        $isStorage = str_contains($particular, 'storage');
                        $isDuty = str_contains($particular, 'duty');
                        $isOther = str_contains($particular, 'other charges');
                        $isTransport = str_contains($particular, 'transportation basic');
                        $isCBMBased = str_contains($particular, 'offloading') || str_contains($particular, 'unboxing')
                        || str_contains($particular, 'fitting') || str_contains($particular, 'fixing');

                        $qty = $isCBMBased ? $cbm : 1;
                        $amount = round(($qty * $rate) / ($roe ?: 1), 2);
                        @endphp

                        <tr>
                          <!-- PARTICULAR -->
                          <td class="text-start ps-3">
                            {{ ucfirst($row['particular']) }}
                            <!-- ✅ Hidden input ensures 'particular' is saved -->
                            <input type="hidden" name="rules[{{ $group }}][{{ $loop->index }}][particular]"
                              value="{{ $row['particular'] }}">
                          </td>

                          <!-- UNIT -->
                          <td>
                            @if ($isOther || $isTransport)
                            <input type="text" name="rules[{{ $group }}][{{ $loop->index }}][unit]"
                              value="{{ $unit !== '-' ? $unit : '' }}" placeholder="Unit"
                              class="form-control form-control-sm short-input text-center">
                            @else
                            <input type="text" readonly
                              class="form-control form-control-sm short-input text-center bg-transparent border-0"
                              value="{{ $unit }}">
                            <input type="hidden" name="rules[{{ $group }}][{{ $loop->index }}][unit]"
                              value="{{ $unit }}">
                            @endif
                          </td>

                          <!-- QTY -->
                          <td>
                            <input type="text" readonly
                              class="form-control form-control-sm short-input text-end bg-transparent border-0"
                              value="{{ number_format($qty, 2) }}">
                            <input type="hidden" name="rules[{{ $group }}][{{ $loop->index }}][qty]" value="{{ $qty }}">
                          </td>

                          <!-- RATE -->
                          <td>
                            @if ($isDetention || $isStorage || $isDuty || $isOther)
                            <input type="number" step="0.01" min="0"
                              name="rules[{{ $group }}][{{ $loop->index }}][rate]"
                              value="{{ number_format($rate, 2, '.', '') }}"
                              class="form-control form-control-sm short-input text-end rate-input">
                            @else
                            <input type="text" readonly
                              class="form-control form-control-sm short-input text-end bg-transparent border-0"
                              value="{{ number_format($rate, 2) }}">
                            <input type="hidden" name="rules[{{ $group }}][{{ $loop->index }}][rate]"
                              value="{{ number_format($rate, 2, '.', '') }}">
                            @endif
                          </td>

                          <!-- ROE -->
                          <td>
                            <input type="text" readonly
                              class="form-control form-control-sm short-input text-end bg-transparent border-0"
                              value="{{ number_format($roe, 4) }}">
                            <input type="hidden" name="rules[{{ $group }}][{{ $loop->index }}][roe]" value="{{ $roe }}">
                          </td>

                          <!-- AMOUNT -->
                          <td class="text-end pe-3">
                            <input type="text" readonly
                              class="form-control form-control-sm short-input text-end bg-transparent border-0 fw-semibold amount-display"
                              value="{{ number_format($amount, 2) }}">
                          </td>
                        </tr>
                        @endforeach

                        <tr class="table-primary fw-bold group-total-row">
                          <td colspan="5" class="text-end">
                            TOTAL {{ strtoupper(str_replace('_',' ',$group)) }}
                          </td>
                          <td class="text-end pe-3"><span class="group-total">0.00</span></td>
                        </tr>
                      </tbody>






                    </table>
                  </div>
                </div>
              </div>
            </div>
            @endforeach

            <!-- Buttons -->
            <div class="mt-4 d-flex justify-content-between">

              {{-- 🔹 Back Button (Styled to match Save) --}}
              <a href="{{ route('rate.step2', $calc->id) }}" class="btn" style="
            background: linear-gradient(90deg, #2b2f3a 0%, #3a404e 60%, #454d60 100%);
            color: #fff;
            border: none;
            padding: 9px 22px;
            font-weight: 600;
            border-radius: 8px;
            letter-spacing: 0.4px;
            box-shadow: 0 4px 12px rgba(20,24,36,0.4);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        " onmouseover="this.style.background='linear-gradient(90deg,#3a404e 0%,#464e62 60%,#505b70 100%)'; this.style.transform='translateY(-2px)';"
                onmouseout="this.style.background='linear-gradient(90deg,#2b2f3a 0%,#3a404e 60%,#454d60 100%)'; this.style.transform='translateY(0)';"
                onmousedown="this.style.transform='scale(0.97)';" onmouseup="this.style.transform='translateY(-2px)';">
                <i class="fas fa-arrow-left me-2" style="color:#00c6ff;"></i>
                Previous
              </a>

              {{-- 🔹 Save Button (Styled) --}}
              <button type="submit" class="btn" style="
            background: linear-gradient(90deg, #141824 0%, #1e2736 60%, #253043 100%);
            color: #fff;
            border: none;
            padding: 9px 22px;
            font-weight: 600;
            border-radius: 8px;
            letter-spacing: 0.4px;
            box-shadow: 0 4px 12px rgba(20, 24, 36, 0.4);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        " onmouseover="this.style.background='linear-gradient(90deg, #1e2736 0%, #2b374f 60%, #34445e 100%)'; this.style.transform='translateY(-2px)';"
                onmouseout="this.style.background='linear-gradient(90deg, #141824 0%, #1e2736 60%, #253043 100%)'; this.style.transform='translateY(0)';"
                onmousedown="this.style.transform='scale(0.97)';" onmouseup="this.style.transform='translateY(-2px)';">
                Rate Calculate
                <i class="fas fa-calculator ms-2" style="color:#00c6ff;"></i>
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
    max-width: 100px;
    display: inline-block;
    text-align: right;
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

  .table-hover tbody tr:hover {
    background-color: #f9fbfd !important;
  }

  .form-control[readonly].bg-transparent {
    background: transparent !important;
    box-shadow: none;
  }

  .card-header {
    border-bottom: 1px solid #eee;
  }
</style>
<style>
  .table {
    width: 100%;
    border-collapse: collapse;
  }

  .table th,
  .table td {
    vertical-align: middle !important;
    text-align: center !important;
    padding: 10px 8px !important;
  }

  /* keep left/right alignment only for edge columns */
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

  /* consistent input sizing/alignment */
  .short-input {
    max-width: 90px;
    text-align: right;
    margin: 0 auto;
  }

  /* fix for readonly alignment */
  .form-control[readonly].bg-transparent {
    background: transparent !important;
    box-shadow: none !important;
    text-align: center !important;
    padding: 6px 0 !important;
  }

  /* table total highlight */
  .group-total-row td {
    background: #f3f7ff;
    font-weight: 600;
  }

  /* subtle hover */
  .table-hover tbody tr:hover {
    background-color: #f9fbfd !important;
  }

  /* table header style */
  .table-light th {
    background-color: #f6f8fb !important;
    font-weight: 600;
    color: #333;
  }
</style>

@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const cbm = parseFloat(document.getElementById("cbm-val").textContent) || 0;
    const format = n => Number(n || 0).toLocaleString("en-IN", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const num = s => parseFloat((s || "0").replace(/[^0-9.\-]/g, "")) || 0;

    // 🔹 1. Fetch ROE from settings or live API
    let liveROE = 0.0439; // fallback (1 INR = 0.0439 AED)
    const port = "{{ strtoupper($calc->port ?? 'KOCHI') }}";

    // First, try to get ROE from database settings
    fetch(`/admin/api/roe/${port}`)
      .then(r => {
        if (r.ok) return r.json();
        throw new Error('ROE not in database, using live API');
      })
      .then(data => {
        if (data && data.roe_value) {
          liveROE = parseFloat(data.roe_value);
          console.log(`✅ ROE loaded from database: 1 INR = ${liveROE.toFixed(4)} AED`);
          applyROE(liveROE);
        }
      })
      .catch(() => {
        // If not in database, fetch live rate
        console.log('📡 Fetching live ROE from API...');
        fetch("https://api.exchangerate.host/convert?from=INR&to=AED")
          .then(r => r.json())
          .then(data => {
            if (data && data.info && data.info.rate) {
              liveROE = parseFloat(data.info.rate);
            }
          })
          .catch(() => console.warn("⚠️ Using fallback ROE 0.0439"))
          .finally(() => {
            applyROE(liveROE);
          });
      });

    // 🔹 2. Apply ROE
    function applyROE(roe) {
      document.querySelectorAll("input[name*='[roe]']").forEach(inp => {
        inp.value = roe.toFixed(4);
        const td = inp.closest("td");
        const display = td.querySelector("input[readonly].bg-transparent");
        if (display) display.value = roe.toFixed(4);
      });

      document.querySelectorAll(".group-body tr").forEach(row => recalc(row, roe));
      updateTotals();
      console.log(`✅ ROE Applied: 1 INR = ${roe.toFixed(4)} AED`);
    }

    // 🔹 3. Recalculate per row (rate INR → amount AED)
    function recalc(row, roe) {
      if (row.classList.contains("group-total-row")) return;

      const qty = num(row.querySelector('input[name*="[qty]"]')?.value || 1);
      const rate = num(row.querySelector(".rate-input")?.value || row.querySelector('input[name*="[rate]"]')?.value || 0);
      // amount in AED = (qty * rate in INR) / roe (INR per AED)
      const amount = (qty * rate) / (roe || 1);

      // ✅ Label only the Rate column with INR
      const rateCell = row.children[3]; // the 4th column = Rate
      const rateDisplay = rateCell.querySelector("input[readonly].bg-transparent");
      if (rateDisplay && !rateDisplay.value.includes("INR")) {
        rateDisplay.value = `₹ ${format(rate)} INR`;
      }

      // ✅ Amount column in AED
      const amountField = row.querySelector(".amount-display");
      if (amountField) {
        amountField.value = `${format(amount)} AED`;
      }
    }

    // 🔹 4. Calculate totals (AED)
    function updateTotals() {
      document.querySelectorAll("tbody.group-body").forEach(group => {
        let total = 0;
        group.querySelectorAll("tr:not(.group-total-row) .amount-display").forEach(a => {
          total += num(a.value || a.textContent);
        });
        const totalCell = group.querySelector(".group-total");
        if (totalCell) {
          totalCell.textContent = `${format(total)} AED`;
        }
      });
    }

    // 🔹 5. Live update on input
    document.querySelectorAll(".rate-input").forEach(inp => {
      inp.addEventListener("input", () => {
        recalc(inp.closest("tr"), liveROE);
        updateTotals();
      });
    });
  });
</script>
@endpush
 