<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomer{{ $customer->id }}" tabindex="-1" aria-labelledby="editCustomerLabel{{ $customer->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('customers.update', $customer->id) }}" method="POST" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            @csrf
            @method('PUT')

            <!-- Header -->
            <div class="position-relative p-4" style="background: linear-gradient(135deg, #141824 0%, #1e2736 60%, #253043 100%);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="text-white fw-bold mb-1">
                            <i class="fas fa-user-edit me-2 text-info"></i> Edit Customer
                        </h4>
                        <p class="text-white-50 mb-0">Update the customer's details below.</p>
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
                            <input type="text" name="name" value="{{ $customer->name }}" class="form-control border-start-0" placeholder="John Doe" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" value="{{ $customer->email }}" class="form-control border-start-0" placeholder="john@example.com" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Phone Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-phone text-muted"></i></span>
                            <input type="text" name="phone" value="{{ $customer->phone }}" class="form-control border-start-0" placeholder="+1 234 567 890" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                            <input type="text" name="address" value="{{ $customer->address }}" class="form-control border-start-0" placeholder="City, Country">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 d-flex justify-content-between px-4 py-3" style="background-color: #fdfdfd;">

                <!-- Cancel Button -->
                <button type="button"
                    data-bs-dismiss="modal"
                    style="
                        background: linear-gradient(90deg, #8b1e1e 0%, #a02222 50%, #b32c2c 100%);
                        color: #fff;
                        border: none;
                        padding: 10px 26px;
                        font-weight: 600;
                        border-radius: 8px;
                        letter-spacing: 0.5px;
                        box-shadow: 0 4px 10px rgba(176, 32, 32, 0.35);
                        transition: all 0.3s ease;
                    "
                    onmouseover="this.style.background='linear-gradient(90deg, #a82828 0%, #bb3333 50%, #c63d3d 100%)'; this.style.transform='translateY(-2px)';"
                    onmouseout="this.style.background='linear-gradient(90deg, #8b1e1e 0%, #a02222 50%, #b32c2c 100%)'; this.style.transform='translateY(0)';"
                    onmousedown="this.style.transform='scale(0.97)';"
                    onmouseup="this.style.transform='translateY(-2px)';"
                >
                    <i class="fas fa-times me-2" style="color:#ffd6d6;"></i> Cancel
                </button>

                <!-- Save Changes Button -->
                <button type="submit"
                    style="
                        background: linear-gradient(90deg, #141824 0%, #1e2736 60%, #253043 100%);
                        color: #fff;
                        border: none;
                        padding: 10px 26px;
                        font-weight: 600;
                        border-radius: 8px;
                        letter-spacing: 0.5px;
                        box-shadow: 0 4px 12px rgba(20, 24, 36, 0.4);
                        transition: all 0.3s ease;
                    "
                    onmouseover="this.style.background='linear-gradient(90deg, #1e2736 0%, #2b374f 60%, #34445e 100%)'; this.style.transform='translateY(-2px)';"
                    onmouseout="this.style.background='linear-gradient(90deg, #141824 0%, #1e2736 60%, #253043 100%)'; this.style.transform='translateY(0)';"
                    onmousedown="this.style.transform='scale(0.97)';"
                    onmouseup="this.style.transform='translateY(-2px)';"
                >
                    <i class="fas fa-save me-2" style="color:#00c6ff;"></i> Update Customer
                </button>

            </div>
        </form>
    </div>
</div>
