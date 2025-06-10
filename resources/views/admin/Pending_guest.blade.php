@extends('admin.layout') {{-- Ensure this is your admin's layout --}}

@section('content')
<div class="container mt-4">
    <h3>Pending Guest Requests</h3>

    <div id="mainResponseMessage" class="mt-3"></div> {{-- Message container for the page --}}

    <table class="table table-bordered mt-3">
        <thead class="thead-dark">
            <tr>
                <th>S.No.</th> {{-- Changed from ID to S.No. --}}
                <th>Scholar No</th>
                <th>Name</th>
                <th>Father's Name</th>
                <th>Mother's Name</th>
                <th>Local Guardian</th>
                <th>Emergency No</th>
                <th>Gender</th>
                <th>Room Preference</th>
                <th>Food Preference</th>
                <th>Fee Waiver</th>
                <th>Account Remark</th> {{-- NEW COLUMN --}}
                <th>Status</th> {{-- NEW COLUMN --}}
                <th>Guest Remarks</th>
                <th>Guest Attachment</th>
                <th>Accessories</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="guestList">
            {{-- Adjusted colspan based on the new column count (14 static + 3 new = 17) --}}
            <tr>
                <td colspan="17" class="text-center">Loading pending guests...</td>
            </tr>
        </tbody>
    </table>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <div class="modal fade" id="editAmountModal" tabindex="-1" aria-labelledby="editAmountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAmountModalLabel">Edit Payment Amount</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAmountForm" enctype="multipart/form-data">
                        <input type="hidden" name="guest_id" id="edit_guest_id">
                        <input type="hidden" name="created_by" id="created_by" value="{{ auth()->user()->id ?? '' }}">

                        <div class="mb-3">
                            <label for="hostel_fee" class="form-label">Hostel Fee*</label>
                            <input type="number" class="form-control" id="hostel_fee" name="hostel_fee" required>
                        </div>

                        <div class="mb-3">
                            <label for="caution_money" class="form-label">Caution Money*</label>
                            <input type="number" class="form-control" id="caution_money" name="caution_money" required>
                        </div>

                        <div class="mb-3">
                            <label for="months" class="form-label">Months</label>
                            <input type="number" class="form-control" id="months" name="months" min="0">
                        </div>

                        <div class="mb-3">
                            <label for="days" class="form-label">Days</label>
                            <input type="number" class="form-control" id="days" name="days" min="0" max="31">
                        </div>

                        <div class="mb-3">
                            <label for="facility" class="form-label">Facility</label>
                            <input type="text" class="form-control" id="facility" name="facility">
                        </div>

                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="approved_by" class="form-label">Approved By</label>
                            <input type="text" class="form-control" id="approved_by" name="approved_by">
                        </div>

                        <div class="mb-3">
                            <label for="document" class="form-label">Upload Document</label>
                            <input type="file" class="form-control" id="document" name="document" accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <div class="mb-3 text-danger" id="editAmountErrors"></div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">Save Changes</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="reviewRejectModal" tabindex="-1" aria-labelledby="reviewRejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewRejectModalLabel">Review & Adjust Rejected Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reviewRejectForm" enctype="multipart/form-data">
                    <input type="hidden" name="guest_id" id="review_guest_id">
                    <input type="hidden" name="created_by" id="review_created_by" value="{{ auth()->user()->id ?? '' }}">

                    <div class="mb-3">
                        <label for="review_hostel_fee" class="form-label">Hostel Fee*</label>
                        <input type="number" class="form-control" id="review_hostel_fee" name="hostel_fee" required>
                    </div>

                    <div class="mb-3">
                        <label for="review_caution_money" class="form-label">Caution Money*</label>
                        <input type="number" class="form-control" id="review_caution_money" name="caution_money" required>
                    </div>

                    <!-- <div class="mb-3">
                        <label class="form-label">Calculated Total Amount:</label>
                        <span id="review_calculated_total_display" class="form-control-plaintext fw-bold">0.00</span>
                    </div> -->

                    <div class="mb-3">
                        <label for="review_months" class="form-label">Months</label>
                        <input type="number" class="form-control" id="review_months" name="months" min="0">
                    </div>

                    <div class="mb-3">
                        <label for="review_days" class="form-label">Days</label>
                        <input type="number" class="form-control" id="review_days" name="days" min="0" max="31">
                    </div>

                    <div class="mb-3">
                        <label for="review_facility" class="form-label">Facility</label>
                        <input type="text" class="form-control" id="review_facility" name="facility">
                    </div>

                    <div class="mb-3">
                        <label for="review_remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="review_remarks" name="remarks"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="review_approved_by" class="form-label">Approved By</label>
                        <input type="text" class="form-control" id="review_approved_by" name="approved_by">
                    </div>

                    <div class="mb-3">
                        <label for="review_document" class="form-label">Upload New Document (optional)</label>
                        <input type="file" class="form-control" id="review_document" name="document" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="form-text text-muted" id="review_currentDocumentInfo"></small>
                    </div>

                    <div class="mb-3 text-danger" id="reviewAmountErrors"></div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">Save Review & Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    {{-- Accessory Details Modal --}}
    <div class="modal fade" id="accessoryModal" tabindex="-1" role="dialog" aria-labelledby="accessoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Accessories</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="accessoryList">
                </div>
            </div>
        </div>
    </div>

</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- <script>
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    // Function to show a custom message box
    function showCustomMessageBox(message, type = 'info', targetElementId = 'mainResponseMessage') {
        const messageContainer = document.getElementById(targetElementId);
        if (messageContainer) {
            messageContainer.innerHTML = ""; // Clear previous messages
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            messageContainer.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000); // Remove after 3 seconds
        } else {
            console.warn(`Message container #${targetElementId} not found.`);
        }
    }

    // Function to show a message within the modal (retained for potential use)
    function showModalMessage(message, type = 'info', targetElementId = 'adjustPaymentMessage') {
        const messageContainer = document.getElementById(targetElementId); // Assuming 'adjustPaymentMessage' is somewhere in your modal
        if (messageContainer) {
            messageContainer.innerHTML = "";
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            messageContainer.appendChild(alertDiv);
        } else {
            console.warn(`Message container #${targetElementId} not found.`);
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        fetchPendingGuests();

        let editAmountModal = new bootstrap.Modal(document.getElementById('editAmountModal'), {
            backdrop: 'static',
            keyboard: false
        });

        // Approve Guest Function
        window.approveGuest = function(guestId) {
            fetch("{{ url('/api/admin/approved-guest') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": getCsrfToken(),
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        guest_id: guestId
                    })
                })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        showCustomMessageBox(response.message || "Guest approved successfully.", 'success');
                        fetchPendingGuests();
                    } else {
                        showCustomMessageBox(response.message || "Approval failed.", 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error approving guest:', error);
                    showCustomMessageBox('An error occurred during approval.', 'danger');
                });
        }

        // Reject guest function
        window.denyGuest = function(guestId) {
            fetch("{{ url('/api/payment/reject') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": getCsrfToken(),
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        guest_id: guestId
                    })
                })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        showCustomMessageBox(response.message || "Guest rejected successfully.", 'success');
                        fetchPendingGuests();
                    } else {
                        showCustomMessageBox(response.message || "Rejection failed.", 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error denying guest:', error);
                    showCustomMessageBox('An error occurred during rejection.', 'danger');
                });
        }

        function fetchPendingGuests() {
            let guestList = document.getElementById("guestList");
            // Updated colspan to 17 here as well
            guestList.innerHTML = `<tr><td colspan="17" class="text-center">Loading pending guests...</td></tr>`;

            fetch("{{ url('/api/guests/pending') }}") // This API endpoint fetches only 'pending' guests
                .then(response => response.json())
                .then(response => {
                    const guests = response.data;
                    guestList.innerHTML = "";

                    // Updated colspan to 17 for "No pending guests found."
                    if (!response.success || !Array.isArray(guests) || guests.length === 0) {
                        guestList.innerHTML = `<tr><td colspan="17" class="text-center">No pending guests found.</td></tr>`;
                        if (!response.success && response.message) {
                            showCustomMessageBox(response.message, 'danger');
                        }
                        return;
                    }

                    guests.forEach((guest, index) => { // Added index for S.No.
                        let accessoriesButton = `<button class="btn btn-info btn-sm" onclick='viewAccessories(${JSON.stringify(guest.accessories || [])})'>View</button>`;

                        const feeWaiverStatus = guest.fee_waiver ? 'Yes' : 'No';
                        // Removed Current Payable Display
                        const remarksContent = guest.remarks || 'N/A'; // General guest remarks

                        // NEW: Account Remark from fee_exception table
                        const accountRemarkContent = guest.fee_exception && guest.fee_exception.account_remark ? guest.fee_exception.account_remark : 'N/A';
                        // NEW: Status from guest table
                        const guestStatusDisplay = guest.status || 'N/A';

                        let attachmentLink = 'N/A';
                        if (guest.attachment_path) {
                            attachmentLink = `<a href="{{ asset('storage/') }}/${guest.attachment_path}" target="_blank" class="btn btn-sm btn-secondary">View</a>`;
                        }

                        let actionButtons = '';

                        if (guest.status === 'pending') {
                            actionButtons = `
                                <button class="btn btn-success btn-sm mb-1" onclick="approveGuest(${guest.id})">Approve</button>
                                <button class="btn btn-danger btn-sm mb-1" onclick="denyGuest(${guest.id})">Deny</button>
                            `;

                            if (guest.fee_waiver) {
                                if (!guest.fee_waiver_approved) {
                                    actionButtons += `
                                        <button class="btn btn-primary btn-sm mb-1" onclick="approveFeeWaiver(${guest.id})">Approve Fee Waiver</button>
                                    `;
                                }
                            }
                        }

                        // The "Review Rejected Status" button should open the *payment adjustment* modal
                        // as you're in the admin panel and can modify waiver/payment.
                        // If it's meant to change the *guest's status* back from reject, you'd need a separate
                        // modal and API for that, or repurpose `approveFeeWaiver` to trigger a status change,
                        // which currently triggers `showAdjustPaymentModal`.
                        if (guest.status === 'accountant_reject') {
                            actionButtons += `
                                <button class="btn btn-warning btn-sm mb-1" onclick="showAdjustPaymentModal(${guest.id})">
                                    Review Rejected Status
                                </button>
                            `;
                        }

                        guestList.innerHTML += `
                            <tr id="guest-${guest.id}">
                                <td>${index + 1}</td> {{-- S.No. --}}
                                <td>${guest.scholar_no || 'N/A'}</td>
                                <td>${guest.name || '-'}</td>
                                <td>${guest.fathers_name || 'N/A'}</td>
                                <td>${guest.mothers_name || 'N/A'}</td>
                                <td>${guest.local_guardian_name || 'N/A'}</td>
                                <td>${guest.emergency_no || 'N/A'}</td>
                                <td>${guest.gender || '-'}</td>
                                <td>${guest.room_preference || 'N/A'}</td>
                                <td>${guest.food_preference || 'N/A'}</td>
                                <td>${feeWaiverStatus}</td>
                                <td>${accountRemarkContent}</td> {{-- Account Remark --}}
                                <td>${guestStatusDisplay}</td> {{-- Status --}}
                                <td>${remarksContent}</td>
                                <td>${attachmentLink}</td>
                                <td>${accessoriesButton}</td>
                                <td>
                                    ${actionButtons}
                                </td>
                            </tr>
                        `;
                    });
                })
                .catch(error => {
                    console.error('Error fetching guests:', error);
                    // Updated colspan to 17 here as well
                    guestList.innerHTML = `<tr><td colspan="17" class="text-center text-danger">Failed to load guests.</td></tr>`;
                    showCustomMessageBox('Failed to load pending guests.', 'danger');
                });
        }

        window.viewAccessories = function(accessories) {
            const accessoryList = document.getElementById("accessoryList");
            accessoryList.innerHTML = "";

            if (!Array.isArray(accessories) || accessories.length === 0) {
                accessoryList.innerHTML = "<p>No accessories found for this guest.</p>";
            } else {
                let table = `
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Price</th>
                                <th>From Date</th>
                                <th>To Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${accessories.map(item => `
                                <tr>
                                    <td>${item.accessory_head ? item.accessory_head.name : 'N/A'}</td>
                                    <td>${item.pivot ? item.pivot.price : 'N/A'}</td>
                                    <td>${item.pivot ? item.pivot.from_date : 'N/A'}</td>
                                    <td>${item.pivot ? item.pivot.to_date || 'N/A' : 'N/A'}</td>
                                </tr>
                            `).join("")}
                        </tbody>
                    </table>
                `;
                accessoryList.innerHTML = table;
            }

            const accessoryModal = new bootstrap.Modal(document.getElementById('accessoryModal'));
            accessoryModal.show();
        }

        // NEW: Event listener for the Save Changes button in Adjust Payment Modal
        // Note: The ID 'saveAdjustedPaymentBtn' was not found in your provided HTML.
        // Assuming your 'editAmountForm' is the one for payment adjustments.
        const editAmountForm = document.getElementById('editAmountForm');
        if (editAmountForm) {
            editAmountForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(editAmountForm);
                const guestId = formData.get('guest_id');

                try {
                    const response = await fetch('/api/admin/modify-waiver/payments', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken()
                        }
                    });

                    const result = await response.json();
                    const errorBox = document.getElementById('editAmountErrors');
                    errorBox.textContent = ''; // Clear previous errors

                    if (result.success) {
                        showCustomMessageBox(result.message || 'Payment updated successfully.', 'success');
                        editAmountModal.hide(); // Hide the modal
                        fetchPendingGuests(); // Refresh the table
                    } else {
                        errorBox.textContent = result.message || 'Something went wrong.';
                        // Display validation errors if any
                        if (result.errors) {
                            for (const key in result.errors) {
                                errorBox.innerHTML += `<br>${result.errors[key].join(', ')}`;
                            }
                        }
                    }

                } catch (error) {
                    console.error('Error:', error);
                    document.getElementById('editAmountErrors').innerText = 'An unexpected error occurred. Please try again.';
                }
            });
        }

        // Approve fee waiver function (opens the payment adjustment modal)
        window.approveFeeWaiver = function(guestId) {
            showAdjustPaymentModal(guestId);
        };

        // Function to show the Adjust Payment Modal
        window.showAdjustPaymentModal = function(guestId) {
            const guestIdInput = document.getElementById('edit_guest_id');
            const hostelFeeInput = document.getElementById('hostel_fee');
            const cautionMoneyInput = document.getElementById('caution_money');
            const facilityInput = document.getElementById('facility');
            const remarksInput = document.getElementById('remarks');
            const monthsInput = document.getElementById('months');
            const daysInput = document.getElementById('days');
            const approvedByInput = document.getElementById('approved_by');
            const errorBox = document.getElementById('editAmountErrors');
            const documentInput = document.getElementById('document'); // For document upload field

            // Clear previous values and errors
            guestIdInput.value = guestId;
            hostelFeeInput.value = '';
            cautionMoneyInput.value = '';
            facilityInput.value = '';
            remarksInput.value = '';
            monthsInput.value = '';
            daysInput.value = '';
            approvedByInput.value = '';
            documentInput.value = ''; // Clear file input
            errorBox.textContent = '';
            errorBox.classList.add('d-none'); // Hide error box initially

            // Fetch current payment details for the guest
            fetch(`/api/guest/${guestId}/total-amount`) // This endpoint provides fee details
                .then(res => res.json())
                .then(response => {
                    if (response.success && response.data) {
                        const feeDetails = response.data;
                        hostelFeeInput.value = feeDetails.hostel_fee || 0;
                        cautionMoneyInput.value = feeDetails.caution_money || 0;
                        facilityInput.value = feeDetails.facility || '';
                        remarksInput.value = feeDetails.remarks || '';
                        monthsInput.value = feeDetails.months || '';
                        daysInput.value = feeDetails.days || '';
                        approvedByInput.value = feeDetails.approved_by || '';
                        // If there's an existing document to display or note
                        // You'd typically add logic here to show a link to the existing document if one exists
                    } else {
                        errorBox.classList.remove('d-none');
                        errorBox.textContent = response.message || 'Failed to load payment details for this guest.';
                    }
                })
                .catch(error => {
                    console.error('Error fetching payment details:', error);
                    errorBox.classList.remove('d-none');
                    errorBox.textContent = 'Error fetching payment details. Please try again.';
                });

            editAmountModal.show();
        };

        function hideEditAmountSection() {
            editAmountModal.hide();
        }
    });


    //new js for review reject payment
   
    // ... (Your existing getCsrfToken, showCustomMessageBox, showModalMessage functions)

    document.addEventListener("DOMContentLoaded", function() {
        fetchPendingGuests();

        let editAmountModal = new bootstrap.Modal(document.getElementById('editAmountModal'), {
            backdrop: 'static',
            keyboard: false
        });

        // NEW: Bootstrap Modal instance for the review/reject modal
        let reviewRejectModal = new bootstrap.Modal(document.getElementById('reviewRejectModal'), {
            backdrop: 'static',
            keyboard: false
        });

        // ... (Your existing approveGuest and denyGuest functions)

        // Your existing fetchPendingGuests function (no changes needed here, just ensure it exists)
        function fetchPendingGuests() {
            let guestList = document.getElementById("guestList");
            guestList.innerHTML = `<tr><td colspan="17" class="text-center">Loading pending guests...</td></tr>`;

            fetch("{{ url('/api/guests/pending') }}")
                .then(response => response.json())
                .then(response => {
                    const guests = response.data;
                    guestList.innerHTML = "";

                    if (!response.success || !Array.isArray(guests) || guests.length === 0) {
                        guestList.innerHTML = `<tr><td colspan="17" class="text-center">No pending guests found.</td></tr>`;
                        if (!response.success && response.message) {
                            showCustomMessageBox(response.message, 'danger');
                        }
                        return;
                    }

                    guests.forEach((guest, index) => {
                        let accessoriesButton = `<button class="btn btn-info btn-sm" onclick='viewAccessories(${JSON.stringify(guest.accessories || [])})'>View</button>`;
                        const feeWaiverStatus = guest.fee_waiver ? 'Yes' : 'No';
                        const remarksContent = guest.remarks || 'N/A';
                        const accountRemarkContent = guest.fee_exception && guest.fee_exception.account_remark ? guest.fee_exception.account_remark : 'N/A';
                        const guestStatusDisplay = guest.status || 'N/A';

                        let attachmentLink = 'N/A';
                        if (guest.attachment_path) {
                            attachmentLink = `<a href="{{ asset('storage/') }}/${guest.attachment_path}" target="_blank" class="btn btn-sm btn-secondary">View</a>`;
                        }

                        let actionButtons = '';

                        if (guest.status === 'pending') {
                            actionButtons = `
                                <button class="btn btn-success btn-sm mb-1" onclick="approveGuest(${guest.id})">Approve</button>
                                <button class="btn btn-danger btn-sm mb-1" onclick="denyGuest(${guest.id})">Deny</button>
                            `;
                            if (guest.fee_waiver) {
                                if (!guest.fee_waiver_approved) {
                                    actionButtons += `<button class="btn btn-primary btn-sm mb-1" onclick="showAdjustPaymentModal(${guest.id})">Approve Fee Waiver</button>`;
                                }
                            }
                        }

                        if (guest.status === 'accountant_reject') {
                            // Changed this button to call the new function for the new modal
                            actionButtons += `
                                <button class="btn btn-warning btn-sm mb-1" onclick="showReviewRejectModal(${guest.id})">
                                    Review Rejected Status
                                </button>
                            `;
                        }

                        guestList.innerHTML += `
                            <tr id="guest-${guest.id}">
                                <td>${index + 1}</td>
                                <td>${guest.scholar_no || 'N/A'}</td>
                                <td>${guest.name || '-'}</td>
                                <td>${guest.fathers_name || 'N/A'}</td>
                                <td>${guest.mothers_name || 'N/A'}</td>
                                <td>${guest.local_guardian_name || 'N/A'}</td>
                                <td>${guest.emergency_no || 'N/A'}</td>
                                <td>${guest.gender || '-'}</td>
                                <td>${guest.room_preference || 'N/A'}</td>
                                <td>${guest.food_preference || 'N/A'}</td>
                                <td>${feeWaiverStatus}</td>
                                <td>${accountRemarkContent}</td>
                                <td>${guestStatusDisplay}</td>
                                <td>${remarksContent}</td>
                                <td>${attachmentLink}</td>
                                <td>${accessoriesButton}</td>
                                <td>${actionButtons}</td>
                            </tr>
                        `;
                    });
                })
                .catch(error => {
                    console.error('Error fetching guests:', error);
                    guestList.innerHTML = `<tr><td colspan="17" class="text-center text-danger">Failed to load guests.</td></tr>`;
                    showCustomMessageBox('Failed to load pending guests.', 'danger');
                });
        }

        // ... (Your existing viewAccessories function)

        // Existing event listener for the original editAmountForm (keep this)
        const editAmountForm = document.getElementById('editAmountForm');
        if (editAmountForm) {
            editAmountForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(editAmountForm);
                // Use the same API endpoint for submission as before
                try {
                    const response = await fetch('/api/admin/modify-waiver/payments', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken()
                        }
                    });
                    const result = await response.json();
                    const errorBox = document.getElementById('editAmountErrors');
                    errorBox.textContent = '';
                    if (result.success) {
                        showCustomMessageBox(result.message || 'Payment updated successfully.', 'success');
                        editAmountModal.hide();
                        fetchPendingGuests();
                    } else {
                        errorBox.textContent = result.message || 'Something went wrong.';
                        if (result.errors) {
                            for (const key in result.errors) {
                                errorBox.innerHTML += `<br>${result.errors[key].join(', ')}`;
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    document.getElementById('editAmountErrors').innerText = 'An unexpected error occurred. Please try again.';
                }
            });
        }

        // NEW: Event listener for the reviewRejectForm
        const reviewRejectForm = document.getElementById('reviewRejectForm');
        if (reviewRejectForm) {
            reviewRejectForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(reviewRejectForm); // Use the new form ID
                // Still submits to the same API endpoint for modification
                try {
                    const response = await fetch('/api/admin/modify-waiver/payments', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken()
                        }
                    });
                    const result = await response.json();
                    const errorBox = document.getElementById('reviewAmountErrors'); // Use the new error box ID
                    errorBox.textContent = '';
                    if (result.success) {
                        showCustomMessageBox(result.message || 'Rejected payment reviewed and updated successfully.', 'success');
                        reviewRejectModal.hide(); // Hide the new modal
                        fetchPendingGuests(); // Refresh the table
                    } else {
                        errorBox.textContent = result.message || 'Review update failed.';
                        if (result.errors) {
                            for (const key in result.errors) {
                                errorBox.innerHTML += `<br>${result.errors[key].join(', ')}`;
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    document.getElementById('reviewAmountErrors').innerText = 'An unexpected error occurred. Please try again.';
                }
            });
        }

        // Your existing approveFeeWaiver function (still opens the original adjust payment modal)
        window.approveFeeWaiver = function(guestId) {
            showAdjustPaymentModal(guestId);
        };

        // Your existing showAdjustPaymentModal function (remains for 'Approve Fee Waiver')
        window.showAdjustPaymentModal = function(guestId) {
            const guestIdInput = document.getElementById('edit_guest_id');
            const hostelFeeInput = document.getElementById('hostel_fee');
            const cautionMoneyInput = document.getElementById('caution_money');
            const monthsInput = document.getElementById('months');
            const daysInput = document.getElementById('days');
            const facilityInput = document.getElementById('facility');
            const remarksInput = document.getElementById('remarks');
            const approvedByInput = document.getElementById('approved_by');
            const currentDocumentInfo = document.getElementById('currentDocumentInfo');
            const editAmountErrors = document.getElementById('editAmountErrors');
            const calculatedTotalDisplay = document.getElementById('calculated_total_display');

            // Clear previous values and errors
            guestIdInput.value = guestId;
            hostelFeeInput.value = '';
            cautionMoneyInput.value = '';
            monthsInput.value = '';
            daysInput.value = '';
            facilityInput.value = '';
            remarksInput.value = '';
            approvedByInput.value = '';
            currentDocumentInfo.innerHTML = 'No document uploaded.';
            editAmountErrors.textContent = '';
            editAmountErrors.classList.add('d-none');
            if (calculatedTotalDisplay) calculatedTotalDisplay.textContent = '0.00';

            const updateCalculatedTotal = () => {
                const hFee = parseFloat(hostelFeeInput.value) || 0;
                const cMoney = parseFloat(cautionMoneyInput.value) || 0;
                if (calculatedTotalDisplay) {
                    calculatedTotalDisplay.textContent = (hFee + cMoney).toFixed(2);
                }
            };
            hostelFeeInput.addEventListener('input', updateCalculatedTotal);
            cautionMoneyInput.addEventListener('input', updateCalculatedTotal);

            fetch(`/api/guest/${guestId}/fee-exception-details`)
                .then(res => res.json())
                .then(response => {
                    if (response.success && response.data) {
                        const feeExceptionDetails = response.data;
                        hostelFeeInput.value = feeExceptionDetails.hostel_fee || '';
                        cautionMoneyInput.value = feeExceptionDetails.caution_money || '';
                        monthsInput.value = feeExceptionDetails.months || '';
                        daysInput.value = feeExceptionDetails.days || '';
                        facilityInput.value = feeExceptionDetails.facility || '';
                        remarksInput.value = feeExceptionDetails.remarks || '';
                        approvedByInput.value = feeExceptionDetails.approved_by || '';
                        updateCalculatedTotal();

                        if (feeExceptionDetails.document_url) {
                            currentDocumentInfo.innerHTML = `
                                Current Document:
                                <button class="btn btn-secondary btn-sm" onclick="window.open('${feeExceptionDetails.document_url}', '_blank')">
                                    View Document
                                </button>
                            `;
                        } else {
                            currentDocumentInfo.textContent = 'No document uploaded.';
                        }
                    } else if (!response.success && response.message) {
                        editAmountErrors.classList.remove('d-none');
                        editAmountErrors.textContent = response.message || 'No existing fee exception details found for this guest.';
                    } else {
                        editAmountErrors.classList.remove('d-none');
                        editAmountErrors.textContent = 'Failed to load fee exception details for this guest.';
                    }
                })
                .catch(error => {
                    console.error('Error fetching fee exception details:', error);
                    editAmountErrors.classList.remove('d-none');
                    editAmountErrors.textContent = 'Error fetching fee exception details. Please try again.';
                });

            editAmountModal.show();
        };

        // NEW: Function to show the Review Reject Modal
        window.showReviewRejectModal = function(guestId) {
            const guestIdInput = document.getElementById('review_guest_id');
            const createdByInput = document.getElementById('review_created_by');
            const hostelFeeInput = document.getElementById('review_hostel_fee');
            const cautionMoneyInput = document.getElementById('review_caution_money');
            const monthsInput = document.getElementById('review_months');
            const daysInput = document.getElementById('review_days');
            const facilityInput = document.getElementById('review_facility');
            const remarksInput = document.getElementById('review_remarks'); // This will be the fee_exception remarks
            const approvedByInput = document.getElementById('review_approved_by');
            const currentDocumentInfo = document.getElementById('review_currentDocumentInfo');
            const reviewAmountErrors = document.getElementById('reviewAmountErrors');
            const calculatedTotalDisplay = document.getElementById('review_calculated_total_display');

            // Clear previous values and errors
            guestIdInput.value = guestId;
            // createdByInput.value is already set by blade, no need to touch here unless it's dynamic
            hostelFeeInput.value = '';
            cautionMoneyInput.value = '';
            monthsInput.value = '';
            daysInput.value = '';
            facilityInput.value = '';
            remarksInput.value = '';
            approvedByInput.value = '';
            currentDocumentInfo.innerHTML = 'No document uploaded.';
            reviewAmountErrors.textContent = '';
            reviewAmountErrors.classList.add('d-none');
            if (calculatedTotalDisplay) calculatedTotalDisplay.textContent = '0.00';

            const updateCalculatedTotal = () => {
                const hFee = parseFloat(hostelFeeInput.value) || 0;
                const cMoney = parseFloat(cautionMoneyInput.value) || 0;
                if (calculatedTotalDisplay) {
                    calculatedTotalDisplay.textContent = (hFee + cMoney).toFixed(2);
                }
            };
            hostelFeeInput.addEventListener('input', updateCalculatedTotal);
            cautionMoneyInput.addEventListener('input', updateCalculatedTotal);

            // Fetch fee exception details from the dedicated API
            fetch(`/api/guest/${guestId}/fee-exception-details`)
                .then(res => res.json())
                .then(response => {
                    if (response.success && response.data) {
                        const feeExceptionDetails = response.data;
                        hostelFeeInput.value = feeExceptionDetails.hostel_fee || '';
                        cautionMoneyInput.value = feeExceptionDetails.caution_money || '';
                        monthsInput.value = feeExceptionDetails.months || ''; // From Guest table
                        daysInput.value = feeExceptionDetails.days || '';     // From Guest table
                        facilityInput.value = feeExceptionDetails.facility || '';
                        remarksInput.value = feeExceptionDetails.remarks || ''; // This is the 'remarks' from fee_exceptions
                        approvedByInput.value = feeExceptionDetails.approved_by || '';
                        updateCalculatedTotal();

                        if (feeExceptionDetails.document_url) {
                            currentDocumentInfo.innerHTML = `
                                Current Document:
                                <button class="btn btn-secondary btn-sm" onclick="window.open('${feeExceptionDetails.document_url}', '_blank')">
                                    View Document
                                </button>
                            `;
                        } else {
                            currentDocumentInfo.textContent = 'No document uploaded.';
                        }
                    } else if (!response.success && response.message) {
                        reviewAmountErrors.classList.remove('d-none');
                        reviewAmountErrors.textContent = response.message || 'No existing fee exception details found for this guest. You can enter new values.';
                    } else {
                        reviewAmountErrors.classList.remove('d-none');
                        reviewAmountErrors.textContent = 'Failed to load fee exception details for this guest.';
                    }
                })
                .catch(error => {
                    console.error('Error fetching fee exception details for review:', error);
                    reviewAmountErrors.classList.remove('d-none');
                    reviewAmountErrors.textContent = 'Error fetching fee exception details. Please try again.';
                });

            reviewRejectModal.show(); // Show the new modal
        };
    });

