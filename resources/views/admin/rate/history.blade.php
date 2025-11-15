@extends('admin.layouts.app')
@section('title', 'Rate Calculation History')

@section('content')
<div class="page-wrapper">
    <div class="page-content container-fluid">

        <!-- PAGE TITLE -->
        <div class="row mb-3">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Rate Calculation History</h4>

                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#">Admin</a></li>
                        <li class="breadcrumb-item active">History</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- HISTORY CARD -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="fw-bold mb-0">Saved Rate Reports</h5>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold">Customer</th>
                                <th class="fw-semibold">Date</th>
                                <th style="width:180px;" class="fw-semibold text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($reports as $report)
                            @php $d = $report->report_data; @endphp

                            <tr>
                                <td class="fw-bold">{{ $report->customer_name }}</td>
                                <td>{{ $report->created_at->format('d M Y  H:i') }}</td>

                                <td class="text-center">

                                    <div class="d-inline-flex gap-2">

                                        <!-- VIEW BUTTON -->
                                        <button type="button"
                                            style="
                                                display: inline-flex;
                                                align-items: center;
                                                gap: 6px;
                                                white-space: nowrap;
                                                background: linear-gradient(90deg,#141824,#1e2736 60%,#253043);
                                                color:#fff; border:none;
                                                padding:8px 18px;
                                                font-weight:600;
                                                border-radius:8px;
                                                letter-spacing:0.4px;
                                                box-shadow:0 4px 12px rgba(20,24,36,0.4);
                                                transition:all .3s ease;
                                            "
                                            onmouseover="this.style.background='linear-gradient(90deg,#1e2736,#2b374f 60%,#34445e)'; this.style.transform='translateY(-2px)'"
                                            onmouseout="this.style.background='linear-gradient(90deg,#141824,#1e2736 60%,#253043)'; this.style.transform='translateY(0)'"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#report-{{ $report->id }}">
                                            <i class="fas fa-eye" style="color:#00c6ff;"></i>
                                            View
                                        </button>

                                        <!-- DELETE BUTTON -->
                                        <form action="{{ route('rate.history.delete', $report->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf @method('DELETE')

                                            <button type="submit"
                                                onclick="return confirm('Delete report?')"
                                                style="
                                                    display:inline-flex;
                                                    align-items:center;
                                                    gap:6px;
                                                    white-space:nowrap;
                                                    background:linear-gradient(90deg,#8b1e1e,#a02222 50%,#b32c2c);
                                                    color:#fff; border:none;
                                                    padding:8px 18px;
                                                    font-weight:600;
                                                    border-radius:8px;
                                                    letter-spacing:0.4px;
                                                    box-shadow:0 4px 10px rgba(176,32,32,0.35);
                                                    transition:all .3s ease;
                                                "
                                                onmouseover="this.style.background='linear-gradient(90deg,#a82828,#bb3333 50%,#c63d3d)'; this.style.transform='translateY(-2px)'"
                                                onmouseout="this.style.background='linear-gradient(90deg,#8b1e1e,#a02222 50%,#b32c2c)'; this.style.transform='translateY(0)'">
                                                <i class="fas fa-trash" style="color:#ffd6d6;"></i>
                                                Delete
                                            </button>
                                        </form>

                                    </div>

                                </td>
                            </tr>

                            <!-- COLLAPSE DETAILS -->
                            <tr class="table-light">
                                <td colspan="3" class="p-0">
                                    <div class="collapse p-4" id="report-{{ $report->id }}">
                                        @if(!$d || !is_array($d))
                                            <div class="alert alert-warning">No valid snapshot stored for this report.</div>
                                        @else
                                            @php $info = $d['customer_info'] ?? []; @endphp

                                            <!-- CUSTOMER INFO -->
                                            <h5 class="fw-bold border-bottom pb-2">Customer Information</h5>

                                            <table class="table table-sm table-bordered mb-4">
                                                <tr><th>Name</th><td>{{ $info['name'] ?? '-' }}</td></tr>
                                                <tr><th>From</th><td>{{ $info['from_location'] ?? '-' }}</td></tr>
                                                <tr><th>To</th><td>{{ $info['to_location'] ?? '-' }}</td></tr>
                                                <tr><th>Port</th><td>{{ $info['port'] ?? '-' }}</td></tr>
                                                <tr><th>CBM</th><td>{{ $info['cbm'] ?? '-' }}</td></tr>
                                            </table>

                                            <!-- ORIGIN -->
                                            <h5 class="fw-bold border-bottom pb-2">Origin Charges</h5>
                                            @foreach(($d['origin'] ?? []) as $group => $rows)
                                                <h6 class="fw-bold mt-3">{{ $group }}</h6>
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Particular</th>
                                                            <th>Unit</th>
                                                            <th>Qty</th>
                                                            <th>Rate</th>
                                                            <th>ROE</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($rows as $r)
                                                        @if(is_array($r))
                                                        <tr>
                                                            <td>{{ $r['particular'] }}</td>
                                                            <td>{{ $r['unit'] }}</td>
                                                            <td>{{ $r['qty'] }}</td>
                                                            <td>{{ $r['rate'] }}</td>
                                                            <td>{{ $r['roe'] }}</td>
                                                            <td class="fw-bold">AED {{ number_format($r['amount'], 2) }}</td>
                                                        </tr>
                                                        @endif
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @endforeach

                                            <div class="alert alert-success fw-bold">
                                                Total Origin: AED {{ number_format($d['origin_total'] ?? 0, 2) }}
                                            </div>

                                            <!-- DESTINATION -->
                                            <h5 class="fw-bold border-bottom pb-2">Destination Charges</h5>
                                            @php $destinationTotal = 0; @endphp

                                            @foreach(($d['destination'] ?? []) as $group => $rows)
                                                <h6 class="fw-bold mt-3">{{ $group }}</h6>
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Particular</th>
                                                            <th>Unit</th>
                                                            <th>Qty</th>
                                                            <th>Rate</th>
                                                            <th>ROE</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($rows as $r)
                                                        @php
                                                            $qty = $r['qty'] ?? 0;
                                                            $rate = $r['rate'] ?? 0;
                                                            $roe = ($r['roe'] ?? 1) ?: 1;
                                                            $amountAED = ($rate * $qty) / $roe;
                                                            $destinationTotal += $amountAED;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $r['particular'] }}</td>
                                                            <td>{{ $r['unit'] }}</td>
                                                            <td>{{ $qty }}</td>
                                                            <td>{{ $rate }}</td>
                                                            <td>{{ $roe }}</td>
                                                            <td class="fw-bold">AED {{ number_format($amountAED, 2) }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @endforeach

                                            <div class="alert alert-success fw-bold">
                                                Total Destination: AED {{ number_format($destinationTotal, 2) }}
                                            </div>

                                            <!-- GRAND TOTAL -->
                                            @php
                                                $originTotal = $d['origin_total'] ?? 0;
                                                $grandTotal = $originTotal + $destinationTotal;
                                            @endphp

                                            <div class="alert alert-warning fw-bold">
                                                Grand Total: {{ number_format($grandTotal, 2) }} AED
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    No rate reports found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($reports->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $reports->links() }}
                </div>
                @endif

            </div>
        </div>

    </div>
</div>
@endsection
