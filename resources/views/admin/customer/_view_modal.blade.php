<div class="modal fade" id="viewCustomer{{ $customer->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="p-4 text-white" style="background: linear-gradient(135deg, #141824, #1e2736, #253043);">
                <h5 class="fw-bold mb-1"><i class="fas fa-user-circle me-2 text-info"></i> View Customer</h5>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group list-group-flush text-start">
                    <li class="list-group-item"><strong>Name:</strong> {{ $customer->name }}</li>
                    <li class="list-group-item"><strong>Email:</strong> {{ $customer->email }}</li>
                    <li class="list-group-item"><strong>Phone:</strong> {{ $customer->phone }}</li>
                    <li class="list-group-item"><strong>Address:</strong> {{ $customer->address ?? '-' }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
