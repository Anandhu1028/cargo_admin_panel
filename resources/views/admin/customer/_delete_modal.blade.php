<!-- Delete Customer Modal -->
<div class="modal fade" id="deleteCustomer{{ $customer->id }}" tabindex="-1" aria-labelledby="deleteCustomerLabel{{ $customer->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            @csrf
            @method('DELETE')

            <!-- Header -->
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #8b1e1e 0%, #a02222 60%, #b32c2c 100%);">
                <h5 class="fw-bold mb-0">
                    <i class="fas fa-exclamation-triangle me-2 text-warning"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body text-center p-4" style="background-color: #f8f9fa;">
                <p class="fw-semibold text-dark mb-2">
                    Are you sure you want to delete <span class="text-danger">{{ $customer->name }}</span>?
                </p>
                <p class="text-muted mb-0">This action cannot be undone.</p>
            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 justify-content-center bg-white py-3">
                <button type="button" class="btn px-4 fw-semibold text-white"
                    style="background: linear-gradient(90deg, #6c757d 0%, #808b96 100%);
                           border: none; border-radius: 8px;"
                    data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i> Cancel
                </button>

                <button type="submit" class="btn px-4 fw-semibold text-white"
                    style="background: linear-gradient(90deg, #8b1e1e 0%, #a02222 50%, #b32c2c 100%);
                           border: none; border-radius: 8px;">
                    <i class="fas fa-trash-alt me-2 text-warning"></i> Yes, Delete
                </button>
            </div>
        </form>
    </div>
</div>
