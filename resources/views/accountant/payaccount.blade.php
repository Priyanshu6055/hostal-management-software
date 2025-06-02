@extends('accountant.layout')

@section('content')
<div class="container mt-5">
    <h2>Resident Payments Form</h2>

    <form id="payment-form">
        <div class="mb-3" style="display: none;">
            <label for="resident_id" class="form-label">Resident</label>
            <select name="resident_id" id="resident_id" class="form-select" required>
                <option value="">-- Select Resident --</option>
            </select>
        </div>

        <div class="mb-3 ">
            <label for="resident_name" class="form-label">Name</label>
            <input type="text" id="resident_name" class="form-control" readonly />
        </div>

        <div class="mb-3">
            <label for="scholar_no" class="form-label">Scholar Number</label>
            <input type="text" id="scholar_no" class="form-control" readonly />
        </div>

        <div class="mb-3">
            <label for="subscription_id" class="form-label">Subscription ID</label>
            <select name="subscription_id" id="subscription_id" class="form-select" required>
                <option value="">-- Select Subscription --</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="transaction_id" class="form-label">Transaction ID</label>
            <input type="text" name="transaction_id" id="transaction_id" class="form-control" required />
        </div>

        <div class="mb-3">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select name="payment_method" id="payment_method" class="form-select" required>
                <option value="">-- Select Payment Method --</option>
                <option value="Cash">Cash</option>
                <option value="UPI">UPI</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Card">Card</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" min="1" step="0.01" name="amount" id="amount" class="form-control" required />
        </div>

        <button type="submit" class="btn btn-primary">Submit Payment</button>
    </form>

    <div id="message" class="mt-3"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {
    /**
     * Retrieves a query parameter from the URL.
     * @param {string} name - The name of the query parameter.
     * @returns {string|null} The value of the query parameter, or null if not found.
     */
    function getQueryParam(name) {
        const params = new URLSearchParams(window.location.search);
        return params.get(name);
    }

    // Initialize Select2 for resident select dropdown
    $('#resident_id').select2({
        placeholder: 'Search Resident...',
        allowClear: true,
        ajax: {
            url: '/api/residents', // API endpoint for searching residents
            dataType: 'json',
            delay: 250, // Delay in milliseconds before sending the request
            data: params => ({ search: params.term }), // Data to send with the request
            processResults: function(data) {
                // Process the results from the API response
                // Assuming resident data is in `data.data` array
                return {
                    results: (data.data || []).map(r => ({
                        id: r.id,
                        text: `${r.name} (${r.scholar_no})`,
                        extra: r // Store the full resident object for later use
                    }))
                };
            },
            cache: true // Cache the results
        },
        minimumInputLength: 1 // Minimum characters to type before search
    });

    /**
     * Loads subscriptions for a given resident ID into the subscription_id select.
     * @param {number} residentId - The ID of the resident.
     */
    function loadSubscriptions(residentId) {
        if (!residentId) {
            $('#subscription_id').empty().append('<option value="">-- Select Subscription --</option>');
            return;
        }
        $.ajax({
            url: `/api/pending/${residentId}/subscription`, // API endpoint for resident subscriptions
            method: 'GET',
            success: function(response) { // Changed 'data' to 'response' for clarity
                $('#subscription_id').empty().append('<option value="">-- Select Subscription --</option>');
                
                // Access the subscriptions array correctly from the nested 'data.subscriptions'
                let subscriptions = response.data && response.data.subscriptions ? response.data.subscriptions : []; 

                if (subscriptions && Array.isArray(subscriptions) && subscriptions.length > 0) {
                    subscriptions.forEach(function(sub) {
                        const optionText = `Subscription ID: ${sub.subscription_id} - Status: ${sub.status}`;
                        $('#subscription_id').append(`<option value="${sub.subscription_id}">${optionText}</option>`);
                    });
                    // Pre-select the first subscription if available
                    $('#subscription_id').val(subscriptions[0].subscription_id).trigger('change');
                } else {
                    $('#subscription_id').append('<option value="">No subscriptions found</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error(`Error loading subscriptions for resident ${residentId}:`, status, error);
                $('#subscription_id').empty().append('<option value="">Error loading subscriptions</option>');
            }
        });
    }

    // Event listener for when a resident is selected from the Select2 dropdown
    $('#resident_id').on('select2:select', function(e) {
        const selectedData = e.params.data;
        // Populate name and scholar number fields
        $('#resident_name').val(selectedData.extra.name || '');
        $('#scholar_no').val(selectedData.extra.scholar_no || '');
        // Load subscriptions for the selected resident
        loadSubscriptions(selectedData.id);
    });

    // Event listener for when the resident selection is cleared
    $('#resident_id').on('select2:clear', function() {
        $('#resident_name').val('');
        $('#scholar_no').val('');
        $('#subscription_id').empty().append('<option value="">-- Select Subscription --</option>');
    });

    // Preselect resident if resident_id is present in the URL query parameters
    const residentIdFromUrl = getQueryParam('resident_id');
    if (residentIdFromUrl) {
        // Fetch resident details using the ID from the URL
        $.ajax({
            url: `/api/residents?id=${residentIdFromUrl}`, // Use 'id' parameter for direct lookup if supported, or 'search'
            success: function(data) {
                // IMPORTANT: Access resident data from `data.data`
                const resident = (data.data || []).find(r => r.id == residentIdFromUrl);
                if (resident) {
                    // Create a new option element for Select2 and append it
                    const option = new Option(`${resident.name} (${resident.scholar_no})`, resident.id, true, true);
                    $('#resident_id').append(option).trigger('change'); // Trigger change to update Select2 display

                    // Populate the readonly name and scholar number fields
                    $('#resident_name').val(resident.name);
                    $('#scholar_no').val(resident.scholar_no);
                    // Load subscriptions for the preselected resident
                    loadSubscriptions(resident.id);
                } else {
                    console.warn(`Resident with ID ${residentIdFromUrl} not found.`);
                }
            },
            error: function(xhr, status, error) {
                console.error(`Error fetching resident with ID ${residentIdFromUrl}:`, status, error);
            }
        });
    }

    // Handle form submission for payment
    $('#payment-form').submit(function(e) {
        e.preventDefault(); // Prevent default form submission

        const formData = {
            resident_id: $('#resident_id').val(),
            subscription_id: $('#subscription_id').val(),
            transaction_id: $('#transaction_id').val(),
            payment_method: $('#payment_method').val(),
            amount: $('#amount').val()
        };

        // Clear previous messages
        $('#message').removeClass('alert alert-success alert-danger').text('');

        $.ajax({
            url: '/api/account/subscribe/pay', // API endpoint for submitting payment
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token for Laravel protection
            },
            success: function(response) {
                $('#message').addClass('alert alert-success').text(response.message);
                // Reset the form after successful submission
                $('#payment-form')[0].reset();
                $('#resident_id').val(null).trigger('change'); // Clear Select2 selection
                $('#resident_name').val('');
                $('#scholar_no').val('');
                $('#subscription_id').empty().append('<option value="">-- Select Subscription --</option>');
            },
            error: function(xhr) {
                let errMsg = 'An error occurred.';
                if(xhr.responseJSON) {
                    if(xhr.responseJSON.error) {
                        errMsg = xhr.responseJSON.error;
                    }
                    else if(xhr.responseJSON.messages) {
                        // Concatenate validation error messages
                        errMsg = Object.values(xhr.responseJSON.messages).flat().join(' ');
                    }
                }
                $('#message').addClass('alert alert-danger').text(errMsg);
            }
        });
    });
});
</script>
@endsection
