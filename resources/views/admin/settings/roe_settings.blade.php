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

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">DESTINATION (Global) ROE</h5>
                    <p class="text-muted">Enter the ROE used by the Destination section (single global value). Format: decimal (e.g. 0.0439)</p>

                    <form method="POST" action="{{ route('roe.store') }}">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">ROE Value</label>
                                <input type="number" step="0.0001" name="roe_value" class="form-control" value="{{ old('roe_value', optional($roeSetting)->roe_value) }}" required>
                            </div>
                            <div class="col-md-2">
                               <button type="submit" class="btn"
                                    style="
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
                                    "
                                    onmouseover="this.style.background='linear-gradient(90deg, #1e2736 0%, #2b374f 60%, #34445e 100%)'; this.style.transform='translateY(-2px)';"
                                    onmouseout="this.style.background='linear-gradient(90deg, #141824 0%, #1e2736 60%, #253043 100%)'; this.style.transform='translateY(0)';"
                                    onmousedown="this.style.transform='scale(0.97)';"
                                    onmouseup="this.style.transform='translateY(-2px)';">
                                    Save ROE
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
