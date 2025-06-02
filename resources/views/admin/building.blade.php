@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h2>Buildings</h2>

    <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('admin.create_building') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Building
        </a>
    </div>

    <div id="errorAlert" class="alert alert-danger d-none" role="alert"></div>

    <table class="table table-bordered" id="buildingList">
        <thead class="table-dark">
            <tr>
                <th>S.No.</th>
                <th>Building Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            </tbody>
    </table>
</div>

<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this building? This action cannot be undone.
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
    let buildingToDeleteId = null; // To store the ID of the building to be deleted

    // Initialize Bootstrap modal directly, as Bootstrap is already loaded via static links
    // Removed redundant dynamic Bootstrap script loading
    deleteConfirmationModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
    fetchBuildings(); // Fetch buildings after DOM is ready and modal is initialized

    // Event listener for the confirm delete button inside the modal
    document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
        if (buildingToDeleteId !== null) {
            performDelete(buildingToDeleteId);
            deleteConfirmationModal.hide(); // Hide the modal after confirming deletion
        }
    });

    function fetchBuildings() {
        fetch('/api/buildings')
            .then(response => {
                // console.log("API Response Status:", response.status, response.statusText); // Removed console.log
                if (!response.ok) {
                    // Log the response body if it's not OK, for more details
                    return response.text().then(text => {
                        throw new Error(`HTTP error! Status: ${response.status}, Message: ${text}`);
                    });
                }
                return response.json();
            })
            .then(apiResponse => {
                // console.log("API Response Data:", apiResponse); // Removed console.log
                let buildingList = document.getElementById("buildingList").querySelector("tbody");
                buildingList.innerHTML = "";
                showError(''); // Clear any previous error messages on successful data fetch

                // Check if the API response indicates success and contains data
                if (apiResponse.success && apiResponse.data && apiResponse.data.length > 0) {
                    apiResponse.data.forEach((building, index) => {
                        buildingList.innerHTML += generateRow(building, index + 1);
                    });
                } else {
                    buildingList.innerHTML = `<tr><td colspan="4" class="text-center">No buildings found.</td></tr>`;
                }
            })
            .catch(error => {
                showError(`Failed to load buildings: ${error.message}`);
                console.error('Error fetching buildings:', error);
            });
    }

    function generateRow(building, serialNumber) {
        let badgeClass = building.status === 'active' ? 'bg-success' : 'bg-danger';
        let statusLabel = building.status.charAt(0).toUpperCase() + building.status.slice(1);
        return `
            <tr data-id="${building.id}">
                <td>${serialNumber}</td>
                <td class="name">${building.name}</td>
                <td class="status"><span class="badge ${badgeClass}">${statusLabel}</span></td>
                <td class="actions">
                    <button class="btn btn-sm btn-warning me-1" onclick="enableEdit(this)">Update</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteBuilding(${building.id})">Delete</button>
                </td>
            </tr>
        `;
    }

    window.enableEdit = function(button) {
        const row = button.closest('tr');
        const name = row.querySelector('.name').innerText;
        const statusText = row.querySelector('.status span').innerText.toLowerCase();

        row.querySelector('.name').innerHTML = `<input type="text" class="form-control form-control-sm" value="${name}">`;
        row.querySelector('.status').innerHTML = `
            <select class="form-select form-select-sm">
                <option value="active" ${statusText === 'active' ? 'selected' : ''}>Active</option>
                <option value="inactive" ${statusText === 'inactive' ? 'selected' : ''}>Inactive</option>
            </select>`;

        row.querySelector('.actions').innerHTML = `
            <button class="btn btn-sm btn-success me-1" onclick="saveUpdate(this)">Save</button>
            <button class="btn btn-sm btn-secondary" onclick="cancelEdit()">Cancel</button>`;
    }

    window.cancelEdit = function() {
        fetchBuildings(); // reload data
    }

    window.saveUpdate = function(button) {
        const row = button.closest('tr');
        const id = row.dataset.id;
        const name = row.querySelector('.name input').value;
        const status = row.querySelector('.status select').value;

        fetch(`/api/buildings/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name, status })
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`Update failed! Status: ${response.status}, Message: ${text}`);
                });
            }
            return response.json();
        })
        .then(() => fetchBuildings())
        .catch(error => {
            showError(`Failed to update building: ${error.message}`);
            console.error('Error updating:', error);
        });
    }

    window.deleteBuilding = function(id) {
        buildingToDeleteId = id; // Store the ID
        deleteConfirmationModal.show(); // Show the custom modal
    }

    function performDelete(id) {
        fetch(`/api/buildings/${id}`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`Deletion failed! Status: ${response.status}, Message: ${text}`);
                });
            }
            return response.json();
        })
        .then(() => fetchBuildings())
        .catch(error => {
            showError(`Failed to delete building: ${error.message}`);
            console.error('Error deleting:', error);
        });
    }

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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
