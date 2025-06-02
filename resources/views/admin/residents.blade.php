@extends('admin.layout')

@section('content')
<div class="container mt-5">
    <h3>List of Residents</h3>
    <hr>

    <div id="responseMessage" class="mt-3"></div> {{-- Added message container here --}}

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>S. No</th>
                <th>Scholar No</th>
                <th>Resident Name</th>
                <th>Email</th>
                <th>Gender</th>
                <th>Bed Number</th>
                <th>Room Preference</th>
                <th>Food Preference</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody id="residentList">
            </tbody>
    </table>
</div>

<script>
document.addEventListener("DOMContentLoaded", fetchResidents);

// Function to show a custom message box
function showCustomMessageBox(message, type = 'info') {
    const messageContainer = document.getElementById('responseMessage');
    if (messageContainer) { // Ensure the container exists
        messageContainer.innerHTML = ""; // Clear previous messages
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        messageContainer.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000); // Remove after 3 seconds
    } else {
        console.warn("Message container #responseMessage not found.");
    }
}

function fetchResidents() {
    fetch("{{ url('/api/residents') }}")
        .then(response => response.json())
        .then(data => {
            // Correctly access residents from data.data as per the API response structure
            const residents = data.data;
            const residentList = document.getElementById("residentList");
            residentList.innerHTML = "";

            if (!Array.isArray(residents) || residents.length === 0) {
                residentList.innerHTML = `<tr><td colspan="10" class="text-center">No residents found.</td></tr>`;
                return;
            }

            residents.forEach((resident, index) => {
                const guest = resident.guest || {};
                const bed = resident.bed || {};

                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${resident.scholar_no || 'N/A'}</td> {{-- Changed from guest.scholar_no to resident.scholar_no based on API --}}
                    <td>${resident.name || 'N/A'}</td> {{-- Changed from guest.name to resident.name based on API --}}
                    <td>${resident.email || 'N/A'}</td> {{-- Changed from guest.email to resident.email based on API --}}
                    <td>${resident.gender || 'N/A'}</td> {{-- Changed from guest.gender to resident.gender based on API --}}
                    <td>${bed.bed_number || 'Not Assigned'}</td>
                    <td>${guest.room_preference || 'N/A'}</td>
                    <td>${guest.food_preference || 'N/A'}</td>
                    <td>${resident.status || 'N/A'}</td>
                    <td>${new Date(resident.created_at).toLocaleString()}</td>
                `;
                residentList.appendChild(row);
            });
        })
        .catch(error => {
            console.error("Error fetching residents:", error);
            document.getElementById("residentList").innerHTML =
                `<tr><td colspan="10" class="text-danger text-center">Error loading residents.</td></tr>`;
            showCustomMessageBox("Failed to load residents.", 'danger'); // Display error message
        });
}
</script>
@endsection
