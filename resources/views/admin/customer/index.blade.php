@extends('admin.layouts.app')
@section('title', 'Customer List')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="container-fluid">

            {{--  SUCCESS TOAST --}}
            @if(session('success'))
                <div id="success-toast"
                    class="alert alert-success border-0 shadow-lg position-fixed top-0 end-0 m-4 d-flex align-items-center fade show"
                    role="alert"
                    style="
                        width: auto;
                        min-width: 280px;
                        max-width: 360px;
                        background: #f0fff5;
                        color: #155724;
                        border-left: 5px solid #00b894;
                        border-radius: 8px;
                        z-index: 1055;
                    ">
                    <div class="d-inline-flex justify-content-center align-items-center bg-success rounded-circle me-2"
                        style="width: 28px; height: 28px;">
                        <i class="fas fa-check text-white"></i>
                    </div>
                    <div>
                        <strong>Success!</strong> {{ session('success') }}
                    </div>
                </div>
            @endif

            {{--  DELETE TOAST --}}
            @if(session('delete-success'))
                <div id="error-toast"
                    class="alert alert-danger border-0 shadow-lg position-fixed top-0 end-0 m-4 d-flex align-items-center fade show"
                    role="alert"
                    style="
                        width: auto;
                        min-width: 280px;
                        max-width: 360px;
                        background: #fff5f5;
                        color: #721c24;
                        border-left: 5px solid #dc3545;
                        border-radius: 8px;
                        z-index: 1055;
                    ">
                    <div class="d-inline-flex justify-content-center align-items-center bg-danger rounded-circle me-2"
                        style="width: 28px; height: 28px;">
                        <i class="fas fa-xmark text-white"></i>
                    </div>
                    <div>
                        <strong>Deleted!</strong> {{ session('delete-success') }}
                    </div>
                </div>
            @endif

            <script>
                setTimeout(() => {
                    document.querySelectorAll('.alert').forEach(alert => {
                        alert.style.transition = 'opacity 0.5s ease';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 500);
                    });
                }, 3000);
            </script>

            <!-- 🔹 Page Title & Breadcrumb -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                        <h4 class="page-title">Customers</h4>
                        <div>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                                <li class="breadcrumb-item active">Customers</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🔹 Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="card-title mb-0">Customer Details</h4>
                                </div>
                                <div class="col-auto">
                                    <div class="col-auto">
                                        
                                        <button type="button"
                                            class="btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#addCustomer"
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
                                            "
                                            onmouseover="this.style.background='linear-gradient(90deg, #1e2736 0%, #2b374f 60%, #34445e 100%)'; this.style.transform='translateY(-2px)';"
                                            onmouseout="this.style.background='linear-gradient(90deg, #141824 0%, #1e2736 60%, #253043 100%)'; this.style.transform='translateY(0)';"
                                            onmousedown="this.style.transform='scale(0.97)';"
                                            onmouseup="this.style.transform='translateY(-2px)';"
                                        >
                                            <i class="fas fa-user-plus me-2" style="color:#00c6ff;"></i> Add Customer
                                        </button>
                                    </div>


                                </div>
                            </div>
                        </div>

                        <!-- 🔹 Table -->
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table mb-0 align-middle">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Address</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center align-middle">
                                        @forelse($customers as $customer)
                                            @php
                                                $colors = ['primary','success','danger','warning','info','secondary'];
                                                $color = $colors[crc32($customer->name) % count($colors)];
                                            @endphp
                                            <tr>
                                                <td class="d-flex align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2 thumb-md align-self-center rounded-circle bg-{{ $color }} d-flex justify-content-center align-items-center text-white fw-bold" style="width:40px;height:40px;">
                                                            {{ strtoupper(substr($customer->name, 0, 2)) }}
                                                        </div>
                                                        <div class="flex-grow-1 text-truncate">
                                                            <h6 class="m-0">{{ $customer->name }}</h6>
                                                           
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><a href="#" class="text-body ">{{ $customer->email }}</a></td>
                                                <td>{{ $customer->phone }}</td>
                                                <td>{{ $customer->address ?? '-' }}</td>
                                                <td class="text-end">
                                                    <!-- Neutral Action Icons -->
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#viewCustomer{{ $customer->id }}" class="action-icon">
                                                        <i class="las la-eye fs-18 text-secondary"></i>
                                                    </a>
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#editCustomer{{ $customer->id }}" class="action-icon">
                                                        <i class="las la-pen fs-18 text-secondary"></i>
                                                    </a>
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#deleteCustomer{{ $customer->id }}" class="action-icon">
                                                        <i class="las la-trash-alt fs-18 text-secondary"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No customers found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div> <!-- End Card Body -->
                    </div> <!-- End Card -->
                </div> <!-- End Col -->
            </div> <!-- End Row -->

            <!--  Modals should always be outside the card/table -->
            @include('admin.customer._create_modal')

            @foreach($customers as $customer)
                @include('admin.customer._view_modal', ['customer' => $customer])
                @include('admin.customer._edit_modal', ['customer' => $customer])
                @include('admin.customer._delete_modal', ['customer' => $customer])
            @endforeach

        </div> <!-- End Container -->
    </div> <!-- End Page Content -->
</div> <!-- End Page Wrapper -->

<style>
.thumb-md {
    font-size: 13px;
    transition: all 0.2s ease;
}
.thumb-md:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
.action-icon i {
    transition: transform 0.2s ease, color 0.2s ease;
}
.action-icon:hover i {
    transform: scale(1.2);
    color: #000 !important;
}
.table td, .table th {
    vertical-align: middle !important;
}
</style>
@endsection
