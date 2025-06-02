@extends('admin.layout')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Create Building</h2>

    <div id="alert-container"></div> {{-- Added alert container for consistent messaging --}}

    @auth
    <form id="buildingForm">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Building Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="mb-3">
            <label for="building_code" class="form-label">Building Code</label>
            <input type="text" class="form-control" id="building_code" name="building_code" required>
        </div>

        <div class="mb-3">
            <label for="university_id" class="form-label">Select University</label>
            <select class="form-control" id="university_id" name="university_id" required>
                <option value="">-- Select University --</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="">-- Select Status --</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="floors" class="form-label">Number of Floors</label>
            <select class="form-control" id="floors" name="floors" required>
                <option value="">-- Select Floors --</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
        </div>

        <div class="mb-3" hidden>
            <label for="created_by" class="form-label">Created By</label>
            <input type="text" class="form-control" id="created_by" name="created_by" value="{{ Auth::id() }}" readonly>
        </div>

        <button type="submit" class="btn btn-success">Create</button>
    </form>

    {{-- Removed old message div, now using alert-container --}}
    @else
    <p>Please log in to create a building.</p>
    @endauth
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        // Helper function to display alerts (copied from previous context)
        function showAlert(type, message) {
            const alertContainer = document.getElementById("alert-container");
            if (alertContainer) {
                alertContainer.innerHTML = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }
        }

        // Function to fetch universities from API and populate the dropdown
        function fetchUniversities() {
            fetch("/api/universities")
                .then(response => {
                    // Check if the response was successful (HTTP status 2xx)
                    if (!response.ok) {
                        // If not, parse the error message from the response body
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    let universitySelect = document.getElementById("university_id");
                    // Clear existing options and add the default one
                    universitySelect.innerHTML = '<option value="">-- Select University --</option>';

                    // Check if the response is successful and contains an array in the 'data' field
                    if (data.success && Array.isArray(data.data)) {
                        data.data.forEach(university => {
                            let option = document.createElement("option");
                            option.value = university.id;
                            option.textContent = university.name;
                            universitySelect.appendChild(option);
                        });
                    } else {
                        // If 'data' is not an array or success is false, show an error
                        showAlert("danger", "Invalid response from server. Expected an array of universities in the 'data' field.");
                        console.error("Invalid university data structure:", data);
                    }
                })
                .catch(error => {
                    // Handle network errors or errors thrown from the .then block
                    let errorMessage = "Failed to load universities.";
                    if (error.message) {
                        errorMessage = error.message; // Use message from API response if available
                    } else if (error instanceof TypeError) {
                        errorMessage = "Could not connect to the server. Please check your network connection.";
                    } else {
                        errorMessage += " Error: " + error;
                    }
                    showAlert("danger", errorMessage);
                    console.error("Error fetching universities:", error);
                });
        }

        fetchUniversities(); // Call the function to fetch universities when the page loads

        // Handle form submission
        document.getElementById("buildingForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent default form submission

            let name = document.getElementById("name").value;
            let building_code = document.getElementById("building_code").value;
            let university_id = document.getElementById("university_id").value;
            let status = document.getElementById("status").value;
            let floors = document.getElementById("floors").value;
            let created_by = document.getElementById("created_by").value; // Fetch created_by value

            // Prepare data to be sent to the backend
            let data = {
                name: name,
                building_code: building_code,
                university_id: university_id,
                status: status,
                floors: floors,
                created_by: created_by // Add created_by to the data
            };

            fetch("/api/buildings", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '', // Get CSRF token
                    "Authorization": `Bearer {{ Auth::user()->api_token ?? '' }}` // Token from logged-in user, handle if not present
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.id) { // Assuming 'id' in the response means success
                    showAlert("success", "Building Created Successfully");
                    document.getElementById("buildingForm").reset(); // Reset form on success
                } else {
                    // If API returns a non-error status but indicates failure in its body
                    showAlert("danger", `Error: ${data.message || JSON.stringify(data)}`);
                }
            })
            .catch(error => {
                let errorMessage = "Something went wrong!";
                if (error.message) {
                    errorMessage = error.message; // Use message from API response if available
                } else if (error instanceof TypeError) {
                    errorMessage = "Could not connect to the server. Please check your network connection.";
                } else {
                    errorMessage += " Error: " + error;
                }
                showAlert("danger", errorMessage);
                console.error("Error creating building:", error);
            });
        });
    });
</script>
@endsection
