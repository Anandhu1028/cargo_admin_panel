@extends('admin.layouts.app')
@section('title', 'Rate Calculation History')

@section('content')

<style>
.table td, .table th {
    vertical-align: middle !important;
    font-size: 13px;
}
.table th {
    background-color: #f6f8fb !important;
    font-weight: 700 !important;
    text-transform: uppercase;
}
.card-header {
    background: #f8f9fa !important;
}
</style>

<div class="page-wrapper">
    <div class="page-content container-fluid">

        <!-- PAGE TITLE -->
        <div class="row mb-4">
            <div class="col-sm-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title fw-bold">Rate Calculation History</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#">Admin</a></li>
                        <li class="breadcrumb-item active">History</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- HISTORY -->
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">

    <h5 class="fw-bold mb-0">Saved Rate Reports</h5>

    <form action="{{ route('rate.history.deleteAll') }}"
          method="POST"
          onsubmit="return confirm('Delete ALL rate reports permanently?')">
        @csrf
        @method('DELETE')

        <button type="submit" class="btn-delete-all">
            <i class="fas fa-trash-alt me-1"></i> Delete All
        </button>
    </form>

</div>


            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Date</th>
                                <th width="200" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        @forelse($reports as $report)
                            @php $d = $report->report_data; @endphp

                            <tr>
                                <td class="fw-bold">{{ $report->customer_name }}</td>
                                <td>{{ $report->created_at->format('d M Y H:i') }}</td>

                               <td class="text-center">

    <div class="d-flex justify-content-center align-items-center gap-2">

        <!-- VIEW -->
        <button type="button"
                class="btn-view"
                data-bs-toggle="collapse"
                data-bs-target="#report-{{ $report->id }}">
            <i class="fas fa-eye" style="color:#00c6ff;"></i>
            View
        </button>

        <!-- DELETE -->
        <form action="{{ route('rate.history.delete', $report->id) }}"
              method="POST" class="m-0 p-0">
            @csrf @method('DELETE')

            <button type="submit"
                    onclick="return confirm('Delete report?')"
                    class="btn-delete">
                <i class="fas fa-trash" style="color:#ffd6d6;"></i>
                Delete
            </button>
        </form>

    </div>

