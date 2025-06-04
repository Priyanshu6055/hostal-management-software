@extends('admin.layout')

@section('content')
<div class="container mt-5">
    <h2 class="text-xl font-semibold mb-4">Assign Fine to Resident</h2>

    <div id="success-message" class="alert alert-success d-none"></div>
    <div id="error-message" class="alert alert-danger d-none"></div>

    <form id="subscriptionForm">
        <div class="mb-3">
            <label for="resident_id" class="form-label">Resident</label>
            <select name="resident_id" id="resident_id" class="form-control" required>
                <option value="">Loading residents...</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="fee_type" class="form-label">Fee Type</label>
            <select name="fee_type" id="fee_type" class="form-control" required>
                <option value="">Select Fee Type</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="subscription_type" class="form-label">Subscription Type</label>
            <input type="text" name="subscription_type" id="subscription_type_input" class="form-control" readonly>
        </div>

        <div class="mb-3">
            <label for="custom_amount" class="form-label">Custom Amount</label>
            <input type="number" name="custom_amount" step="0.01" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select name="payment_method" class="form-control" required>
                <option value="">Select Method</option>
                <option value="Cash">Cash</option>
                <option value="UPI">UPI</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Card">Card</option>
                <option value="Other">Other</option>
                <option value="Null">Null</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks (optional)</label>
            <textarea name="remarks" class="form-control"></textarea>
        </div>

        <input type="hidden" name="fee_head_id" id="fee_head_id">
        <input type="hidden" name="created_by" value="{{ auth()->user()->id }}">

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

{{-- Assets --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(async function () {
        const residentSelect = $('#resident_id');
        const feeTypeSelect = $('#fee_type');
        const subscriptionTypeInput = $('#subscription_type_input');
        const feeHeadIdInput = $('#fee_head_id');

        try {
            // Fetch residents
            const residentRes = await fetch("{{ url('/api/residents') }}");
            const residentData = await residentRes.json();

            residentSelect.html('<option value="">Select Resident</option>');
            residentData.data.forEach(res => {
                residentSelect.append(`<option value="${res.id}">${res.name} (${res.scholar_no})</option>`);
            });

            residentSelect.select2({ placeholder: "Select Resident", width: '100%' });

            // Fetch all fees
            const feeRes = await fetch("{{ url('/api/fees') }}");
            const feeData = await feeRes.json();

            feeTypeSelect.html('<option value="">Select Fee Type</option>');
            feeData.data.forEach(fee => {
                if (fee.is_active === 1) {
                    feeTypeSelect.append(`<option value="${fee.name}" data-id="${fee.fee_head_id}">${fee.name}</option>`);
                }
            });

            // On Fee Type change
            feeTypeSelect.on('change', function () {
                const selectedOption = $(this).find('option:selected');
                const feeType = selectedOption.val();
                const feeHeadId = selectedOption.data('id');

                subscriptionTypeInput.val(feeType);
                feeHeadIdInput.val(feeHeadId);
            });

        } catch (err) {
            console.error(err);
            $('#error-message').removeClass('d-none').text('Failed to load resident or fee type data.');
        }

        // Form Submit
        $('#subscriptionForm').on('submit', async function (e) {
            e.preventDefault();

            const formData = {
                resident_id: $('#resident_id').val(),
                fee_head_id: $('#fee_head_id').val(),
                subscription_type: $('#subscription_type_input').val(),
                custom_amount: parseFloat($('input[name="custom_amount"]').val()),
                remarks: $('textarea[name="remarks"]').val(),
                payment_method: $('select[name="payment_method"]').val(),
                created_by: $('input[name="created_by"]').val()
            };

            try {
                const res = await fetch("{{ url('/api/admin/subscribe-resident') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                const result = await res.json();
                if (res.ok) {
                    $('#success-message').removeClass('d-none').text(result.message || 'Fine assigned successfully.');
                    $('#error-message').addClass('d-none');
                    $('#subscriptionForm')[0].reset();
                    $('#resident_id').val(null).trigger('change');
                    $('#fee_type').val('').trigger('change');
                    subscriptionTypeInput.val('');
                    feeHeadIdInput.val('');
                } else {
                    $('#error-message').removeClass('d-none').text(result.message || 'Assignment failed.');
                    $('#success-message').addClass('d-none');
                }
            } catch (err) {
                console.error('Submit error:', err);
                $('#error-message').removeClass('d-none').text('An error occurred. Please try again.');
                $('#success-message').addClass('d-none');
            }
        });
    });
</script>
@endsection
