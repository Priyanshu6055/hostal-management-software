@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Manage Beds</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header">Create New Bed</div>
        <div class="card-body">
            <form id="createBedForm">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <label for="buildingSelect" class="form-label">Select Building</label>
                        <select class="form-select" id="buildingSelect" name="building_id" required>
                            <option value="">-- Select Building --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="roomSelect" class="form-label">Select Room</label>
                        <select class="form-select" id="roomSelect" name="room_id" required>
                            <option value="">-- Select Room --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="bedNumber" class="form-label">Bed Number</label>
                        <input type="text" class="form-control" id="bedNumber" name="bed_number" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">Create Bed</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Beds List</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>S. No</th>
                        <th>Bed Number</th>
                        <th>Room Number</th>
                        <th>Building Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="bedList">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editForm">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Bed</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editId">
            <div class="mb-3">
                <label for="editBedNumber" class="form-label">Bed Number</label>
                <input type="text" class="form-control" id="editBedNumber" required>
            </div>
            <div class="mb-3">
                <label for="editRoomId" class="form-label">Room ID</label>
                <input type="number" class="form-control" id="editRoomId" required>
            </div>
            <div class="mb-3">
                <label for="editStatus" class="form-label">Status</label>
                <select id="editStatus" class="form-select">
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
    let roomMap = {};
    let buildingMap = {};

    document.addEventListener("DOMContentLoaded", function () {
        fetchBuildings();
        fetchRoomsMap();

        document.getElementById('buildingSelect').addEventListener('change', function () {
            fetchRoomsByBuilding(this.value);
        });

        document.getElementById("createBedForm").addEventListener("submit", function (e) {
            e.preventDefault();
            const bedNumber = document.getElementById("bedNumber").value;
            const roomId = document.getElementById("roomSelect").value;

            fetch('/api/beds', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    bed_number: bedNumber,
                    room_id: roomId,
                    status: 'available'
                })
            })
            .then(res => res.json())
            .then(res => {
                if (res.message) {
                    showCustomMessageBox(res.message, 'success'); // Display success message
                    fetchBeds();
                    document.getElementById("createBedForm").reset();
                    document.getElementById("roomSelect").innerHTML = '<option value="">-- Select Room --</option>';
                } else {
                    showCustomMessageBox('Failed to create bed.', 'danger'); // Display error message
                }
            })
            .catch(error => {
                console.error("Error creating bed:", error);
                showCustomMessageBox('An error occurred while creating the bed.', 'danger'); // Display error message
            });
        });
    });

    function fetchBuildings() {
        fetch("/api/buildings")
            .then(res => res.json())
            .then(response => {
                const select = document.getElementById("buildingSelect");
                select.innerHTML = '<option value="">-- Select Building --</option>';
                buildingMap = {};
                if (response.success && response.data) {
                    response.data.forEach(building => {
                        const option = document.createElement("option");
                        option.value = building.id;
                        option.textContent = building.name;
                        buildingMap[building.id] = building.name;
                        select.appendChild(option);
                    });
                } else {
                    console.error("API response for buildings was not successful or data is missing:", response);
                    showCustomMessageBox("Error loading buildings.", 'danger'); // Display error message
                }
            })
            .catch(error => {
                console.error("Error fetching buildings:", error);
                showCustomMessageBox("Failed to load buildings.", 'danger'); // Display error message
            });
    }

    function fetchRoomsByBuilding(buildingId) {
        const roomSelect = document.getElementById("roomSelect");
        roomSelect.innerHTML = '<option value="">Loading...</option>';

        if (!buildingId) {
            roomSelect.innerHTML = '<option value="">-- Select Room --</option>';
            return;
        }

        fetch(`/api/buildings/${buildingId}/rooms`)
            .then(res => res.json())
            .then(response => {
                roomSelect.innerHTML = '<option value="">-- Select Room --</option>';
                const rooms = response.data || response;
                if (Array.isArray(rooms)) {
                    rooms.forEach(room => {
                        const option = document.createElement("option");
                        option.value = room.id;
                        option.textContent = room.room_number;
                        roomSelect.appendChild(option);
                    });
                } else {
                    console.error("API response for rooms was not an array or data is missing:", response);
                    showCustomMessageBox("Error loading rooms.", 'danger'); // Display error message
                }
            })
            .catch(error => {
                console.error("Error fetching rooms by building:", error);
                roomSelect.innerHTML = '<option value="">Failed to load rooms</option>';
                showCustomMessageBox("Failed to load rooms.", 'danger'); // Display error message
            });
    }

    function fetchRoomsMap() {
        fetch("/api/rooms")
            .then(res => res.json())
            .then(response => {
                roomMap = {};
                const rooms = response.data || response;
                if (Array.isArray(rooms)) {
                    rooms.forEach(room => {
                        roomMap[room.id] = {
                            number: room.room_number,
                            building_id: room.building_id
                        };
                    });
                } else {
                    console.error("API response for rooms map was not an array or data is missing:", response);
                    showCustomMessageBox("Error processing room data.", 'danger'); // Display error message
                }
                fetchBeds();
            })
            .catch(error => {
                console.error("Error fetching rooms map:", error);
                showCustomMessageBox("Failed to load room mapping data.", 'danger'); // Display error message
            });
    }

    function fetchBeds() {
        fetch("/api/beds")
            .then(res => res.json())
            .then(response => {
                const bedList = document.getElementById("bedList");
                bedList.innerHTML = "";

                const beds = response.data || response;
                if (!Array.isArray(beds) || !beds.length) {
                    bedList.innerHTML = `<tr><td colspan="6" class="text-center">No beds found.</td></tr>`;
                    return;
                }

                beds.forEach((bed, index) => {
                    const room = roomMap[bed.room_id];
                    const roomNumber = room ? room.number : 'N/A';
                    const buildingName = room && buildingMap[room.building_id] ? buildingMap[room.building_id] : 'N/A';
                    let badge = bed.status === 'available' ? 'success' : 'danger';

                    bedList.innerHTML += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${bed.bed_number}</td>
                            <td>${roomNumber}</td>
                            <td>${buildingName}</td>
                            <td><span class="badge bg-${badge}">${bed.status.toUpperCase()}</span></td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="openEditModal(${bed.id}, '${bed.bed_number}', ${bed.room_id}, '${bed.status}')">Edit</button>
                                <button class="btn btn-sm btn-danger" onclick="deleteBed(${bed.id})">Delete</button>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(error => {
                console.error("Error fetching beds:", error);
                document.getElementById("bedList").innerHTML = `<tr><td colspan="6" class="text-danger text-center">Failed to load beds.</td></tr>`;
                showCustomMessageBox("Failed to load beds.", 'danger'); // Display error message
            });
    }

    // Function to show a custom message box
    function showCustomMessageBox(message, type = 'info') {
        const messageContainer = document.createElement('div');
        messageContainer.className = `alert alert-${type} mt-3`;
        messageContainer.textContent = message;
        document.querySelector('.container').prepend(messageContainer);
        setTimeout(() => messageContainer.remove(), 3000); // Remove after 3 seconds
    }


    function openEditModal(id, bedNumber, roomId, status) {
        document.getElementById("editId").value = id;
        document.getElementById("editBedNumber").value = bedNumber;
        document.getElementById("editRoomId").value = roomId;
        document.getElementById("editStatus").value = status;
        new bootstrap.Modal(document.getElementById("editModal")).show();
    }

    document.getElementById("editForm").addEventListener("submit", function (e) {
        e.preventDefault();

        const id = document.getElementById("editId").value;
        const bedNumber = document.getElementById("editBedNumber").value;
        const roomId = document.getElementById("editRoomId").value;
        const status = document.getElementById("editStatus").value;

        fetch(`/api/beds/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ bed_number: bedNumber, room_id: roomId, status: status })
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                showCustomMessageBox(response.message || "Bed updated successfully.", 'success');
                fetchRoomsMap();
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
            } else {
                showCustomMessageBox(response.message || "Failed to update bed.", 'danger');
            }
        })
        .catch(error => {
            console.error("Error updating bed:", error);
            showCustomMessageBox("An error occurred while updating the bed.", 'danger');
        });
    });

    function deleteBed(id) {
        // Directly call performDelete, as confirm() is removed.
        // A proper confirmation would involve a custom modal.
        performDelete(id);
    }

    function performDelete(id) {
        fetch(`/api/beds/${id}`, {
            method: 'DELETE'
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                showCustomMessageBox(response.message || "Bed deleted successfully.", 'success');
                fetchRoomsMap();
            } else {
                showCustomMessageBox(response.message || "Failed to delete bed.", 'danger');
            }
        })
        .catch(error => {
            console.error("Error deleting bed:", error);
            showCustomMessageBox("An error occurred while deleting the bed.", 'danger');
        });
    }
</script>
@endsection
