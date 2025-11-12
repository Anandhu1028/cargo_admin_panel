@extends('admin.layouts.app')
@section('title', 'Rate Calculator - Step 1')

@section('content')
<div class="page-wrapper">
  <div class="page-content container-fluid">

    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
      <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
          <h4 class="page-title">STEP 1 of 3 - Basic Shipment Details</h4>
          <div>
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="#">Admin</a></li>
              <li class="breadcrumb-item active">Rate Calculator</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <form method="POST" action="{{ route('rate.step1.store') }}">
          @csrf

          <!-- CUSTOMER -->
          <div class="row mb-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Customer Name</label>
              <select name="customer_id" id="customerSelect" class="form-select" required>
                <option value="">Select Customer</option>
                @foreach ($customers as $customer)
                  <option value="{{ $customer->id }}"
                    {{ (old('customer_id', $calc->customer_id ?? '') == $customer->id) ? 'selected' : '' }}>
                    {{ $customer->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Add Customer Modal Button -->
            <div class="col-md-1 d-flex align-items-end">
              <button type="button"
                class="btn p-0 border-0 bg-transparent"
                data-bs-toggle="modal"
                data-bs-target="#addCustomer"
                title="Add Customer"
                style="color: #141824; font-size: 22px; transition: all 0.25s ease;"
                onmouseover="this.style.color='#33d0ff'; this.style.transform='scale(1.2)';"
                onmouseout="this.style.color='#141824'; this.style.transform='scale(1)';"
                onmousedown="this.style.transform='scale(0.9)';"
                onmouseup="this.style.transform='scale(1.2)';">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>

          <!-- LOCATIONS & SHIPMENT INFO -->
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">From Location</label>
              <input type="text" name="from_location" class="form-control"
                     value="{{ old('from_location', $calc->from_location ?? '') }}"
                     placeholder="Enter city or district" required>
            </div>

            <div class="col-md-3">
              <label class="form-label">To Location</label>
              <input type="text" name="to_location" class="form-control"
                     value="{{ old('to_location', $calc->to_location ?? '') }}"
                     placeholder="Enter city or district" required>
            </div>

            <div class="col-md-3">
              <label class="form-label">Port Name</label>
              <input type="text" name="port" class="form-control"
                     value="{{ old('port', $calc->port ?? '') }}">
            </div>

            <div class="col-md-3">
              <label class="form-label">CBM</label>
              <input type="number" name="cbm" step="0.001" class="form-control"
                     value="{{ old('cbm', $calc->cbm ?? '') }}" required>
            </div>

            <div class="col-md-3">
              <label class="form-label">Actual Weight (kg)</label>
              <input type="number" name="actual_weight" step="0.1" class="form-control"
                     value="{{ old('actual_weight', $calc->actual_weight ?? '') }}">
            </div>

            <div class="col-md-3">
              <label class="form-label">Condition</label>
              <select name="condition" class="form-select">
                <option value="new" {{ old('condition', $calc->condition ?? '') == 'new' ? 'selected' : '' }}>New</option>
                <option value="used" {{ old('condition', $calc->condition ?? '') == 'used' ? 'selected' : '' }}>Used</option>
              </select>
            </div>
          </div>

          <!-- ADDITIONAL PACKING -->
          <div class="row mt-4">
            <div class="col-md-3">
              <div class="form-check">
                <input type="hidden" name="additional_packing" value="0">
                <input type="checkbox" name="additional_packing" id="additional_packing"
                       class="form-check-input" value="1"
                       {{ old('additional_packing', $calc->additional_packing ?? false) ? 'checked' : '' }}>
                <label for="additional_packing" class="form-check-label">Additional Packing</label>
              </div>
            </div>
          </div>

          <!-- NEXT BUTTON -->
          <div class="mt-4 text-end">
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

<!-- Add Customer Modal -->
@include('admin.rate._add_customer_modal')
@endsection
