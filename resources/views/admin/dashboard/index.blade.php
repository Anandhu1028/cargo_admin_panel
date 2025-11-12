@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-wrapper">
    <!-- Page Content-->
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Title -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                        <h4 class="page-title">Cargo Dashboard</h4>
                        <div>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Approx Cargo</a></li>
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cargo Summary Cards -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-globe-img">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-16 fw-semibold">Cargo Summary</span>
                                <form>
                                    <select id="warehouse-select" class="form-select form-select-sm">
                                        <option>Warehouse A</option>
                                        <option>Warehouse B</option>
                                        <option>Warehouse C</option>
                                    </select>
                                </form>
                            </div>

                            <h4 class="my-2 fs-24 fw-semibold">1,248 <small class="font-14">Shipments</small></h4>
                            <p class="mb-3 text-muted fw-semibold">
                                <span class="text-success"><i class="fas fa-arrow-up me-1"></i>4.8%</span> Growth this week
                            </p>
                            <button type="button" class="btn btn-soft-primary">New Shipment</button>
                            <button type="button" class="btn btn-soft-info">Generate Report</button>

                            <div class="row mt-3">
                                <div class="col-4">
                                    <div class="p-2 border-dashed border-theme-color rounded text-center">
                                        <h5 class="mt-1 mb-0 fw-medium">854</h5>
                                        <small class="text-muted">Delivered</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 border-dashed border-theme-color rounded text-center">
                                        <h5 class="mt-1 mb-0 fw-medium">320</h5>
                                        <small class="text-muted">In Transit</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 border-dashed border-theme-color rounded text-center">
                                        <h5 class="mt-1 mb-0 fw-medium">74</h5>
                                        <small class="text-muted">Delayed</small>
                                    </div>
                                </div>
                            </div>
                            <p class="mb-0 mt-2 text-info fst-italic">Last delivery: 560kg cargo from Dubai to Mumbai</p>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="col-md-6 col-lg-4">
                    @foreach ([ 
                        ['Total Revenue', '$124,560', 'iconoir-wallet'], 
                        ['Avg Delivery Time', '2.4 Days', 'iconoir-clock'], 
                        ['Fuel Cost (Monthly)', '$8,230', 'iconoir-gas-tank'] 
                    ] as [$label, $value, $icon])
                        <div class="card bg-corner-img mb-3">
                            <div class="card-body">
                                <div class="row d-flex justify-content-center">
                                    <div class="col-9">
                                        <p class="text-muted text-uppercase mb-0 fw-normal fs-13">{{ $label }}</p>
                                        <h4 class="mt-1 mb-0 fw-medium">{{ $value }}</h4>
                                    </div>
                                    <div class="col-3 align-self-center">
                                        <div class="d-flex justify-content-center align-items-center thumb-md border-dashed border-info rounded mx-auto">
                                            <i class="{{ $icon }} fs-22 align-self-center mb-0 text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Delivery Rate Chart -->
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Delivery Performance</h4>
                        </div>
                        <div class="card-body pt-0">
                            <div id="delivery_chart" class="apex-charts"></div>
                        </div>
                    </div>
                </div>
            </div>

           
        <!-- Footer -->
        <footer class="footer text-center text-sm-start d-print-none">
            <div class="container-fluid">
                <div class="card mb-0 rounded-bottom-0">
                    <div class="card-body">
                        <p class="text-muted mb-0">
                            © <script>document.write(new Date().getFullYear())</script> Cargo ERP
                            <span class="text-muted d-none d-sm-inline-block float-end">
                                Built with <i class="iconoir-heart-solid text-danger align-middle"></i> by Mannatthemes
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </footer>

    </div><!-- end page-content -->
</div><!-- end page-wrapper -->



@endsection
