@extends('accountant.layout')

@section('content')
<div class="container">
    <h1>Resident Payment History</h1>

    {{-- Search input and button --}}
    <div class="mb-3">
        <input type="text" id="resident_search_input" class="form-control" placeholder="Search by Scholar No, Name, Email, or Number" style="max-width: 400px; display: inline-block;">
        <button id="resident_search_btn" class="btn btn-primary">Search</button>
        <button id="resident_search_reset_btn" class="btn btn-secondary">Reset</button>
    </div>

    <h5>Resident List:</h5>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>S. No.</th>
                    <th>Scholar No</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="resident_table_body">
                {{-- Residents will be loaded via JavaScript --}}
            </tbody>
        </table>
    </div>

    {{-- Pagination controls --}}
    <div class="d-flex justify-content-center align-items-center gap-2">
        <button class="btn btn-outline-primary" id="prev_page_btn">Previous</button>
        <div id="current_page_display" class="border rounded px-2 py-1">1</div>
        <button class="btn btn-outline-primary" id="next_page_btn">Next</button>
    </div>

    {{-- Modal for updating resident information --}}
    <div class="modal fade" id="updateResidentModal" tabindex="-1" aria-labelledby="updateResidentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateResidentModalLabel">Update Resident Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateResidentForm">
                        <input type="hidden" id="update_resident_id">
                        <div class="mb-3">
                            <label for="update_scholar_no" class="form-label">Scholar No</label>
                            <input type="text" class="form-control" id="update_scholar_no" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="update_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="update_name">
                        </div>
                        <div class="mb-3">
                            <label for="update_email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="update_email">
                        </div>
                        <div class="mb-3">
                            <label for="update_emergency_no" class="form-label">Emergency Number</label>
                            <input type="text" class="form-control" id="update_emergency_no">
                        </div>
                    </form>
                    <div id="update_resident_message" class="mt-3" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="save_resident_updates">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <div id="payment_tables_container" class="mt-5" style="display: none;">
        <h2>Payment Details</h2>

        <h3 class="mt-4">Pending Payments</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Total Amount</th>
                        <th>Paid Amount</th>
                        <th>Remaining</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody id="pending_payments_body"></tbody>
            </table>
        </div>
        <p id="no_pending_payments" style="display: none;">No pending payments found for this resident.</p>

        <h3 class="mt-4">Completed Payments</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Total Amount</th>
                        <th>Paid Amount</th>
                        <th>Remaining</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody id="completed_payments_body"></tbody>
            </table>
        </div>
        <p id="no_completed_payments" style="display: none;">No completed payments found for this resident.</p>
    </div>
</div>

