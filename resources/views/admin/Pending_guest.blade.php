@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h3>Pending Guest Requests</h3>

    <div id="mainResponseMessage" class="mt-3"></div> {{-- Message container for the page --}}

    <table class="table table-bordered mt-3">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
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
                <th>Current Payable</th> {{-- NEW COLUMN: To display current payable amount --}}
                <th>Remarks</th>
                <th>Attachment</th>
                <th>Accessories</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="guestList">
            {{-- Updated colspan to 16 for loading/no guests messages (12 original + 3 new + 1 Current Payable) --}}
            <tr>
                <td colspan="16" class="text-center">Loading pending guests...</td>
            </tr>
        </tbody>
    </table>


    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <!-- Edit amount  -->
    <!-- <div id="editAmountSection" style="display: none;" class="border p-4 mt-4 bg-light rounded">
        <h5>Edit Payment Amount</h5>
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
                <button type="button" class="btn btn-secondary" onclick="hideEditAmountSection()">Cancel</button>
            </div>
        </form>
    </div> old -->



    <!-- Bootstrap Modal -->
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



<meta name="csrf-token" content="{{ csrf_token() }}">


<script>


            //Approve Guest Function
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
                    console.error("Error approving guest:", error);
                    showCustomMessageBox("An error occurred during approval.", 'danger');
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
                    console.error("Error denying guest:", error);
                    showCustomMessageBox("An error occurred during rejection.", 'danger');
                });
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

    // Function to show a message within the modal
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

    document.addEventListener("DOMContentLoaded", function() {
        fetchPendingGuests();

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        function fetchPendingGuests() {
            let guestList = document.getElementById("guestList");
            // Updated colspan to 16 here as well
            guestList.innerHTML = `<tr><td colspan="16" class="text-center">Loading pending guests...</td></tr>`;

            fetch("{{ url('/api/guests/pending') }}")
                .then(response => response.json())
                .then(response => {
                    const guests = response.data;
                    guestList.innerHTML = "";

                    // Updated colspan to 16 for "No pending guests found."
                    if (!response.success || !Array.isArray(guests) || guests.length === 0) {
                        guestList.innerHTML = `<tr><td colspan="16" class="text-center">No pending guests found.</td></tr>`;
                        if (!response.success && response.message) {
                            showCustomMessageBox(response.message, 'danger');
                        }
                        return;
                    }

                    guests.forEach(guest => {
                        let accessoriesButton = `<button class="btn btn-info btn-sm" onclick='viewAccessories(${JSON.stringify(guest.accessories || [])})'>View</button>`;

                        const feeWaiverStatus = guest.fee_waiver ? 'Yes' : 'No';
                        // Display the guest's current payable amount. Default to 'N/A' or 'Not Set' if null.
                        const currentPayableDisplay = guest.payable_amount !== null && guest.payable_amount !== undefined ? guest.payable_amount : 'Not Set';
                        const remarksContent = guest.remarks || 'N/A';

                        let attachmentLink = 'N/A';
                        if (guest.attachment_path) {
                            // Assuming 'attachment_path' is relative to the public storage disk
                            attachmentLink = `<a href="{{ asset('storage/') }}/${guest.attachment_path}" target="_blank" class="btn btn-sm btn-secondary">View</a>`;
                        }

                        let actionButtons = '';

                        if (guest.status === 'pending') {
                            // Show approve/deny buttons for pending guests
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
                        } else if (guest.status === 'waiver_approved') {
                            // Show Edit Amount button only if status is waiver_approved
                            actionButtons = `
    <button class="btn btn-warning btn-sm mb-1" onclick="showAdjustPaymentModal(${guest.id}, '${currentPayableDisplay}', '${remarksContent}')">Edit Amount</button>
  `;
                        } else {
                            // For any other status, just show the status text
                            actionButtons = `<span class="text-muted">${guest.status.charAt(0).toUpperCase() + guest.status.slice(1)}</span>`;
                        }


                        guestList.innerHTML += `
                            <tr id="guest-${guest.id}">
                                <td>${guest.id}</td>
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
                                <td>${currentPayableDisplay}</td> {{-- Display current payable --}}
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
                    // Updated colspan to 16 here as well
                    guestList.innerHTML = `<tr><td colspan="16" class="text-center text-danger">Failed to load guests.</td></tr>`;
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
        document.getElementById('saveAdjustedPaymentBtn').addEventListener('click', function() {
            const guestId = document.getElementById('adjustGuestId').value;
            const newPayableAmount = document.getElementById('newPayableAmount').value;
            const waiverRemarks = document.getElementById('waiverRemarks').value;

            if (newPayableAmount === '' || parseFloat(newPayableAmount) < 0) {
                showModalMessage('Please enter a valid non-negative amount.', 'danger', 'adjustPaymentMessage');
                return;
            }




            fetch("{{ url('/api/admin/guests/adjust-payment') }}", { // Existing API endpoint for saving adjustment
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": getCsrfToken(),
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        guest_id: guestId,
                        new_payable_amount: newPayableAmount,
                        waiver_remarks: waiverRemarks
                    })
                })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        showModalMessage(response.message || "Payment adjusted successfully.", 'success', 'adjustPaymentMessage');
                        // Close the modal after a short delay and refresh the list
                        setTimeout(() => {
                            const modalElement = document.getElementById('adjustPaymentModal');
                            const modal = bootstrap.Modal.getInstance(modalElement);
                            if (modal) modal.hide();
                            fetchPendingGuests(); // Re-fetch to update the list with new amount
                        }, 1000);
                    } else {
                        showModalMessage(response.message || "Payment adjustment failed.", 'danger', 'adjustPaymentMessage');
                    }
                })
                .catch(error => {
                    console.error("Error adjusting payment:", error);
                    showModalMessage("An error occurred during payment adjustment.", 'danger', 'adjustPaymentMessage');
                });
        });






        document.getElementById('accessoryModal').addEventListener('hidden.bs.modal', function(event) {
            // Any cleanup needed when modal is hidden
        });
    });





    // Approve fee waiver function

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    window.approveFeeWaiver = function(guestId) {
        fetch("{{ url('/api/admin/approved-waiver') }}", {
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
                    showCustomMessageBox(response.message || "Fee waiver approved successfully.", 'success');
                    fetchPendingGuests(); // Refresh the list to update button/status
                } else {
                    showCustomMessageBox(response.message || "Fee waiver approval failed.", 'danger');
                }
            })
            .catch(error => {
                console.error("Error approving fee waiver:", error);
                showCustomMessageBox("An error occurred during fee waiver approval.", 'danger');
            });
    };






    //show form

    // Initialize Bootstrap modal variable globally
    let editAmountModal;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the modal using Bootstrap JS (make sure Bootstrap JS is included in your page)
        const modalEl = document.getElementById('editAmountModal');
        editAmountModal = new bootstrap.Modal(modalEl, {
            backdrop: 'static',
            keyboard: false
        });

        // Submit handler here remains the same...
    });

    function showAdjustPaymentModal(guestId, remarks = '') {
        const guestIdInput = document.getElementById('edit_guest_id');
        const hostelFeeInput = document.getElementById('hostel_fee');
        const cautionMoneyInput = document.getElementById('caution_money');
        const facilityInput = document.getElementById('facility');
        const remarksInput = document.getElementById('remarks');
        const monthsInput = document.getElementById('months');
        const daysInput = document.getElementById('days');
        const errorBox = document.getElementById('editAmountErrors');

        guestIdInput.value = guestId;
        remarksInput.value = remarks || '';

        hostelFeeInput.value = '';
        cautionMoneyInput.value = '';
        facilityInput.value = '';
        monthsInput.value = '';
        daysInput.value = '';
        errorBox.textContent = '';

        fetch(`/api/guest/${guestId}/total-amount`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    hostelFeeInput.value = data.data.hostel_fee || 0;
                    cautionMoneyInput.value = data.data.caution_money || 0;
                    facilityInput.value = data.data.facility || '';
                    monthsInput.value = data.data.months || '';
                    daysInput.value = data.data.days || '';
                } else {
                    alert('Failed to load payment details');
                }
            })
            .catch(() => alert('Error fetching payment details'));

        // Show modal instead of display block
        editAmountModal.show();
    }

    function hideEditAmountSection() {
        editAmountModal.hide();
    }




    //payments edit submit 
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('editAmountForm');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(form);

                try {
                    const response = await fetch('/api/admin/modify-waiver/payments', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert('Payment updated successfully');
                        location.reload();
                    } else {
                        document.getElementById('editAmountErrors').innerText = result.message || 'Something went wrong.';
                    }

                } catch (error) {
                    console.error('Error:', error);
                    document.getElementById('editAmountErrors').innerText = 'Something went wrong. Try again.';
                }
            });
        }
    });
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


@endsection