// ... (rest of your JavaScript code, including the submit listener for editAmountForm)
</script> -->


<script>
    /**
     * Retrieves the CSRF token from the meta tag.
     * @returns {string|null} The CSRF token or null if not found.
     */
    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : null;
    }

    /**
     * Shows a custom message box at a specified target element.
     * The message will automatically disappear after 3 seconds.
     * @param {string} message - The message to display.
     * @param {'info'|'success'|'warning'|'danger'} type - The type of alert (e.g., 'success', 'danger').
     * @param {string} targetElementId - The ID of the container element where the message should be displayed.
     */
    function showCustomMessageBox(message, type = 'info', targetElementId = 'mainResponseMessage') {
        const messageContainer = document.getElementById(targetElementId);
        if (messageContainer) {
            messageContainer.innerHTML = ""; // Clear previous messages
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            messageContainer.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000); // Remove after 3 seconds
        } else {
            console.warn(`Message container #${targetElementId} not found.`);
        }
    }

    /**
     * Shows a message within a modal, typically for feedback inside a form.
     * @param {string} message - The message to display.
     * @param {'info'|'success'|'warning'|'danger'} type - The type of alert.
     * @param {string} targetElementId - The ID of the container element within the modal.
     */
    function showModalMessage(message, type = 'info', targetElementId = 'adjustPaymentMessage') {
        const messageContainer = document.getElementById(targetElementId);
        if (messageContainer) {
            messageContainer.innerHTML = "";
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            messageContainer.appendChild(alertDiv);
        } else {
            console.warn(`Message container #${targetElementId} not found.`);
        }
    }

    /**
     * Fetches and displays the list of pending guests in the table.
     */
    function fetchPendingGuests() {
        const guestList = document.getElementById("guestList");
        if (!guestList) {
            console.error("Guest list table body element #guestList not found.");
            return;
        }

        guestList.innerHTML = `<tr><td colspan="17" class="text-center">Loading pending guests...</td></tr>`;

        fetch("{{ url('/api/guests/pending') }}")
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(response => {
                const guests = response.data;
                guestList.innerHTML = ""; // Clear loading message

                if (!response.success || !Array.isArray(guests) || guests.length === 0) {
                    guestList.innerHTML = `<tr><td colspan="17" class="text-center">No pending guests found.</td></tr>`;
                    if (!response.success && response.message) {
                        showCustomMessageBox(response.message, 'danger');
                    }
                    return;
                }

                guests.forEach((guest, index) => {
                    const accessoriesButton = `<button class="btn btn-info btn-sm" onclick='viewAccessories(${JSON.stringify(guest.accessories || [])})'>View</button>`;
                    const feeWaiverStatus = guest.fee_waiver ? 'Yes' : 'No';
                    const remarksContent = guest.remarks || 'N/A'; // General guest remarks
                    const accountRemarkContent = guest.fee_exception && guest.fee_exception.account_remark ? guest.fee_exception.account_remark : 'N/A';
                    const guestStatusDisplay = guest.status || 'N/A';

                    let attachmentLink = 'N/A';
                    if (guest.attachment_path) {
                        // FIX 2: Ensure /storage/ is prepended only once.
                        // Assuming guest.attachment_path is relative (e.g., 'uploads/file.pdf')
                        attachmentLink = `<a href="/storage/${guest.attachment_path}" target="_blank" class="btn btn-sm btn-secondary">View</a>`;
                    }

                    let actionButtons = '';
                    if (guest.status === 'pending') {
                        actionButtons = `
                            <button class="btn btn-success btn-sm mb-1" onclick="approveGuest(${guest.id})">Process</button>
                            <button class="btn btn-danger btn-sm mb-1" onclick="denyGuest(${guest.id})">Reject</button>
                        `;
                        if (guest.fee_waiver && !guest.fee_waiver_approved) {
                            actionButtons += `
                                <button class="btn btn-primary btn-sm mb-1" onclick="approveFeeWaiver(${guest.id})">Approve Fee Waiver</button>
                            `;
                        }
                    } else if (guest.status === 'accountant_reject') {
                        actionButtons += `
                            <button class="btn btn-warning btn-sm mb-1" onclick="showReviewRejectModal(${guest.id})">
                                Review Rejected Status
                            </button>
                        `;
                    }

                    guestList.innerHTML += `
                        <tr id="guest-${guest.id}">
                            <td>${index + 1}</td>
                            <td>${guest.scholar_no || 'N/A'}</td>
                            <td>${guest.name || '-'}</td>
                            <td>${guest.fathers_name || 'N/A'}</td>
                            <td>${guest.mothers_name || 'N/A'}</td>
                            <td>${guest.local_guardian_name || 'N/A'}</td>
                            <td>${guest.emergency_no || 'N/A'}</td>
                            <td>${guest.gender || '-'}</td>
                            <td>${guest.room_preference || 'N/A'}</td>
                            <td>${guest.food_preference || 'N/A'}</td>
                            <td>${feeWaiverStatus}</td>
                            <td>${accountRemarkContent}</td>
                            <td>${guestStatusDisplay}</td>
                            <td>${remarksContent}</td>
                            <td>${attachmentLink}</td>
                            <td>${accessoriesButton}</td>
                            <td>${actionButtons}</td>
                        </tr>
                    `;
                });
            })
            .catch(error => {
                console.error('Error fetching guests:', error);
                guestList.innerHTML = `<tr><td colspan="17" class="text-center text-danger">Failed to load guests.</td></tr>`;
                showCustomMessageBox('Failed to load pending guests.', 'danger');
            });
    }

    /**
     * Sends a request to approve a guest.
     * @param {number} guestId - The ID of the guest to approve.
     */
    window.approveGuest = function(guestId) {
        fetch("{{ url('/api/admin/approved-guest') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    guest_id: guestId
                })
            })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    showCustomMessageBox(response.message || "Guest approved successfully.", 'success');
                    fetchPendingGuests();
                } else {
                    showCustomMessageBox(response.message || "Approval failed.", 'danger');
                }
            })
            .catch(error => {
                console.error('Error approving guest:', error);
                showCustomMessageBox('An error occurred during approval.', 'danger');
            });
    };

    /**
     * Sends a request to deny/reject a guest.
     * @param {number} guestId - The ID of the guest to deny.
     */
    window.denyGuest = function(guestId) {
        fetch("{{ url('/api/payment/reject') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    guest_id: guestId
                })
            })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    showCustomMessageBox(response.message || "Guest rejected successfully.", 'success');
                    fetchPendingGuests();
                } else {
                    showCustomMessageBox(response.message || "Rejection failed.", 'danger');
                }
            })
            .catch(error => {
                console.error('Error denying guest:', error);
                showCustomMessageBox('An error occurred during rejection.', 'danger');
            });
    };

    /**
     * Displays a modal with accessories associated with a guest.
     * @param {Array<Object>} accessories - An array of accessory objects.
     */
    window.viewAccessories = function(accessories) {
        const accessoryList = document.getElementById("accessoryList");
        if (!accessoryList) {
            console.error("Accessory list element #accessoryList not found.");
            return;
        }
        accessoryList.innerHTML = ""; // Clear previous content

        if (!Array.isArray(accessories) || accessories.length === 0) {
            accessoryList.innerHTML = "<p>No accessories found for this guest.</p>";
        } else {
            let table = `
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Price</th>
                            <th>From Date</th>
                            <th>To Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${accessories.map(item => `
                            <tr>
                                <td>${item.accessory_head ? item.accessory_head.name : 'N/A'}</td>
                                <td>${item.pivot ? item.pivot.price : 'N/A'}</td>
                                <td>${item.pivot ? item.pivot.from_date : 'N/A'}</td>
                                <td>${item.pivot ? item.pivot.to_date || 'N/A' : 'N/A'}</td>
                            </tr>
                        `).join("")}
                    </tbody>
                </table>
            `;
            accessoryList.innerHTML = table;
        }

        const accessoryModal = new bootstrap.Modal(document.getElementById('accessoryModal'));
        accessoryModal.show();
    };

    // Global modal instances for easier access
    let editAmountModal;
    let reviewRejectModal;

    document.addEventListener("DOMContentLoaded", function() {
        // Initialize Bootstrap Modals
        editAmountModal = new bootstrap.Modal(document.getElementById('editAmountModal'), {
            backdrop: 'static',
            keyboard: false
        });

        reviewRejectModal = new bootstrap.Modal(document.getElementById('reviewRejectModal'), {
            backdrop: 'static',
            keyboard: false
        });

        // Initial fetch of pending guests when the page loads
        fetchPendingGuests();

        /**
         * Event listener for the 'Adjust Payment' form submission.
         * Handles the modification of waiver/payment details.
         */
        const editAmountForm = document.getElementById('editAmountForm');
        if (editAmountForm) {
            editAmountForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(editAmountForm);
                // The 'guest_id' should be part of your form fields, e.g., <input type="hidden" name="guest_id" id="edit_guest_id">

                try {
                    const response = await fetch('/api/admin/modify-waiver/payments', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken()
                        }
                    });

                    const result = await response.json();
                    const errorBox = document.getElementById('editAmountErrors');
                    if (errorBox) {
                        errorBox.textContent = ''; // Clear previous errors
                        errorBox.classList.add('d-none'); // Hide error box
                    }

                    if (result.success) {
                        showCustomMessageBox(result.message || 'Payment updated successfully.', 'success');
                        editAmountModal.hide(); // Hide the modal
                        fetchPendingGuests(); // Refresh the table
                    } else {
                        if (errorBox) {
                            errorBox.classList.remove('d-none'); // Show error box
                            errorBox.textContent = result.message || 'Something went wrong.';
                            // Display validation errors if any
                            if (result.errors) {
                                for (const key in result.errors) {
                                    errorBox.innerHTML += `<br>${result.errors[key].join(', ')}`;
                                }
                            }
                        } else {
                             showCustomMessageBox(result.message || 'Something went wrong.', 'danger');
                        }
                    }

                } catch (error) {
                    console.error('Error submitting editAmountForm:', error);
                    const errorBox = document.getElementById('editAmountErrors');
                    if (errorBox) {
                        errorBox.classList.remove('d-none');
                        errorBox.innerText = 'An unexpected error occurred. Please try again.';
                    } else {
                        showCustomMessageBox('An unexpected error occurred. Please try again.', 'danger');
                    }
                }
            });
        }

        /**
         * Event listener for the 'Review Rejected Payment' form submission.
         * Handles the modification of waiver/payment details after a rejection.
         */
        const reviewRejectForm = document.getElementById('reviewRejectForm');
        if (reviewRejectForm) {
            reviewRejectForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(reviewRejectForm);

                try {
                    const response = await fetch('/api/admin/modify-waiver/payments', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken()
                        }
                    });
                    const result = await response.json();
                    const errorBox = document.getElementById('reviewAmountErrors');
                    if (errorBox) {
                        errorBox.textContent = ''; // Clear previous errors
                        errorBox.classList.add('d-none'); // Hide error box
                    }

                    if (result.success) {
                        showCustomMessageBox(result.message || 'Rejected payment reviewed and updated successfully.', 'success');
                        reviewRejectModal.hide(); // Hide the new modal
                        fetchPendingGuests(); // Refresh the table
                    } else {
                        if (errorBox) {
                            errorBox.classList.remove('d-none'); // Show error box
                            errorBox.textContent = result.message || 'Review update failed.';
                            if (result.errors) {
                                for (const key in result.errors) {
                                    errorBox.innerHTML += `<br>${result.errors[key].join(', ')}`;
                                }
                            }
                        } else {
                             showCustomMessageBox(result.message || 'Review update failed.', 'danger');
                        }
                    }
                } catch (error) {
                    console.error('Error submitting reviewRejectForm:', error);
                    const errorBox = document.getElementById('reviewAmountErrors');
                    if (errorBox) {
                        errorBox.classList.remove('d-none');
                        errorBox.innerText = 'An unexpected error occurred. Please try again.';
                    } else {
                        showCustomMessageBox('An unexpected error occurred. Please try again.', 'danger');
                    }
                }
            });
        }

        /**
         * Approves a fee waiver by opening the payment adjustment modal.
         * This function essentially triggers the 'showAdjustPaymentModal' for a specific guest,
         * indicating that approving a waiver might involve an administrative adjustment to fees.
         * @param {number} guestId - The ID of the guest for whom to approve the fee waiver.
         */
        window.approveFeeWaiver = function(guestId) {
            showAdjustPaymentModal(guestId);
        };

        /**
         * Shows the Adjust Payment Modal and pre-fills it with guest's current fee exception details.
         * This modal is primarily for approving fee waivers or general payment adjustments.
         * @param {number} guestId - The ID of the guest whose payment details are to be adjusted.
         */
        window.showAdjustPaymentModal = function(guestId) {
            const guestIdInput = document.getElementById('edit_guest_id');
            const hostelFeeInput = document.getElementById('hostel_fee');
            const cautionMoneyInput = document.getElementById('caution_money');
            const monthsInput = document.getElementById('months');
            const daysInput = document.getElementById('days');
            const facilityInput = document.getElementById('facility');
            const remarksInput = document.getElementById('remarks');
            const approvedByInput = document.getElementById('approved_by');
            const currentDocumentInfo = document.getElementById('currentDocumentInfo');
            const editAmountErrors = document.getElementById('editAmountErrors');
            const calculatedTotalDisplay = document.getElementById('calculated_total_display');

            // Clear previous values and errors
            if (guestIdInput) guestIdInput.value = guestId;
            if (hostelFeeInput) hostelFeeInput.value = '';
            if (cautionMoneyInput) cautionMoneyInput.value = '';
            if (monthsInput) monthsInput.value = '';
            if (daysInput) daysInput.value = '';
            if (facilityInput) facilityInput.value = '';
            if (remarksInput) remarksInput.value = '';
            if (approvedByInput) approvedByInput.value = '';
            if (currentDocumentInfo) currentDocumentInfo.innerHTML = '';
            if (editAmountErrors) {
                editAmountErrors.textContent = '';
                editAmountErrors.classList.add('d-none');
            }
            if (calculatedTotalDisplay) calculatedTotalDisplay.textContent = '0.00';

            // Function to update the displayed total
            const updateCalculatedTotal = () => {
                const hFee = parseFloat(hostelFeeInput.value) || 0;
                const cMoney = parseFloat(cautionMoneyInput.value) || 0;
                if (calculatedTotalDisplay) {
                    calculatedTotalDisplay.textContent = (hFee + cMoney).toFixed(2);
                }
            };

            // Add event listeners for instant calculation updates
            if (hostelFeeInput) hostelFeeInput.addEventListener('input', updateCalculatedTotal);
            if (cautionMoneyInput) cautionMoneyInput.addEventListener('input', updateCalculatedTotal);

            // Fetch fee exception details from the dedicated API
            fetch(`/api/guest/${guestId}/fee-exception-details`)
                .then(res => res.json())
                .then(response => {
                    if (response.success && response.data) {
                        const feeExceptionDetails = response.data;
                        if (hostelFeeInput) hostelFeeInput.value = feeExceptionDetails.hostel_fee || '';
                        if (cautionMoneyInput) cautionMoneyInput.value = feeExceptionDetails.caution_money || '';
                        if (monthsInput) monthsInput.value = feeExceptionDetails.months || '';
                        if (daysInput) daysInput.value = feeExceptionDetails.days || '';
                        if (facilityInput) facilityInput.value = feeExceptionDetails.facility || '';
                        if (remarksInput) remarksInput.value = feeExceptionDetails.remarks || '';
                        if (approvedByInput) approvedByInput.value = feeExceptionDetails.approved_by || '';
                        updateCalculatedTotal(); // Update total after populating fields

                        if (feeExceptionDetails.document_url && currentDocumentInfo) {
                            // FIX 1: Add type="button" to prevent form submission.
                            // FIX 2: Ensure /storage/ is prepended only once.
                            // Assuming feeExceptionDetails.document_url is relative (e.g., 'waiver_docs/file.pdf')
                            // currentDocumentInfo.innerHTML = `
                            //     Current Document:
                            //     <button type="button" class="btn btn-secondary btn-sm" onclick="window.open('/storage/${feeExceptionDetails.document_url}', '_blank')">
                            //         View Document
                            //     </button>
                            // `;
                        } else if (currentDocumentInfo) {
                            currentDocumentInfo.textContent = '';
                        }
                    } else if (!response.success && response.message && editAmountErrors) {
                        editAmountErrors.classList.remove('d-none');
                        editAmountErrors.textContent = response.message || 'No existing fee exception details found for this guest.';
                    } else if (editAmountErrors) {
                        editAmountErrors.classList.remove('d-none');
                        editAmountErrors.textContent = 'Failed to load fee exception details for this guest.';
                    }
                })
                .catch(error => {
                    console.error('Error fetching fee exception details:', error);
                    if (editAmountErrors) {
                        editAmountErrors.classList.remove('d-none');
                        editAmountErrors.textContent = 'Error fetching fee exception details. Please try again.';
                    }
                });

            editAmountModal.show();
        };

        /**
         * Shows the Review Reject Modal and pre-fills it with guest's fee exception details
         * for reviewing a previously rejected status.
         * @param {number} guestId - The ID of the guest whose rejected status is to be reviewed.
         */
        window.showReviewRejectModal = function(guestId) {
            const guestIdInput = document.getElementById('review_guest_id');
            // const createdByInput = document.getElementById('review_created_by'); // Assumed to be static/blade set
            const hostelFeeInput = document.getElementById('review_hostel_fee');
            const cautionMoneyInput = document.getElementById('review_caution_money');
            const monthsInput = document.getElementById('review_months');
            const daysInput = document.getElementById('review_days');
            const facilityInput = document.getElementById('review_facility');
            const remarksInput = document.getElementById('review_remarks'); // This will be the fee_exception remarks
            const approvedByInput = document.getElementById('review_approved_by');
            const currentDocumentInfo = document.getElementById('review_currentDocumentInfo');
            const reviewAmountErrors = document.getElementById('reviewAmountErrors');
            const calculatedTotalDisplay = document.getElementById('review_calculated_total_display');

            // Clear previous values and errors
            if (guestIdInput) guestIdInput.value = guestId;
            if (hostelFeeInput) hostelFeeInput.value = '';
            if (cautionMoneyInput) cautionMoneyInput.value = '';
            if (monthsInput) monthsInput.value = '';
            if (daysInput) daysInput.value = '';
            if (facilityInput) facilityInput.value = '';
            if (remarksInput) remarksInput.value = '';
            if (approvedByInput) approvedByInput.value = '';
            if (currentDocumentInfo) currentDocumentInfo.innerHTML = '';
            if (reviewAmountErrors) {
                reviewAmountErrors.textContent = '';
                reviewAmountErrors.classList.add('d-none');
            }
            if (calculatedTotalDisplay) calculatedTotalDisplay.textContent = '0.00';

            // Function to update the displayed total
            const updateCalculatedTotal = () => {
                const hFee = parseFloat(hostelFeeInput.value) || 0;
                const cMoney = parseFloat(cautionMoneyInput.value) || 0;
                if (calculatedTotalDisplay) {
                    calculatedTotalDisplay.textContent = (hFee + cMoney).toFixed(2);
                }
            };
            // Add event listeners for instant calculation updates
            if (hostelFeeInput) hostelFeeInput.addEventListener('input', updateCalculatedTotal);
            if (cautionMoneyInput) cautionMoneyInput.addEventListener('input', updateCalculatedTotal);

            // Fetch fee exception details from the dedicated API
            fetch(`/api/guest/${guestId}/fee-exception-details`)
                .then(res => res.json())
                .then(response => {
                    if (response.success && response.data) {
                        const feeExceptionDetails = response.data;
                        if (hostelFeeInput) hostelFeeInput.value = feeExceptionDetails.hostel_fee || '';
                        if (cautionMoneyInput) cautionMoneyInput.value = feeExceptionDetails.caution_money || '';
                        if (monthsInput) monthsInput.value = feeExceptionDetails.months || '';
                        if (daysInput) daysInput.value = feeExceptionDetails.days || '';
                        if (facilityInput) facilityInput.value = feeExceptionDetails.facility || '';
                        if (remarksInput) remarksInput.value = feeExceptionDetails.remarks || '';
                        if (approvedByInput) approvedByInput.value = feeExceptionDetails.approved_by || '';
                        updateCalculatedTotal(); // Update total after populating fields

                        if (feeExceptionDetails.document_url && currentDocumentInfo) {
                            // FIX 1: Add type="button" to prevent form submission.
                            // FIX 2: Ensure /storage/ is prepended only once.
                            // Assuming feeExceptionDetails.document_url is relative (e.g., 'waiver_docs/file.pdf')
                            // currentDocumentInfo.innerHTML = `
                            //     Current Document:
                            //     <button type="button" class="btn btn-secondary btn-sm" onclick="window.open('/storage/${feeExceptionDetails.document_url}', '_blank')">
                            //         View Document
                            //     </button>
                            // `;
                        } else if (currentDocumentInfo) {
                            currentDocumentInfo.textContent = '';
                        }
                    } else if (!response.success && response.message && reviewAmountErrors) {
                        reviewAmountErrors.classList.remove('d-none');
                        reviewAmountErrors.textContent = response.message || 'No existing fee exception details found for this guest. You can enter new values.';
                    } else if (reviewAmountErrors) {
                        reviewAmountErrors.classList.remove('d-none');
                        reviewAmountErrors.textContent = 'Failed to load fee exception details for this guest.';
                    }
                })
                .catch(error => {
                    console.error('Error fetching fee exception details for review:', error);
                    if (reviewAmountErrors) {
                        reviewAmountErrors.classList.remove('d-none');
                        reviewAmountErrors.textContent = 'Error fetching fee exception details. Please try again.';
                    }
                });

            reviewRejectModal.show();
        };

        /**
         * Hides the edit amount modal.
         */
        window.hideEditAmountSection = function() {
            editAmountModal.hide();
        };
    });
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection