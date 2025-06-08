@extends('guest.layout')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="container mt-4">
    <h3 class="mb-3">Guest Request Status</h3>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>S.No.</th> {{-- Changed to Serial Number --}}
                <th>Scholar No</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="guestList">
            <tr><td colspan="4" class="text-center">Loading guest requests...</td></tr>
        </tbody>
    </table>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    fetchGuestStatus();
});

function fetchGuestStatus() {
    fetch("{{ url('/api/aproved/rejected/guest') }}")
        .then(response => {
            if (!response.ok) {
                throw new Error("Failed to load data");
            }
            return response.json();
        })
        .then(data => {
            let guestList = document.getElementById("guestList");
            guestList.innerHTML = "";

            if (!data.data || data.data.length === 0) {
                guestList.innerHTML = `<tr><td colspan="4" class="text-center">No approved, rejected, or pending guests found.</td></tr>`;
                return;
            }

            // Initialize serial number
            let serialNumber = 1;

            data.data.forEach(guest => {
                let statusClass = getStatusClass(guest.status);
                let actionColumn = (guest.status.trim().toLowerCase() === 'approved' || guest.status.trim().toLowerCase() === 'waiver_approved')
                    ? `<button class="btn btn-primary btn-sm" onclick="makePayment(${guest.id})"><i class="fa fa-credit-card"></i> Make Payment</button>`
                    : '-';

                guestList.innerHTML += `
                    <tr>
                        <td>${serialNumber++}</td> {{-- Display serial number and increment --}}
                        <td>${guest.scholar_no}</td>
                        <td><span class="badge ${statusClass}">${guest.status}</span></td>
                        <td>${actionColumn}</td>
                    </tr>
                `;
            });
        })
        .catch(error => {
            console.error('Error fetching guest status:', error);
            document.getElementById("guestList").innerHTML = `
                <tr><td colspan="4" class="text-center text-danger">Failed to load guest requests. Please try again later.</td></tr>`;
        });
}

function getStatusClass(status) {
    switch (status.trim().toLowerCase()) {
        case 'approved': return 'bg-success text-white';
        case 'rejected': return 'bg-danger text-white';
        case 'pending': return 'bg-warning text-dark'; // Added pending status style
        default: return 'bg-secondary text-white';
    }
}

function makePayment(guestId) {
    alert("Redirecting to payment page for Guest ID: " + guestId); // Added alert for clarity
    window.location.href = "{{ url('/guest/payment') }}/" + guestId;
}
</script>

@endsection