{{-- Include jQuery library --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- Include Bootstrap 5 JavaScript bundle for modal functionality --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {
    // Cache jQuery selectors for better performance
    const $residentTableBody = $('#resident_table_body');
    const $paymentTablesContainer = $('#payment_tables_container');
    const $completedPaymentsBody = $('#completed_payments_body');
    const $pendingPaymentsBody = $('#pending_payments_body');
    const $noCompletedPaymentsMessage = $('#no_completed_payments');
    const $noPendingPaymentsMessage = $('#no_pending_payments');
    const $searchInput = $('#resident_search_input');
    const $prevPageBtn = $('#prev_page_btn');
    const $nextPageBtn = $('#next_page_btn');
    const $currentPageDisplay = $('#current_page_display');
    const $updateResidentModal = $('#updateResidentModal');
    const $updateResidentForm = $('#updateResidentForm'); // Although not directly used for form submission, good to cache
    const $updateResidentId = $('#update_resident_id');
    const $updateScholarNo = $('#update_scholar_no');
    const $updateName = $('#update_name');
    const $updateEmail = $('#update_email');
    const $updateEmergencyNo = $('#update_emergency_no');
    const $saveResidentUpdatesBtn = $('#save_resident_updates');
    const $updateResidentMessage = $('#update_resident_message');
    const $residentSearchResetBtn = $('#resident_search_reset_btn'); // Cached reset button

    // Constants for pagination
    const RESIDENTS_PER_PAGE = 10;
    // Variables to hold resident data, current page, total pages, and search query
    let residentsData = []; // Stores all fetched resident data
    let currentPage = 1;
    let totalPages = 0;
    let currentSearchQuery = ''; // Stores the current search term

    /**
     * Renders a subset of residents to the table based on the current page and filters.
     * @param {Array} residents - The array of resident objects to paginate and render.
     * @param {number} page - The current page number to display.
     */
    function renderResidents(residents, page) {
        $residentTableBody.empty(); // Clear existing rows
        const start = (page - 1) * RESIDENTS_PER_PAGE;
        const paginatedResidents = residents.slice(start, start + RESIDENTS_PER_PAGE);

        if (paginatedResidents.length > 0) {
            paginatedResidents.forEach((resident, index) => {
                // Access nested properties for room number and phone
                const roomNumber = resident.bed ? resident.bed.bed_number : 'N/A';
                const phoneNumber = resident.guest ? resident.guest.emergency_no : 'N/A';

                const row = `
                    <tr data-scholar="${resident.scholar_no ? resident.scholar_no.toLowerCase() : ''}"
                        data-name="${resident.name ? resident.name.toLowerCase() : ''}"
                        data-email="${resident.email ? resident.email.toLowerCase() : ''}"
                        data-number="${phoneNumber.toLowerCase()}">
                        <td>${start + index + 1}</td>
                        <td>${resident.scholar_no ?? 'N/A'}</td>
                        <td>${resident.name ?? 'N/A'}</td>
                        <td>${resident.email ?? 'N/A'}</td>
                        <td>${resident.gender ?? 'N/A'}</td>
                        <td>${phoneNumber}</td>
                        <td>
                            <button class="btn btn-sm btn-info edit-resident-btn" data-id="${resident.id}"
                                data-scholar="${resident.scholar_no ?? ''}"
                                data-name="${resident.name ?? ''}"
                                data-email="${resident.email ?? ''}"
                                data-emergency="${phoneNumber}"
                                data-bs-toggle="modal" data-bs-target="#updateResidentModal">Edit</button>
                            <button class="btn btn-sm btn-primary view-history-btn" data-id="${resident.id}">View History</button>
                            <button class="btn btn-sm btn-success make-payment-btn" data-id="${resident.id}">Make Payment</button>
                        </td>
                    </tr>
                `;
                $residentTableBody.append(row);
            });
        } else {
            $residentTableBody.append('<tr><td colspan="7" class="text-center">No residents found on this page.</td></tr>');
        }

        // Update pagination controls
        $currentPageDisplay.text(currentPage);
        $prevPageBtn.prop('disabled', currentPage <= 1);
        $nextPageBtn.prop('disabled', currentPage >= totalPages);
    }

    /**
     * Fetches all resident data from the API and initializes pagination.
     */
    function loadInitialData() {
        $.ajax({
            url: '/api/residents', // API endpoint to get all residents
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                // IMPORTANT: Access data from 'data.data' based on your API response
                if (data.data && Array.isArray(data.data) && data.data.length > 0) {
                    residentsData = data.data; // Store all residents
                    totalPages = Math.ceil(residentsData.length / RESIDENTS_PER_PAGE);
                    currentPage = 1; // Reset to first page on new data load
                    renderResidents(residentsData, currentPage);
                } else {
                    residentsData = [];
                    totalPages = 0;
                    currentPage = 0; // Set current page to 0 if no data
                    renderResidents(residentsData, currentPage); // Render empty table
                    $prevPageBtn.prop('disabled', true);
                    $nextPageBtn.prop('disabled', true);
                    $currentPageDisplay.text(0);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error loading residents:", status, error);
                $residentTableBody.empty().append('<tr><td colspan="7" class="text-danger text-center">Error loading residents. Please try again.</td></tr>');
                $prevPageBtn.prop('disabled', true);
                $nextPageBtn.prop('disabled', true);
                $currentPageDisplay.text(0);
            }
        });
    }

    /**
     * Filters residents based on the search query and re-renders the table.
     */
    function applySearchAndRender() {
        let filteredResidents;
        if (currentSearchQuery === '') {
            filteredResidents = residentsData;
        } else {
            filteredResidents = residentsData.filter(resident => {
                const scholarNo = resident.scholar_no ? resident.scholar_no.toLowerCase() : '';
                const name = resident.name ? resident.name.toLowerCase() : '';
                const email = resident.email ? resident.email.toLowerCase() : '';
                const phoneNumber = resident.guest && resident.guest.emergency_no ? resident.guest.emergency_no.toLowerCase() : '';

                return scholarNo.includes(currentSearchQuery) ||
                       name.includes(currentSearchQuery) ||
                       email.includes(currentSearchQuery) ||
                       phoneNumber.includes(currentSearchQuery);
            });
        }
        totalPages = Math.ceil(filteredResidents.length / RESIDENTS_PER_PAGE);
        currentPage = 1; // Reset to first page after search
        renderResidents(filteredResidents, currentPage);
    }

    // Event listener for the Search button
    $('#resident_search_btn').on('click', function () {
        currentSearchQuery = $searchInput.val().trim().toLowerCase();
        applySearchAndRender();
    });

    // Event listener for the Reset button
    $residentSearchResetBtn.on('click', function () {
        $searchInput.val(''); // Clear the search input
        currentSearchQuery = ''; // Reset the search query
        currentPage = 1; // Reset to first page
        totalPages = Math.ceil(residentsData.length / RESIDENTS_PER_PAGE); // Recalculate total pages for all data
        renderResidents(residentsData, currentPage); // Render all residents
    });

    // Event listener for Previous page button
    $prevPageBtn.on('click', function () {
        if (currentPage > 1) {
            currentPage--;
            applySearchAndRender(); // Re-apply search filter and render for the new page
        }
    });

    // Event listener for Next page button
    $nextPageBtn.on('click', function () {
        if (currentPage < totalPages) {
            currentPage++;
            applySearchAndRender(); // Re-apply search filter and render for the new page
        }
    });

    // Event listener for "View History" buttons (delegated)
    $(document).on('click', '.view-history-btn', function () {
        const residentId = $(this).data('id');

        if (residentId) {
            // Show a loading indicator or clear previous data
            $completedPaymentsBody.empty();
            $pendingPaymentsBody.empty();
            $noCompletedPaymentsMessage.hide();
            $noPendingPaymentsMessage.hide();
            $paymentTablesContainer.show(); // Show the container while loading

            // Fetch payment history for the selected resident
            $.ajax({
                url: `/api/payments/resident/${residentId}`, // API endpoint for resident payments
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    let hasCompleted = false;
                    let hasPending = false;

                    // Check if the API response indicates success and contains data
                    if (response.success && response.data && Array.isArray(response.data) && response.data.length > 0) {
                        response.data.forEach(payment => {
                            // Corrected property names to match the provided API response for payments
                            const row = `
                                <tr>
                                    <td>${payment.transaction_id ?? 'N/A'}</td>
                                    <td>${payment.total_amount ?? 'N/A'}</td>
                                    <td>${payment.amount ?? 'N/A'}</td>
                                    <td>${payment.remaining_amount ?? 'N/A'}</td>
                                    <td>${payment.payment_method ?? 'N/A'}</td>
                                    <td>${payment.payment_status ?? 'N/A'}</td>
                                    <td>${payment.due_date ?? 'N/A'}</td>
                                </tr>
                            `;

                            // Check payment status (case-insensitive comparison for robustness)
                            if (payment.payment_status && payment.payment_status.toLowerCase() === 'completed') {
                                $completedPaymentsBody.append(row);
                                hasCompleted = true;
                            } else if (payment.payment_status && payment.payment_status.toLowerCase() === 'pending') {
                                $pendingPaymentsBody.append(row);
                                hasPending = true;
                            }
                        });

                        // Toggle messages based on whether completed/pending payments were found
                        $noPendingPaymentsMessage.toggle(!hasPending);
                        $noCompletedPaymentsMessage.toggle(!hasCompleted);
                        // If no payments at all, hide the container. Otherwise, it's already shown.
                        if (!hasCompleted && !hasPending) {
                            $paymentTablesContainer.hide();
                            $noPendingPaymentsMessage.text('No payments found for this resident.').show();
                        }

                    } else {
                        // If no data or success is false
                        $paymentTablesContainer.hide();
                        $noCompletedPaymentsMessage.hide();
                        $noPendingPaymentsMessage.text('No payments found for this resident.').show();
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching payment data:", status, error);
                    $paymentTablesContainer.hide();
                    $noCompletedPaymentsMessage.hide();
                    $noPendingPaymentsMessage.text('Error fetching payment data. Please try again.').show();
                }
            });
        } else {
            console.warn("Resident ID not found for view history button.");
        }
    });

    // Event listener for "Make Payment" buttons (delegated)
    $(document).on('click', '.make-payment-btn', function () {
        const residentId = $(this).data('id');
        if (residentId) {
            // Redirect to the make payment page with resident_id as a query parameter
            window.location.href = `/accountant/resident/pay?resident_id=${residentId}`;
        } else {
            console.warn("Resident ID not found for make payment button.");
        }
    });

    // Handle edit resident button click (delegated)
    $(document).on('click', '.edit-resident-btn', function () {
        const residentId = $(this).data('id');
        const scholarNo = $(this).data('scholar');
        const name = $(this).data('name');
        const email = $(this).data('email');
        const emergency = $(this).data('emergency');

        // Populate the modal fields with the resident's current data
        $updateResidentId.val(residentId);
        $updateScholarNo.val(scholarNo);
        $updateName.val(name);
        $updateEmail.val(email);
        $updateEmergencyNo.val(emergency);
        $updateResidentMessage.hide(); // Hide any previous messages
    });

    // Handle save resident updates button click
    $saveResidentUpdatesBtn.on('click', function () {
        const residentId = $updateResidentId.val();
        const name = $updateName.val();
        const email = $updateEmail.val();
        const emergencyNo = $updateEmergencyNo.val();

        $.ajax({
            url: `/api/residents/${residentId}`, // API endpoint for updating resident
            type: 'PUT', // Use PUT method for updates
            dataType: 'json',
            data: {
                name: name,
                email: email,
                emergency_no: emergencyNo // Ensure this matches your backend's expected field name
            },
            // Add CSRF token if your Laravel application requires it for PUT/POST requests
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    $updateResidentMessage.text('Resident information updated successfully.').removeClass('alert-danger').addClass('alert-success').show();
                    // Reload the resident data to reflect changes in the table
                    loadInitialData();
                    // Close the modal after a short delay
                    setTimeout(() => {
                        $updateResidentModal.modal('hide');
                        $updateResidentMessage.hide(); // Hide message after modal closes
                    }, 1500);
                } else {
                    $updateResidentMessage.text('Error updating resident information: ' + (response.message || 'Unknown error.')).removeClass('alert-success').addClass('alert-danger').show();
                }
            },
            error: function (xhr, status, error) {
                console.error("Error updating resident:", status, error);
                $updateResidentMessage.text('An error occurred while updating resident information.').removeClass('alert-success').addClass('alert-danger').show();
            }
        });
    });

    // Load data on page load
    loadInitialData();
});
</script>
@endsection
