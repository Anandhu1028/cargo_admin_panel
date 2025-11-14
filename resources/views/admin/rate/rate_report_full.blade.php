@extends('admin.layouts.app')
@section('title', 'Full Rate Report (Origin + Destination)')

@section('content')
<div class="page-wrapper">
  <div class="page-content container-fluid">

    <!-- 🔹 Header -->
    <div class="row mb-4">
      <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
          <h4 class="page-title fw-bold text-uppercase">Full Rate Report — {{ strtoupper($calc->customer_name ?? 'N/A') }}</h4>
          <div>
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="#">Admin</a></li>
              <li class="breadcrumb-item active">Full Rate Report</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- 🔹 Customer & Shipment Info -->
    <div class="card shadow-sm border-0 mb-4 rounded-3">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h6 class="fw-bold text-primary text-uppercase mb-2">Customer Details</h6>
            <p class="mb-1"><strong>Name:</strong> {{ strtoupper($calc->customer_name ?? 'N/A') }}</p>
            <p class="mb-1"><strong>From:</strong> {{ strtoupper($calc->from_location) }}</p>
            <p class="mb-1"><strong>To:</strong> {{ strtoupper($calc->to_location) }}</p>
          </div>
          <div class="col-md-6 text-md-end">
            <h6 class="fw-bold text-primary text-uppercase mb-2">Shipment Info</h6>
            <p class="mb-1"><strong>Port:</strong> {{ strtoupper($calc->port ?? '-') }}</p>
            <p class="mb-1"><strong>CBM:</strong> {{ number_format($calc->cbm ?? 0, 2) }}</p>
            <p class="mb-0"><strong>Date:</strong> {{ $calc->created_at->format('d M, Y') }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ===============================
         ORIGIN / DUBAI CHARGES
    ================================ -->
    <h5 class="fw-bold text-primary mb-3 ps-2  border-3 border-primary">
      Dubai / Origin Charges
    </h5>

    @php $originTotal = 0; @endphp
    @foreach ($originGroups as $group => $rows)
      @php
        $groupTotal = $rows->sum('amount');
        $originTotal += $groupTotal;
      @endphp

      <div class="card border-0 shadow-sm mb-3 rounded-3">
        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center rounded-top">
          <h6 class="fw-bold mb-0 text-primary">{{ strtoupper(str_replace('_', ' ', $group)) }}</h6>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover align-middle mb-0 text-center">
            <thead class="table-light">
              <tr>
                <th class="text-start ps-3" style="width:35%">Particulars</th>
                <th>Unit</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>ROE</th>
                <th class="text-end pe-3">Amount (INR)</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($rows as $r)
              <tr>
                <td class="text-start ps-3">{{ ucfirst($r->particular) }}</td>
                <td>{{ $r->unit ?? '-' }}</td>
                <td>{{ number_format($r->qty ?? 0, 2) }}</td>
                <td>{{ $r->rate !== null ? number_format($r->rate, 2) : '-' }}</td>
                <td>{{ number_format($r->roe ?? 1, 4) }}</td>
                <td class="text-end pe-3 fw-semibold">₹ {{ number_format($r->amount ?? 0, 2) }}</td>
              </tr>
              @endforeach
              <tr class="table-primary fw-bold">
                <td colspan="5" class="text-end pe-3">Subtotal ({{ strtoupper(str_replace('_', ' ', $group)) }})</td>
                <td class="text-end pe-3">₹ {{ number_format($groupTotal, 2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    @endforeach

    <!-- ===============================
         DESTINATION / KOCHI LOCALS
    ================================ -->
    <h5 class="fw-bold text-primary mt-5 mb-3 ps-2  border-3 border-primary">
      Destination / Kochi Locals
    </h5>

    @php $destTotal = 0; @endphp
    @foreach ($destinationGroups as $group => $rows)
      @php
        $groupTotal = $rows->sum('amount');
        $destTotal += $groupTotal;
      @endphp

      <div class="card border-0 shadow-sm mb-3 rounded-3">
        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center rounded-top">
          <h6 class="fw-bold mb-0 text-primary">{{ strtoupper(str_replace('_', ' ', $group)) }}</h6>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover align-middle mb-0 text-center">
            <thead class="table-light">
              <tr>
                <th class="text-start ps-3" style="width:35%">Particulars</th>
                <th>Unit</th>
                <th>Qty</th>
                <th>Rate → INR</th>
                <th>ROE</th>
                <th class="text-end pe-3">Amount → AED</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($rows as $r)
              <tr>
                <td class="text-start ps-3">{{ ucfirst($r->particular) }}</td>
                <td>{{ $r->unit ?? '-' }}</td>
                <td>{{ number_format($r->qty ?? 0, 2) }}</td>
                <td>{{ $r->rate !== null ? number_format($r->rate, 2) . ' INR' : '-' }}</td>
                <td>{{ number_format($r->roe ?? 1, 4) }}</td>
                <td class="text-end pe-3 fw-semibold">{{ number_format($r->amount ?? 0, 2) }} AED</td>
              </tr>
              @endforeach
              <tr class="table-primary fw-bold">
                <td colspan="5" class="text-end pe-3">Subtotal ({{ strtoupper(str_replace('_', ' ', $group)) }})</td>
                <td class="text-end pe-3">₹ {{ number_format($groupTotal, 2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    @endforeach

    <!-- ===============================
         GRAND TOTAL
    ================================ -->
    @php $grandTotal = $originTotal + $destTotal; @endphp
    <div class="card border-0 shadow-sm mt-4 rounded-3">
      <div class="card-body text-end">
        <h5 class="fw-semibold mb-2">
          Total Origin Charges:
          <span class="text-dark">₹ {{ number_format($originTotal, 2) }} INR</span>
        </h5>

        <h5 class="fw-semibold mb-2">
          Total Destination Charges:
          <span class="text-dark">{{ number_format($destTotal, 2) }} AED</span>
        </h5>

        <hr class="my-2">

        <h3 class="fw-bold text-primary">Grand Total: {{ number_format($grandTotal, 2) }} AED</h3>
      </div>
    </div>

    <!-- 🔹 Footer Buttons -->
    <div class="mt-5 d-flex justify-content-between align-items-center">
      <a href="{{ route('rate.step3', $calc->id) }}" class="btn"
         style="background: linear-gradient(90deg, #2b2f3a, #3a404e 60%, #454d60); color: #fff; border: none; padding: 8px 22px; font-weight: 600; border-radius: 8px;">
         <i class="fas fa-arrow-left me-2" style="color:#00c6ff;"></i> Previous
      </a>

     <button onclick="window.print()" class="btn"
  style="
    background: linear-gradient(90deg, #0f172a 0%, #1e293b 50%, #334155 100%);
    color: #fff;
    border: none;
    padding: 10px 26px;
    font-weight: 600;
    border-radius: 10px;
    letter-spacing: 0.4px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
  "
  onmouseover="this.style.background='linear-gradient(90deg,#1e293b 0%,#334155 50%,#475569 100%)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 18px rgba(0,0,0,0.35)';"
  onmouseout="this.style.background='linear-gradient(90deg,#0f172a 0%,#1e293b 50%,#334155 100%)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(0,0,0,0.25)';"
  onmousedown="this.style.transform='scale(0.97)';"
  onmouseup="this.style.transform='translateY(-2px)';">
  <i class="fas fa-print me-2" style="color:#00c6ff; font-size:15px;"></i>
  Print / Export PDF
</button>

    </div>

  </div>
</div>

<!-- ===============================
     STYLES
=============================== -->
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
  font-weight: 700;          /* bolder than before */
  color: #333;
  font-size: 12.5px;         /* smaller header text */
  text-transform: uppercase; /* optional — adds a neat A4-report style */
  letter-spacing: 0.3px;     /* improves small-text readability */
  padding-top: 6px !important;
  padding-bottom: 6px !important;
}

.table-hover tbody tr:hover {
  background-color: #f9fbfd !important;
}
/*  Alignment fix */
.table th.text-start, .table td.text-start { text-align: left !important; }
.table th.text-end, .table td.text-end { text-align: right !important; }
.card-header {
  background-color: #f8f9fa !important;
  border-bottom: 1px solid #dee2e6;
}
h5.text-primary {
  font-size: 1.1rem;
}
@media print {
  .btn, .breadcrumb, .page-title-box { display: none !important; }
  body { background: #fff; }
  .card { page-break-inside: avoid; }
  .page-wrapper { padding: 0; margin: 0; }
}
</style>
@endsection
