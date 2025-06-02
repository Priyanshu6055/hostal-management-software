@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h2>Create Room</h2>

    <div id="responseMessage" class="alert" style="display: none;"></div>

    <form id="createRoomForm">
        @csrf
        <div class="mb-3">
            <label for="room_number" class="form-label">Room Number</label>
            <input type="text" class="form-control" id="room_number" name="room_number" required>
        </div>

        <div class="mb-3">
            <label for="building_id" class="form-label">Select Building</label>
            <select class="form-control" id="building_id" name="building_id" required>
                <option value="">-- Select Building --</option>
                </select>
        </div>

        <div class="mb-3">
            <label for="floorSelect" class="form-label">Select Floor</label>
            <select class="form-control" id="floorSelect" name="floor_no" required>
                <option value="">-- Select Floor --</option>
                </select>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="available">Available</option>
                <option value="occupied">Occupied</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Create Room</button>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Helper function to display messages
    function showMessage(type, message) {
        const responseMessage = document.getElementById("responseMessage");
        responseMessage.className = `alert alert-${type}`;
        responseMessage.innerText = message;
        responseMessage.style.display = "block";
        setTimeout(() => {
            responseMessage.style.display = "none";
        }, 4000); // Hide message after 4 seconds
    }

    fetchBuildings();

    // Event listener when a building is selected
    document.getElementById('building_id').addEventListener('change', function () {
        const selectedBuildingId = this.value;
        const selectedBuildingFloors = getBuildingFloorsById(selectedBuildingId);
        updateFloorDropdown(selectedBuildingFloors);
    });

    document.getElementById("createRoomForm").addEventListener("submit", function (event) {
        event.preventDefault();

        const room_number = document.getElementById("room_number").value;
        const building_id = document.getElementById("building_id").value;
        const floor_no = document.getElementById("floorSelect").value;
        const status = document.getElementById("status").value;

        fetch("/api/rooms", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '', // Get CSRF token
            },
            body: JSON.stringify({
                room_number: room_number,
                building_id: building_id,
                floor_no: floor_no,
                status: status
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) { // Check for 'success' flag in the response
                showMessage("success", data.message || "Room created successfully!");
                // Reset form and floor dropdown
                document.getElementById("createRoomForm").reset();
                document.getElementById('floorSelect').innerHTML = '<option value="">-- Select Floor --</option>';
            } else {
                showMessage("danger", data.message || "Error creating room. Please try again.");
            }
        })
        .catch(error => {
            let errorMessage = "An unexpected error occurred.";
            if (error.message) {
                errorMessage = error.message;
            } else if (error.errors) { // Handle Laravel validation errors
                errorMessage = Object.values(error.errors).flat().join('<br>');
            }
            showMessage("danger", `Error: ${errorMessage}`);
            console.error("Error creating room:", error);
        });
    });

    // Fetch buildings and populate the dropdown
    function fetchBuildings() {
        fetch("/api/buildings")
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(`HTTP error! Status: ${response.status}, Message: ${text}`); });
                }
                return response.json();
            })
            .then(apiResponse => {
                const buildingSelect = document.getElementById("building_id");
                buildingSelect.innerHTML = '<option value="">-- Select Building --</option>'; // Clear and add default

                // Check if the API response indicates success and contains data
                if (apiResponse.success && Array.isArray(apiResponse.data)) {
                    apiResponse.data.forEach(building => {
                        const option = document.createElement("option");
                        option.value = building.id;
                        option.textContent = building.name;
                        option.dataset.floors = building.floors;  // Store the number of floors in the option
                        buildingSelect.appendChild(option);
                    });
                } else {
                    showMessage("danger", "Invalid response from server for buildings. Expected an array in 'data' field.");
                    console.error("Invalid building data structure:", apiResponse);
                }
            })
            .catch(error => {
                let errorMessage = "Failed to load buildings.";
                if (error.message) {
                    errorMessage = error.message;
                } else if (error instanceof TypeError) {
                    errorMessage = "Could not connect to the server. Please check your network connection.";
                }
                showMessage("danger", `Error: ${errorMessage}`);
                console.error("Error fetching buildings:", error);
            });
    }

    // Get the number of floors for the selected building
    function getBuildingFloorsById(buildingId) {
        const buildingSelect = document.getElementById("building_id");
        const selectedBuilding = Array.from(buildingSelect.options).find(option => option.value == buildingId);
        return selectedBuilding ? parseInt(selectedBuilding.dataset.floors) : 0;
    }

    // Update the floor dropdown based on the selected building's floors
    function updateFloorDropdown(floors) {
        const floorSelect = document.getElementById("floorSelect");
        floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';

        if (floors > 0) {
            for (let i = 1; i <= floors; i++) {
                const option = document.createElement("option");
                option.value = i;
                option.textContent = `Floor ${i}`;
                floorSelect.appendChild(option);
            }
        } else {
            floorSelect.innerHTML = '<option value="">No floors available</option>';
        }
    }
});
</script>

@endsection
