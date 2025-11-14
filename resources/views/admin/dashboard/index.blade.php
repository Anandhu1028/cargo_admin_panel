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
                        <h4 class="page-title">Dashboard</h4>
                        <div>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 fw-semibold">Total Shipments</p>
                                    <h4 class="mb-0">1,248</h4>
                                </div>
                                <div class="text-info fs-40">
                                    <i class="fas fa-box"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 fw-semibold">Delivered</p>
                                    <h4 class="mb-0">854</h4>
                                </div>
                                <div class="text-success fs-40">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 fw-semibold">In Transit</p>
                                    <h4 class="mb-0">320</h4>
                                </div>
                                <div class="text-warning fs-40">
                                    <i class="fas fa-truck"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 fw-semibold">Total Revenue</p>
                                    <h4 class="mb-0">$124,560</h4>
                                </div>
                                <div class="text-primary fs-40">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('rate.step1') }}" class="btn btn-primary btn-sm me-2">
                                <i class="fas fa-calculator me-2"></i>New Rate Calculation
                            </a>
                            <a href="#" class="btn btn-info btn-sm me-2">
                                <i class="fas fa-box me-2"></i>New Shipment
                            </a>
                            <a href="#" class="btn btn-success btn-sm">
                                <i class="fas fa-file-export me-2"></i>Generate Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div><!-- end page-content -->
</div><!-- end page-wrapper -->

@endsection