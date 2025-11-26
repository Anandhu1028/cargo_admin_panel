@extends('admin.layouts.app')
@section('title', 'Full Rate Report (Origin + Destination)')

@section('content')

<style>
body {
  background: #f4f6f8;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.table td, .table th {
  vertical-align: middle !important;
  font-size: 13.5px;
  padding: 8px 10px !important;
}
.table th {
  background-color: #f6f8fb !important;
  font-weight: 700;
  color: #333;
  font-size: 12.5px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}
.table-hover tbody tr:hover {
  background-color: #f9fbfd !important;
}
.table th.text-start, .table td.text-start { text-align: left !important; }
.table th.text-end, .table td.text-end { text-align: right !important; }
.card-header {
  background-color: #f8f9fa !important;
  border-bottom: 1px solid #dee2e6;
}
@media print {
  .btn, .breadcrumb, .page-title-box { display: none !important; }
  body { background: #fff; }
  .card { page-break-inside: avoid; }
  .page-wrapper { padding: 0; margin: 0; }
}
</style>

<div class="page-wrapper">
  <div class="page-content container-fluid">

    <!-- HEADER -->
    <div class="row mb-4">
      <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
          <h4 class="page-title fw-bold text-uppercase">
              Full Rate Report — {{ strtoupper($calc->customer_name ?? 'N/A') }}
          </h4>
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="#">Admin</a></li>
            <li class="breadcrumb-item active">Full Rate Report</li>
          </ol>
        </div>
      </div>
    </div>

    <!-- CUSTOMER INFO -->
    <div class="card shadow-sm border-0 mb-4 rounded-3">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h6 class="fw-bold text-primary">Customer Details</h6>
            <p><strong>Name:</strong> {{ strtoupper($calc->customer_name) }}</p>
            <p><strong>From:</strong> {{ strtoupper($calc->from_location) }}</p>
            <p><strong>To:</strong> {{ strtoupper($calc->to_location) }}</p>
          </div>
          <div class="col-md-6 text-end">
            <h6 class="fw-bold text-primary">Shipment Info</h6>
            <p><strong>Port:</strong> {{ strtoupper($calc->port ?? '-') }}</p>
            <p><strong>CBM:</strong> {{ number_format($calc->cbm, 2) }}</p>
            <p><strong>Date:</strong> {{ $calc->created_at->format('d M, Y') }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================
         ORIGIN CHARGES
    ============================ -->
    @php
        $originTotalCharges = 0;
        $ratio = ($calc->cbm ?? 0) / 50;
    @endphp

    <h5 class="fw-bold text-primary mb-3 ps-2">Dubai / Origin Charges</h5>

    @foreach ($originGroups as $group => $rows)

      @php $groupCharges = 0; @endphp

      <div class="card shadow-sm border-0 mb-3 rounded-3">
        <div class="card-header py-2">
          <h6 class="fw-bold text-primary mb-0">{{ strtoupper(str_replace('_',' ',$group)) }}</h6>
        </div>

        <div class="card-body p-0">
          <table class="table table-hover mb-0 text-center">
            <thead class="table-light">
              <tr>
                <th class="text-start ps-3">Particulars</th>
                <th>Unit</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>ROE</th>
                <th class="text-end pe-3">Amount (AED)</th>
                <th class="text-end pe-3">Total Charges</th>
              </tr>
            </thead>
            <tbody>

              @foreach ($rows as $r)
                  @php
                      $qty = $r->qty ?? 0;
                      $rate = $r->rate ?? 0;
                      $roe = $r->roe ?: 1;

                      $amount = ($qty * $rate) / $roe;
                      $charge = $amount * $ratio;

                      $groupCharges += $charge;
                  @endphp

                  <tr>
                    <td class="text-start ps-3">{{ ucfirst($r->particular) }}</td>
                    <td>{{ $r->unit ?? '-' }}</td>
                    <td>{{ number_format($qty,2) }}</td>
                    <td>{{ number_format($rate,2) }}</td>
                    <td>{{ number_format($roe,4) }}</td>
                    <td class="text-end pe-3">{{ number_format($amount,2) }} AED</td>
                    <td class="text-end pe-3 fw-bold">{{ number_format($charge,2) }} AED</td>
                  </tr>
              @endforeach

              <!-- FIXED: SUBTOTAL LEFT + VALUE RIGHT -->
              <tr class="table-primary fw-bold">
                <td colspan="6" class="text-start ps-3 fw-bold">
                    Sub Total ({{ strtoupper(str_replace('_', ' ', $group)) }})
                </td>
                <td class="text-end pe-3 fw-bold">
                    {{ number_format($groupCharges,2) }} AED
                </td>
              </tr>

            </tbody>
          </table>
        </div>
      </div>

      @php $originTotalCharges += $groupCharges; @endphp
    @endforeach

    <!-- ============================
         DESTINATION CHARGES
    ============================ -->
    @php $destTotalCharges = 0; @endphp

    <h5 class="fw-bold text-primary mt-4 mb-3 ps-2">Destination / Kochi Locals</h5>

    @foreach ($destinationGroups as $group => $rows)

      @php $groupCharges = 0; @endphp

      <div class="card shadow-sm border-0 mb-3 rounded-3">
        <div class="card-header py-2">
          <h6 class="fw-bold text-primary mb-0">{{ strtoupper(str_replace('_',' ',$group)) }}</h6>
        </div>

        <div class="card-body p-0">
          <table class="table table-hover mb-0 text-center">
            <thead class="table-light">
              <tr>
                <th class="text-start ps-3">Particulars</th>
                <th>Unit</th>
                <th>Qty</th>
                <th>Rate → INR</th>
                <th>ROE</th>
                <th class="text-end pe-3">Amount → AED</th>
                <th class="text-end pe-3">Total Charges</th>
              </tr>
            </thead>

            <tbody>
              @foreach ($rows as $r)
                @php
                    $qty = $r->qty ?? 0;
                    $rate = $r->rate ?? 0;
                    $roe = $r->roe ?: 1;

                    $amount = ($qty * $rate) / $roe;
                    $charge = $amount * $ratio;

                    $groupCharges += $charge;
                @endphp

                <tr>
                  <td class="text-start ps-3">{{ ucfirst($r->particular) }}</td>
                  <td>{{ $r->unit ?? '-' }}</td>
                  <td>{{ number_format($qty,2) }}</td>
                  <td>{{ number_format($rate,2) }} INR</td>
                  <td>{{ number_format($roe,4) }}</td>
                  <td class="text-end pe-3">{{ number_format($amount,2) }} AED</td>
                  <td class="text-end pe-3 fw-bold">{{ number_format($charge,2) }} AED</td>
                </tr>

              @endforeach

              <tr class="table-primary fw-bold">
                <td colspan="6" class="text-start ps-3 fw-bold">
                    Sub Total ({{ strtoupper(str_replace('_', ' ', $group)) }})
                </td>
                <td class="text-end pe-3 fw-bold">
                    {{ number_format($groupCharges,2) }} AED
                </td>
              </tr>

            </tbody>
          </table>
        </div>
      </div>

      @php $destTotalCharges += $groupCharges; @endphp
    @endforeach

    <!-- ============================
         GRAND TOTAL
    ============================ -->
    @php
        $grandCharges = $originTotalCharges + $destTotalCharges;
    @endphp

    <div class="card shadow-sm border-0 rounded-3 mt-4">
      <div class="card-body text-end">

        <h5 class="fw-semibold">
          Total Origin Charges:
          <span>{{ number_format($originTotalCharges,2) }} AED</span>
        </h5>

        <h5 class="fw-semibold">
          Total Destination Charges:
          <span>{{ number_format($destTotalCharges,2) }} AED</span>
        </h5>

        <hr>

        <h3 class="fw-bold text-primary">
          Grand Total: {{ number_format($grandCharges,2) }} AED
        </h3>

      </div>
    </div>

    <!-- ============================
         FOOTER BUTTONS (Restored)
    ============================ -->
    <div class="mt-5 d-flex justify-content-between align-items-center">

      <a href="{{ route('rate.step3', $calc->id) }}" class="btn"
         style="background: linear-gradient(90deg, #2b2f3a, #3a404e 60%, #454d60); color: #fff; padding: 8px 22px; font-weight: 600; border-radius: 8px;">
         <i class="fas fa-arrow-left me-2" style="color:#00c6ff;"></i> Previous
      </a>

      <button onclick="window.print()" class="btn"
        style="
          background: linear-gradient(90deg, #0f172a 0%, #1e293b 50%, #334155 100%);
          color: #fff;
          padding: 10px 26px;
          font-weight: 600;
          border-radius: 10px;
          letter-spacing: 0.4px;
          box-shadow: 0 4px 14px rgba(0,0,0,0.25);
          transition: all 0.3s ease;">
        <i class="fas fa-print me-2" style="color:#00c6ff; font-size:15px;"></i>
        Print / Export PDF
      </button>

    </div>

  </div>
</div>

@endsection
