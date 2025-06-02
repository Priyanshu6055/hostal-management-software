@extends('admin.layout')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-3">Rooms Management</h2>

            <div class="d-flex justify-content-between mb-3">
                <a href="{{ route('admin.create_rooms') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Room
                </a>
            </div>

            <div id="errorAlert" class="alert alert-danger d-none" role="alert"></div>

            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Room List</h4>
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="roomList">
                        <thead class="table-dark">
                            <tr>
                                <th>Serial No.</th>
                                <th>Room Number</th>
                                <th>Building Name</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="text-center">Loading rooms...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this room? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let deleteConfirmationModal; // Declare for global access
    let roomToDeleteId = null; // To store the ID of the room to be deleted

    // Initialize Bootstrap modal after DOM is ready
    deleteConfirmationModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));

    // Fetch rooms when the page loads
    fetchRooms();

    // Event listener for the confirm delete button inside the modal
    document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
        if (roomToDeleteId !== null) {
            performDelete(roomToDeleteId);
            deleteConfirmationModal.hide(); // Hide the modal after confirming deletion
        }
    });

    // Fetch all rooms and building data
    function fetchRooms() {
        Promise.all([
            fetch("{{ url('/api/rooms') }}").then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(`HTTP error! Status: ${response.status}, Message: ${text}`); });
                }
                return response.json();
            }),
            fetch("{{ url('/api/buildings') }}").then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(`HTTP error! Status: ${response.status}, Message: ${text}`); });
                }
                return response.json();
            })
        ])
        .then(([roomsApiResponse, buildingsApiResponse]) => {
            let roomList = document.getElementById("roomList").querySelector("tbody");
            roomList.innerHTML = ""; // Clear existing rows

            // Check if rooms API response is successful and contains data
            if (!roomsApiResponse.success || !Array.isArray(roomsApiResponse.data) || roomsApiResponse.data.length === 0) {
                roomList.innerHTML = `<tr><td colspan="5" class="text-center">No rooms found.</td></tr>`;
                return;
            }

            // Create a map for building IDs to names for easy lookup
            const buildingMap = {};
            if (buildingsApiResponse.success && Array.isArray(buildingsApiResponse.data)) {
                buildingsApiResponse.data.forEach(building => {
                    buildingMap[building.id] = building.name;
                });
            } else {
                // If building data is not as expected, log an error but don't stop room display
                console.error("Invalid building data structure:", buildingsApiResponse);
            }

            // Populate the table with room data
            roomsApiResponse.data.forEach((room, index) => {
                roomList.innerHTML += generateRow(room, index + 1, buildingMap);
            });
            showError(''); // Clear any previous error messages on successful data fetch
        })
        .catch(error => {
            showError(`Failed to load rooms or buildings: ${error.message}`);
            console.error('Error fetching rooms or buildings:', error);
        });
    }

    // Generate a table row for a room
    function generateRow(room, serialNo, buildingMap) {
        const buildingName = buildingMap[room.building_id] || 'Unknown'; // Get building name from map

        let badge = room.status === 'available' || room.is_available
            ? `<span class="badge bg-success">Available</span>`
            : `<span class="badge bg-danger">Not Available</span>`;

        return `
            <tr data-id="${room.id}">
                <td>${serialNo}</td>
                <td class="room_number">${room.room_number}</td>
                <td class="building_name">${buildingName}</td>
                <td class="status">${badge}</td>
                <td class="actions">
                    <button class="btn btn-sm btn-warning me-1" onclick="enableEdit(this)">Update</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteRoom(${room.id})">Delete</button>
                </td>
            </tr>`;
    }

    // Enable editing for a room row
    window.enableEdit = function(button) {
        const row = button.closest('tr');
        const room_number = row.querySelector('.room_number').innerText;
        const building_name = row.querySelector('.building_name').innerText; // Keep building name for display
        const status_text = row.querySelector('.status span').innerText.toLowerCase();

        row.querySelector('.room_number').innerHTML = `<input type="text" class="form-control form-control-sm" value="${room_number}">`;
        // Building name is usually not editable in a room entry, so keep it as text or disabled input
        row.querySelector('.building_name').innerHTML = `<span class="form-control-plaintext form-control-sm">${building_name}</span>`;
        row.querySelector('.status').innerHTML = `
            <select class="form-select form-select-sm">
                <option value="available" ${status_text.includes('available') ? 'selected' : ''}>Available</option>
                <option value="not available" ${status_text.includes('not') ? 'selected' : ''}>Not Available</option>
            </select>`;

        row.querySelector('.actions').innerHTML = `
            <button class="btn btn-sm btn-success me-1" onclick="saveUpdate(this)">Save</button>
            <button class="btn btn-sm btn-secondary" onclick="cancelEdit()">Cancel</button>`;
    }

    // Cancel the edit and reload rooms
    window.cancelEdit = function() {
        fetchRooms();
    }

    // Save the updated room data
    window.saveUpdate = function(button) {
        const row = button.closest('tr');
        const id = row.dataset.id;
        const room_number = row.querySelector('.room_number input').value;
        const status = row.querySelector('.status select').value;

        fetch(`/api/rooms/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                room_number,
                status
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => { throw new Error(`Update failed! Status: ${response.status}, Message: ${text}`); });
            }
            return response.json();
        })
        .then(data => {
            fetchRooms(); // Reload rooms after successful update
            // Optionally, show a success message
            // showError("Room updated successfully!", "success");
        })
        .catch(error => {
            showError(`Failed to update room: ${error.message}`);
            console.error('Error updating room:', error);
        });
    }

    // Prepare for room deletion by showing the confirmation modal
    window.deleteRoom = function(id) {
        roomToDeleteId = id; // Store the ID of the room to be deleted
        deleteConfirmationModal.show(); // Show the Bootstrap modal
    }

    // Perform the actual room deletion after confirmation
    function performDelete(id) {
        fetch(`/api/rooms/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => { throw new Error(`Deletion failed! Status: ${response.status}, Message: ${text}`); });
            }
            return response.json();
        })
        .then(() => {
            fetchRooms(); // Reload rooms after successful deletion
            // Optionally, show a success message
            // showError("Room deleted successfully!", "success");
        })
        .catch(error => {
            showError(`Failed to delete room: ${error.message}`);
            console.error('Error deleting room:', error);
        });
    }

    // Show error message in the alert box
    function showError(message) {
        const errorAlert = document.getElementById("errorAlert");
        errorAlert.textContent = message;
        if (message) { // Only show if there's an actual message
            errorAlert.classList.remove("d-none");
            setTimeout(() => {
                errorAlert.classList.add("d-none");
            }, 4000);
        } else { // Hide if message is empty
            errorAlert.classList.add("d-none");
        }
    }
});
</script>
{{-- Bootstrap CSS and JS are typically included in admin.layout or a common layout file.
     If not, ensure these are present for Bootstrap functionality. --}}
{{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
@endsection
