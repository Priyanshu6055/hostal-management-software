@extends('resident.layout')

@section('content')
<div class="container">
    <h2 style="    font-size: inherit;
    font-weight: inherit;
    display: flex
;
    align-items: center;
    justify-content: center;">Request Room Change</h2>

    <div id="alert" class="alert d-none"></div>

    <form id="roomChangeForm">
        @csrf

        <!-- Hidden Resident ID input (not shown to user but will be submitted) -->
        <input type="hidden" id="resident_id" name="resident_id">

        <!-- Hidden Created By (user ID) -->
        <input type="hidden" id="created_by" name="created_by">

        <div class="form-group">
            <label for="reason">Reason for Room Change:</label>
            <textarea id="reason" name="reason" class="form-control" required></textarea>
        </div>

        <div class="form-group">
            <label for="preference">Room Preference (Optional):</label>
            <input type="text" id="preference" name="preference" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary mt-3" id="submitBtn">Submit Request</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Set values from backend
        const residentId = "{{ auth()->user()->resident->id ?? '' }}";
        const createdBy = "{{ auth()->id() ?? '' }}";

        if (residentId) {
            $('#resident_id').val(residentId);
        }

        if (createdBy) {
            $('#created_by').val(createdBy);
        }

        $('#roomChangeForm').submit(function(event) {
            event.preventDefault();

            let residentId = $('#resident_id').val().trim();
            let createdBy = $('#created_by').val().trim();
            let reason = $('#reason').val().trim();
            let preference = $('#preference').val().trim();
            let csrfToken = $('input[name="_token"]').val();

            if (!residentId) {
                showAlert('Resident ID is required!', 'danger');
                return;
            }

            if (!createdBy) {
                showAlert('User ID (created_by) is missing!', 'danger');
                return;
            }

            if (!reason) {
                showAlert('Reason for room change is required!', 'danger');
                return;
            }

            $('#submitBtn').prop('disabled', true).text('Submitting...');

            let formData = {
                reason: reason,
                preference: preference,
                created_by: createdBy,
                _token: csrfToken
            };

            $.ajax({
                url: "{{ url('/api/room-change/request') }}/" + residentId,
                type: "POST",
                data: formData,
                success: function(response) {
                    showAlert('✅ Room change request submitted successfully!', 'success');
                    $('#roomChangeForm')[0].reset();
                },
                error: function(xhr) {
                    let errorMessage = '❌ Error submitting request. Please try again.';

                    if (xhr.status === 422 && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join(' ');
                    } else if (xhr.status === 500 && xhr.responseText.includes("foreign key constraint fails")) {
                        errorMessage = "❌ Invalid Resident ID. Please enter a valid one.";
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    showAlert(errorMessage, 'danger');
                },
                complete: function() {
                    $('#submitBtn').prop('disabled', false).text('Submit Request');
                }
            });
        });

        function showAlert(message, type) {
            $('#alert').removeClass('d-none alert-success alert-danger')
                .addClass('alert-' + type).text(message).show();
            setTimeout(() => {
                $('#alert').fadeOut();
            }, 5000);
        }
    });
</script>
@endsection