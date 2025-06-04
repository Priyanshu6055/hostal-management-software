@extends('resident.layout')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Checkout Status</h2>

    <div id="checkoutTableContainer">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reason</th>
                    <th>Account Approval</th>
                    <th>Admin Approval</th>
                    <th>Remarks</th> {{-- Added Remarks column --}}
                    <th>Acessory Check</th> {{-- Added Action column --}}
                    <th>View Details</th>
                </tr>
            </thead>
            <tbody id="checkoutTableBody">
            </tbody>
        </table>
        <p id="noData" style="display:none;">No checkout status found.</p>
        <p id="fetchError" class="text-danger text-center mt-3" style="display:none;">Error loading checkout status. Please try again.</p>
    </div>

    <div class="modal fade" id="checkoutDetailsModal" tabindex="-1" aria-labelledby="checkoutDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutDetailsModalLabel">Checkout Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="checkoutDetailsContent">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const residentId = "{{ auth()->user()->resident->id ?? '' }}";
    const checkoutTableBody = document.getElementById('checkoutTableBody');
    const noDataMsg = document.getElementById('noData');
    const fetchErrorMsg = document.getElementById('fetchError');
    const checkoutDetailsModalElement = document.getElementById('checkoutDetailsModal');
    let checkoutDetailsModal;

    // Load Bootstrap dynamically and initialize modal
    const bootstrapScript = document.createElement('script');
    bootstrapScript.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js';
    bootstrapScript.onload = () => {
        initializeBootstrap();
    };
    bootstrapScript.onerror = () => {
        console.error("Failed to load Bootstrap from CDN.");
        fetchErrorMsg.textContent = "Error: Bootstrap is required but failed to load. The application may not function correctly.";
        fetchErrorMsg.style.display = 'block';
    };
    document.head.appendChild(bootstrapScript);

    function initializeBootstrap() {
        checkoutDetailsModal = new bootstrap.Modal(checkoutDetailsModalElement);
        fetchCheckoutStatus(residentId);
    }

    function fetchCheckoutStatus(residentId) {
        // Validate resident ID
        if (!residentId || isNaN(parseInt(residentId)) || parseInt(residentId) <= 0) {
            console.error("Resident ID is invalid or not found:", residentId);
            fetchErrorMsg.innerText = "Resident ID not found or invalid. Please check your session or authentication.";
            fetchErrorMsg.style.display = 'block';
            return;
        }

        fetch(`http://127.0.0.1:8000/api/resident/${residentId}/checkout-status`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Network response was not ok, status: ${response.status}`);
                }
                return response.json();
            })
            .then(apiResponse => {
                checkoutTableBody.innerHTML = ''; // Clear previous data
                fetchErrorMsg.style.display = 'none'; // Hide any previous error messages

                // Access the 'data' object within the API response for the main table
                const checkoutData = apiResponse.data;

                if (!checkoutData || Object.keys(checkoutData).length === 0) {
                    noDataMsg.style.display = 'block';
                    return;
                }

                noDataMsg.style.display = 'none';
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${checkoutData.date ?? 'N/A'}</td>
                    <td>${checkoutData.reason ?? 'N/A'}</td>
                    <td><span class="badge ${getBadgeClass(checkoutData.account_approval)}">${capitalize(checkoutData.account_approval)}</span></td>
                    <td><span class="badge ${getBadgeClass(checkoutData.admin_approval)}">${capitalize(checkoutData.admin_approval)}</span></td>
                    <td>${checkoutData.remarks ?? 'N/A'}</td> {{-- Display remarks --}}
                    <td>${formatAction(checkoutData.action)}</td> {{-- Display action --}}
                    <td>
                        <button class="btn btn-primary btn-sm view-details-btn" data-resident-id="${residentId}">View Details</button>
                    </td>
                `;
                checkoutTableBody.appendChild(row);

                // Add event listener for the "View Details" button
                const viewDetailsButton = row.querySelector('.view-details-btn');
                viewDetailsButton.addEventListener('click', () => {
                    showCheckoutDetails(residentId, checkoutData); // Pass checkoutData to showCheckoutDetails
                });
            })
            .catch(err => {
                console.error('Error fetching checkout status:', err);
                fetchErrorMsg.innerText = `Error fetching checkout status: ${err.message}. Please try again.`;
                fetchErrorMsg.style.display = 'block';
                noDataMsg.style.display = 'none'; // Hide no data message if there's an error
            });
    }

    async function showCheckoutDetails(residentId, checkoutDetailsFromMainAPI) {
        const checkoutDetailsContent = document.getElementById('checkoutDetailsContent');
        checkoutDetailsContent.innerHTML = '<p>Loading details...</p>'; // Show loading message

        try {
            // This API call's response structure is based on the provided getCheckoutLogs controller:
            // { success: true, message: "...", data: [...] } where 'data' is the array of accessory logs.
            const response = await fetch(`http://127.0.0.1:8000/api/resident-checkout-logs/${residentId}`);
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || `Network response was not ok, status: ${response.status}`;
                throw new Error(errorMessage);
            }
            const apiResponse = await response.json(); // This holds { success, message, data: accessory_logs_array, errors }

            let detailsHTML = '';

            // Check the success flag from the API response first
            if (apiResponse.success === false) {
                detailsHTML += `<p class="text-danger">${apiResponse.message || 'Failed to fetch checkout details from API.'}</p>`;
            } else {
                // Display Resident Information: This API endpoint (getCheckoutLogs) does NOT return resident details directly.
                // You would need another API endpoint to get comprehensive resident details if needed here.
                detailsHTML += `
                    <h4>Resident Information</h4>
                    <p><strong>Resident ID:</strong> ${residentId}</p>
                    <p><strong>Name:</strong> N/A (Resident name not available from this API endpoint)</p>
                    <hr>
                `;

                // Display Checkout Details: Now using checkoutDetailsFromMainAPI
                const depositedAmount = 10000; // Fixed deposited amount as requested
                let totalDebit = 0;
                const accessoryLogs = Array.isArray(apiResponse.data) ? apiResponse.data : [];

                accessoryLogs.forEach(log => {
                    const debit = parseFloat(log.debit_amount);
                    if (!isNaN(debit)) {
                        totalDebit += debit;
                    }
                });
                const remainingDeposit = depositedAmount - totalDebit;

                detailsHTML += `
                    <h4>Checkout Details</h4>
                    <p><strong>Date:</strong> ${checkoutDetailsFromMainAPI.date ?? 'N/A'}</p>
                    <p><strong>Reason:</strong> ${checkoutDetailsFromMainAPI.reason ?? 'N/A'}</p>
                    <p><strong>Deposited Amount:</strong> ${depositedAmount.toFixed(2)}</p>
                    <p><strong>Total Debit:</strong> ${totalDebit.toFixed(2)}</p>
                    <p><strong>Remaining Deposit:</strong> ${remainingDeposit.toFixed(2)}</p>
                    <p><strong>Action:</strong> ${formatAction(checkoutDetailsFromMainAPI.action)}</p>
                    <p><strong>Admin Approval:</strong> <span class="badge ${getBadgeClass(checkoutDetailsFromMainAPI.admin_approval)}">${capitalize(checkoutDetailsFromMainAPI.admin_approval)}</span></p>
                    <p><strong>Account Approval:</strong> <span class="badge ${getBadgeClass(checkoutDetailsFromMainAPI.account_approval)}">${capitalize(checkoutDetailsFromMainAPI.account_approval)}</span></p>
                    <p><strong>Remark:</strong> ${checkoutDetailsFromMainAPI.remarks ?? 'N/A'}</p>
                    <hr>
                `;

                // Display Accessory Logs from apiResponse.data
                if (accessoryLogs.length > 0) {
                    detailsHTML += `
                        <h4>Accessory Logs</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Accessory Name</th>
                                    <th>Returned</th>
                                    <th>Debit Amount</th>
                                    <th>Remark</th>
                                    <th>Logged At</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    // Fetch active accessories to map IDs to names
                    const accessoriesResponse = await fetch('http://127.0.0.1:8000/api/accessories/active');
                    if (!accessoriesResponse.ok) {
                        console.warn(`Failed to fetch active accessories, status: ${accessoriesResponse.status}. Accessory names might be "Unknown".`);
                    }
                    const accessoriesData = await accessoriesResponse.json();
                    const accessoryHeadNames = {};

                    if (accessoriesData && accessoriesData.data) {
                        accessoriesData.data.forEach(accessory => {
                            if (accessory.accessory_head && accessory.accessory_head.id) {
                                accessoryHeadNames[accessory.accessory_head.id] = accessory.accessory_head.name;
                            }
                        });
                    }

                    accessoryLogs.forEach(log => {
                        const accessoryName = log.accessory_name || accessoryHeadNames[log.accessory_head_id] || 'Unknown';
                        detailsHTML += `
                                <tr>
                                    <td>${accessoryName}</td>
                                    <td>${log.is_returned ? 'Yes' : 'No'}</td>
                                    <td>${parseFloat(log.debit_amount).toFixed(2) ?? '0.00'}</td>
                                    <td>${log.remark ?? 'N/A'}</td>
                                    <td>${log.logged_at ?? 'N/A'}</td>
                                </tr>
                        `;
                    });
                    detailsHTML += `
                                </tbody>
                            </table>
                        `;
                } else {
                    detailsHTML += `<p>No accessory logs found for this checkout.</p>`;
                }
            } // End of if (apiResponse.success === false) else block

            checkoutDetailsContent.innerHTML = detailsHTML;
            checkoutDetailsModal.show();

        } catch (error) {
            console.error('Error fetching checkout details:', error);
            checkoutDetailsContent.innerHTML = `<p class="text-danger">Failed to fetch checkout details: ${error.message}. Please try again.</p>`;
            checkoutDetailsModal.show();
        }
    }

    function getBadgeClass(status) {
        switch (status) {
            case 'approved': return 'bg-success';
            case 'denied': return 'bg-danger';
            case 'pending': return 'bg-warning text-dark';
            default: return 'bg-secondary';
        }
    }

    function capitalize(str) {
        return str ? str.charAt(0).toUpperCase() + str.slice(1) : 'Pending';
    }

    function formatAction(action) {
        if (!action) return 'N/A';
        return action.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
    }
});
</script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection