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
            <tr><td colspan="16" class="text-center">Loading pending guests...</td></tr>
        </tbody>
    </table>
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

{{-- Adjust Payment Modal --}}
<div class="modal fade" id="adjustPaymentModal" tabindex="-1" aria-labelledby="adjustPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adjustPaymentModalLabel">Adjust Guest Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="adjustPaymentForm">
                    <input type="hidden" id="adjustGuestId" name="guest_id">
                    <div class="mb-3">
                        <label for="currentPayableAmountDisplay" class="form-label">Current Payable Amount (from DB)</label>
                        <input type="text" class="form-control" id="currentPayableAmountDisplay" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="calculatedTotalAmountDisplay" class="form-label">Calculated Total Amount (System)</label>
                        <input type="text" class="form-control" id="calculatedTotalAmountDisplay" readonly>
                        <small class="form-text text-muted">This is the full amount calculated by the system without waiver.</small>
                    </div>
                    <div class="mb-3">
                        <label for="newPayableAmount" class="form-label">New Custom Payable Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="newPayableAmount" name="new_payable_amount" required min="0" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label for="waiverRemarks" class="form-label">Fee Waiver Remarks (Optional)</label>
                        <textarea class="form-control" id="waiverRemarks" name="waiver_remarks" rows="3"></textarea>
                    </div>
                    <div id="adjustPaymentMessage" class="mt-3"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveAdjustedPaymentBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
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

    document.addEventListener("DOMContentLoaded", function () {
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
                        if(guest.status === 'pending'){
                            actionButtons = `
                                <button class="btn btn-success btn-sm mb-1" onclick="approveGuest(${guest.id})">Approve</button>
                                <button class="btn btn-danger btn-sm mb-1" onclick="denyGuest(${guest.id})">Deny</button>
                            `;
                            // Show "Update Payment" button only if fee_waiver is true
                            if (guest.fee_waiver) {
                                actionButtons += `
                                    <button class="btn btn-warning btn-sm" onclick="showAdjustPaymentModal(${guest.id}, '${currentPayableDisplay}', '${remarksContent}')">Update Payment</button>
                                `;
                            }
                        } else {
                            actionButtons = `<span class="text-muted">${guest.status.charAt(0).toUpperCase() + guest.status.slice(1)}</span>`; //show status
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

        // NEW: Function to show the Adjust Payment Modal
        window.showAdjustPaymentModal = function(guestId, currentPayable, existingRemarks) {
            document.getElementById('adjustGuestId').value = guestId;
            document.getElementById('currentPayableAmountDisplay').value = currentPayable;
            document.getElementById('newPayableAmount').value = ''; // Clear previous input
            document.getElementById('waiverRemarks').value = existingRemarks || ''; // Pre-fill existing remarks
            document.getElementById('adjustPaymentMessage').innerHTML = ''; // Clear previous messages

            // Make an API call to get the calculated total, and pre-fill the newPayableAmount field
            // Use the correct URL for your API
            fetch(`http://127.0.0.1:8000/api/guest/${guestId}/total-amount`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Display the calculated total in a read-only field
                        document.getElementById('calculatedTotalAmountDisplay').value = data.data.final_total_amount;
                        // Pre-fill the new payable amount with the calculated final total as a starting point
                        document.getElementById('newPayableAmount').value = data.data.final_total_amount;
                    } else {
                        showModalMessage(data.message || "Could not fetch calculated total.", 'warning', 'adjustPaymentMessage');
                        document.getElementById('calculatedTotalAmountDisplay').value = 'Error';
                        document.getElementById('newPayableAmount').value = 0; // Default to 0 on error
                    }
                })
                .catch(error => {
                    console.error("Error fetching calculated total:", error);
                    showModalMessage("Error fetching calculated total. Please check console.", 'danger', 'adjustPaymentMessage');
                    document.getElementById('calculatedTotalAmountDisplay').value = 'Error';
                    document.getElementById('newPayableAmount').value = 0; // Default to 0 on error
                })
                .finally(() => {
                    // Always show the modal after attempting to fetch and pre-fill
                    const adjustPaymentModal = new bootstrap.Modal(document.getElementById('adjustPaymentModal'));
                    adjustPaymentModal.show();
                });
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


        window.approveGuest = function(guestId) {
            fetch("{{ url('/api/admin/approved-guest') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    "Accept": "application/json"
                },
                body: JSON.stringify({ guest_id: guestId })
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

        window.denyGuest = function(guestId) {
            fetch("{{ url('/api/payment/reject') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    "Accept": "application/json"
                },
                body: JSON.stringify({ guest_id: guestId })
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

        document.getElementById('accessoryModal').addEventListener('hidden.bs.modal', function (event) {
            // Any cleanup needed when modal is hidden
        });
    });
</script>
@endsection