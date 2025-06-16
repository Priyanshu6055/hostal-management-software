@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h2>Buildings</h2>

    <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('admin.create_building') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Building
        </a>
    </div>

    {{-- Alert for errors --}}
    <div id="errorAlert" class="alert alert-danger d-none" role="alert"></div>
    {{-- Alert for success messages --}}
    <div id="successAlert" class="alert alert-success d-none" role="alert"></div>

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
    document.addEventListener("DOMContentLoaded", function() {
        let deleteConfirmationModal;
        let buildingToDeleteId = null;

        deleteConfirmationModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
        fetchBuildings();

        document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
            if (buildingToDeleteId !== null) {
                performDelete(buildingToDeleteId);
                deleteConfirmationModal.hide();
            }
        });

        function fetchBuildings(message = null, type = 'success') {
            fetch('/api/buildings')
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP error! Status: ${response.status}, Message: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(apiResponse => {
                    let buildingList = document.getElementById("buildingList").querySelector("tbody");
                    buildingList.innerHTML = "";
                    showError(''); // Clear any previous error messages

                    if (apiResponse.success && apiResponse.data && apiResponse.data.length > 0) {
                        apiResponse.data.forEach((building, index) => {
                            buildingList.innerHTML += generateRow(building, index + 1);
                        });
                    } else {
                        buildingList.innerHTML = `<tr><td colspan="4" class="text-center">No buildings found.</td></tr>`;
                    }

                    // Display success/info message after fetching, if provided
                    if (message) {
                        if (type === 'success') {
                            showMessage(message);
                        } else {
                            showError(message);
                        }
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
            fetchBuildings();
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
                    body: JSON.stringify({
                        name,
                        status
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`Update failed! Status: ${response.status}, Message: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(() => fetchBuildings('Building updated successfully.')) // Pass success message
                .catch(error => {
                    showError(`Failed to update building: ${error.message}`);
                    console.error('Error updating:', error);
                });
        }

        window.deleteBuilding = function(id) {
            buildingToDeleteId = id;
            deleteConfirmationModal.show();
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
                .then(() => fetchBuildings('Building deleted successfully.')) // Pass success message
                .catch(error => {
                    showError(`Failed to delete building: ${error.message}`);
                    console.error('Error deleting:', error);
                });
        }

        function showError(message) {
            const errorAlert = document.getElementById("errorAlert");
            const successAlert = document.getElementById("successAlert"); // Hide success if error comes

            successAlert.classList.add("d-none"); // Ensure success alert is hidden
            errorAlert.textContent = message;
            if (message) {
                errorAlert.classList.remove("d-none");
                setTimeout(() => {
                    errorAlert.classList.add("d-none");
                }, 4000);
            } else {
                errorAlert.classList.add("d-none");
            }
        }

        function showMessage(message) {
            const successAlert = document.getElementById("successAlert");
            const errorAlert = document.getElementById("errorAlert"); // Hide error if success comes

            errorAlert.classList.add("d-none"); // Ensure error alert is hidden
            successAlert.textContent = message;
            if (message) {
                successAlert.classList.remove("d-none");
                setTimeout(() => {
                    successAlert.classList.add("d-none");
                }, 4000);
            } else {
                successAlert.classList.add("d-none");
            }
        }
    });
</script>
@endsection