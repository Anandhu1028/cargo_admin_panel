@extends('admin.layouts.app')
@section('title', 'ROE Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                        <h4 class="page-title">ROE Settings</h4>
                        <div>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                                <li class="breadcrumb-item active">ROE Settings</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif


            <!-- 🌍 GLOBAL DESTINATION ROE -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">DESTINATION (Global) ROE</h5>
                    <p class="text-muted">This ROE is used for all Destination charges.</p>

                    <form method="POST" action="{{ route('roe.store') }}">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Global ROE Value</label>
                                <input type="number" step="0.0001" name="roe_value"
                                       class="form-control"
                                       value="{{ old('roe_value', optional($roeSetting)->roe_value) }}"
                                       required>
                            </div>

                            <div class="col-md-2">
                               <button type="submit" class="btn"
                                    style="background: linear-gradient(90deg,#141824,#1e2736 60%,#253043);color:#fff;border:none;padding:9px 22px;font-weight:600;border-radius:8px;">
                                    Save ROE
                                    <i class="fas fa-save ms-2" style="color:#00c6ff;"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>


            <!-- 🚢 OCEAN FREIGHT ROE (NEW SECTION) -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">OCEAN FREIGHT ROE</h5>
                    <p class="text-muted">
                        This ROE will be used ONLY for Ocean Freight row in Step-2.
                    </p>

                    <form method="POST" action="{{ route('roe.ocean.store') }}">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Ocean Freight ROE</label>
                                <input type="number" step="0.0001" name="ocean_freight_roe"
                                       class="form-control"
                                       value="{{ old('ocean_freight_roe', optional($roeSetting)->ocean_freight_roe) }}"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn"
                                    style="background: linear-gradient(90deg,#141824,#1e2736 60%,#253043);color:#fff;border:none;padding:9px 22px;font-weight:600;border-radius:8px;">
                                    Save Ocean ROE
                                    <i class="fas fa-save ms-2" style="color:#00c6ff;"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>


        </div>
    </div>
</div>
@endsection