</td>

                            </tr>

                            <!-- COLLAPSE CONTENT -->
                            <tr class="table-light">
                                <td colspan="3" class="p-0">

                                    <div class="collapse p-4" id="report-{{ $report->id }}">

                                        @if(!$d || !is_array($d))
                                            <div class="alert alert-warning">
                                                No valid snapshot stored.
                                            </div>
                                        @else

                                            @php
                                                $info = $d['customer_info'] ?? [];
                                                $cbm = $info['cbm'] ?? 0;
                                                $ratio = $cbm / 50;
                                            @endphp

                                            <!-- CUSTOMER INFO -->
                                            <h5 class="fw-bold border-bottom pb-2">Customer Information</h5>

                                            <table class="table table-sm table-bordered mb-4">
                                                <tr><th>Name</th><td>{{ $info['name'] ?? '-' }}</td></tr>
                                                <tr><th>From</th><td>{{ $info['from_location'] ?? '-' }}</td></tr>
                                                <tr><th>To</th><td>{{ $info['to_location'] ?? '-' }}</td></tr>
                                                <tr><th>Port</th><td>{{ $info['port'] ?? '-' }}</td></tr>
                                                <tr><th>CBM</th><td>{{ number_format($cbm,2) }}</td></tr>
                                            </table>

                                            <!-- ==========================
                                                 ORIGIN CHARGES
                                            =========================== -->
                                            <h5 class="fw-bold text-primary mb-3">Dubai / Origin Charges</h5>

                                            @php $originTotal = 0; @endphp

                                            @foreach(($d['origin'] ?? []) as $group => $rows)

                                                @php $groupTotal = 0; @endphp

                                                <div class="card shadow-sm border-0 mb-3 rounded-3">
                                                    <div class="card-header py-2">
                                                        <h6 class="fw-bold mb-0">
                                                            {{ strtoupper(str_replace('_',' ',$group)) }}
                                                        </h6>
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
                                                                    <th class="text-end">Amount (AED)</th>
                                                                    <th class="text-end">Total Charges</th>
                                                                </tr>
                                                            </thead>

                                                            <tbody>
                                                                @foreach($rows as $r)
                                                                    @php
                                                                        $qty = $r['qty'] ?? 0;
                                                                        $rate = $r['rate'] ?? 0;
                                                                        $roe = ($r['roe'] ?? 1) ?: 1;

                                                                        $amount = $r['amount'] ?? (($qty * $rate) / $roe);
                                                                        $charge = $r['charge'] ?? ($amount * $ratio);

                                                                        $groupTotal += $charge;
                                                                    @endphp

                                                                    <tr>
                                                                        <td class="text-start ps-3">{{ ucfirst($r['particular']) }}</td>
                                                                        <td>{{ $r['unit'] }}</td>
                                                                        <td>{{ number_format($qty,2) }}</td>
                                                                        <td>{{ number_format($rate,2) }}</td>
                                                                        <td>{{ number_format($roe,4) }}</td>
                                                                        <td class="text-end">{{ number_format($amount,2) }} AED</td>
                                                                        <td class="text-end fw-bold">{{ number_format($charge,2) }} AED</td>
                                                                    </tr>
                                                                @endforeach

                                                                <tr class="table-primary fw-bold">
                                                                    <td colspan="6" class="text-start ps-3">
                                                                        Sub Total ({{ strtoupper(str_replace('_',' ',$group)) }})
                                                                    </td>
                                                                    <td class="text-end">{{ number_format($groupTotal,2) }} AED</td>
                                                                </tr>

                                                            </tbody>
                                                        </table>

                                                    </div>
                                                </div>

                                                @php $originTotal += $groupTotal; @endphp
                                            @endforeach

                                            <div class="alert alert-success fw-bold">
                                                Total Origin Charges: AED {{ number_format($originTotal,2) }}
                                            </div>

                                            <!-- ==========================
                                                 DESTINATION CHARGES
                                            =========================== -->
                                            <h5 class="fw-bold text-primary mb-3">Destination / Kochi Locals</h5>

                                            @php $destTotal = 0; @endphp

                                            @foreach(($d['destination'] ?? []) as $group => $rows)

                                                @php $groupTotal = 0; @endphp

                                                <div class="card shadow-sm border-0 mb-3 rounded-3">
                                                    <div class="card-header py-2">
                                                        <h6 class="fw-bold mb-0">
                                                            {{ strtoupper(str_replace('_',' ',$group)) }}
                                                        </h6>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <table class="table table-hover mb-0 text-center">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th class="text-start ps-3">Particulars</th>
                                                                    <th>Unit</th>
                                                                    <th>Qty</th>
                                                                    <th>Rate (INR)</th>
                                                                    <th>ROE</th>
                                                                    <th class="text-end">Amount (AED)</th>
                                                                    <th class="text-end">Total Charges</th>
                                                                </tr>
                                                            </thead>

                                                            <tbody>
                                                                @foreach($rows as $r)
                                                                    @php
                                                                        $qty = $r['qty'] ?? 0;
                                                                        $rate = $r['rate'] ?? 0;
                                                                        $roe = ($r['roe'] ?? 1) ?: 1;

                                                                        $amount = $r['amount'] ?? (($qty * $rate) / $roe);
                                                                        $charge = $r['charge'] ?? ($amount * $ratio);

                                                                        $groupTotal += $charge;
                                                                    @endphp

                                                                    <tr>
                                                                        <td class="text-start ps-3">{{ ucfirst($r['particular']) }}</td>
                                                                        <td>{{ $r['unit'] }}</td>
                                                                        <td>{{ number_format($qty,2) }}</td>
                                                                        <td>{{ number_format($rate,2) }}</td>
                                                                        <td>{{ number_format($roe,4) }}</td>
                                                                        <td class="text-end">{{ number_format($amount,2) }} AED</td>
                                                                        <td class="text-end fw-bold">{{ number_format($charge,2) }} AED</td>
                                                                    </tr>
                                                                @endforeach

                                                                <tr class="table-primary fw-bold">
                                                                    <td colspan="6" class="text-start ps-3">
                                                                        Sub Total ({{ strtoupper(str_replace('_',' ',$group)) }})
                                                                    </td>
                                                                    <td class="text-end">{{ number_format($groupTotal,2) }} AED</td>
                                                                </tr>

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                @php $destTotal += $groupTotal; @endphp
                                            @endforeach

                                            <div class="alert alert-success fw-bold">
                                                Total Destination Charges: AED {{ number_format($destTotal,2) }}
                                            </div>

                                            <div class="alert alert-warning fw-bold">
                                                Grand Total: AED {{ number_format($originTotal + $destTotal,2) }}
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

                <!-- <div class="d-flex justify-content-center mt-3">
                    {{ $reports->links() }}
                </div> -->

            </div>
        </div>

    </div>
</div>
<style>
.btn-delete-all {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    background: linear-gradient(90deg,#8b1e1e,#a02222 50%,#b32c2c);
    color: #fff;
    border: none;
    padding: 8px 18px;
    font-weight: 600;
    border-radius: 8px;
    letter-spacing: 0.4px;
    box-shadow: 0 4px 10px rgba(176,32,32,0.35);
    transition: all .3s ease;
}

.btn-delete-all:hover {
    background: linear-gradient(90deg,#a82828,#bb3333 50%,#c63d3d);
    transform: translateY(-2px);
}


.btn-view {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    background: linear-gradient(90deg,#141824,#1e2736 60%,#253043);
    color: #fff;
    border: none;
    padding: 8px 18px;
    font-weight: 600;
    border-radius: 8px;
    letter-spacing: 0.4px;
    box-shadow: 0 4px 12px rgba(20,24,36,0.4);
    transition: all .3s ease;
}
.btn-view:hover {
    background: linear-gradient(90deg,#1e2736,#2b374f 60%,#34445e);
    transform: translateY(-2px);
}

.btn-delete {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    background: linear-gradient(90deg,#8b1e1e,#a02222 50%,#b32c2c);
    color: #fff;
    border: none;
    padding: 8px 18px;
    font-weight: 600;
    border-radius: 8px;
    letter-spacing: 0.4px;
    box-shadow: 0 4px 10px rgba(176,32,32,0.35);
    transition: all .3s ease;
}
.btn-delete:hover {
    background: linear-gradient(90deg,#a82828,#bb3333 50%,#c63d3d);
    transform: translateY(-2px);
}
</style>

@endsection
