<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomer" tabindex="-1" aria-labelledby="addCustomerLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('rate.customers.store') }}" method="POST"
              class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            @csrf

            <!-- Header -->
            <div class="position-relative p-4" style="background: linear-gradient(135deg, #141824 0%, #1e2736 60%, #253043 100%);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="text-white fw-bold mb-1">
                            <i class="fas fa-user-plus me-2 text-info"></i> Add New Customer
                        </h4>
                        <p class="text-white-50 mb-0">Enter the customer’s details below to register them.</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" 
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body -->
            <div class="modal-body py-4 px-4" style="background-color: #f8f9fa;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="name" class="form-control border-start-0" placeholder="John Doe" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" class="form-control border-start-0" placeholder="john@example.com">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Phone</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-phone text-muted"></i></span>
                            <input type="text" name="phone" class="form-control border-start-0" placeholder="+91 98765 43210">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                            <input type="text" name="address" class="form-control border-start-0" placeholder="City, Country">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 d-flex justify-content-between px-4 py-3" style="background-color: #fdfdfd;">
                <button type="button" class="btn" data-bs-dismiss="modal"
                    style="background: linear-gradient(90deg, #8b1e1e 0%, #a02222 50%, #b32c2c 100%);
                           color: #fff; border: none; padding: 10px 26px;
                           font-weight: 600; border-radius: 8px; letter-spacing: 0.5px;
                           box-shadow: 0 4px 10px rgba(176, 32, 32, 0.35);
                           transition: all 0.3s ease;">
                    <i class="fas fa-times me-2" style="color:#ffd6d6;"></i> Cancel
                </button>

                <button type="submit" class="btn"
                    style="background: linear-gradient(90deg, #141824 0%, #1e2736 60%, #253043 100%);
                           color: #fff; border: none; padding: 10px 26px;
                           font-weight: 600; border-radius: 8px; letter-spacing: 0.5px;
                           box-shadow: 0 4px 12px rgba(20, 24, 36, 0.4);
                           transition: all 0.3s ease;">
                    <i class="fas fa-save me-2" style="color:#00c6ff;"></i> Save Customer
                </button>
            </div>
        </form>
    </div>
</div>
