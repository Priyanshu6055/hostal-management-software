<?php

use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\AccessoryController;
use App\Http\Controllers\StudentAccessoryController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\RoomChangeController;
use App\Http\Controllers\GrievanceController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Middleware\CheckValidReferer;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\MessController;
use App\Http\Controllers\FeeHeadController;
use App\Http\Controllers\AccessoryHeadController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Payment;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoomChangeMessageController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::middleware(['auth:sanctum'])->get('/admin-dashboard', function () {
    return response()->json(['message' => 'Welcome Admin']);
});



// Route::post('/login', function (Request $request) {
//     $request->validate([
//         'email' => 'required|email',
//         'password' => 'required',
//     ]);

//     $user = User::where('email', $request->email)->first();

//     if (! $user || ! Hash::check($request->password, $user->password)) {
//         return response()->json(['message' => 'Invalid credentials'], 401);
//     }

//     $tokenResult = $user->createToken('api-token');
//     $token = $tokenResult->plainTextToken;

//     // Manually update expires_at
//     $tokenResult->accessToken->expires_at = Carbon::now()->addMinutes(1);
//     $tokenResult->accessToken->save();

//     return response()->json([
//         'token' => $token,
//         'user' => $user,
//         'expires_at' => Carbon::now()->addMinutes(10),
//     ]);
// });

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('/create-admin', [SuperAdminController::class, 'createAdmin']);
Route::get('/admins', [SuperAdminController::class, 'getAdmins']);
Route::get('/admins/{id}', [SuperAdminController::class, 'getAdmin']);
Route::put('/admins/{id}', [SuperAdminController::class, 'updateAdmin']);
Route::delete('/admins/{id}', [SuperAdminController::class, 'deleteAdmin']);



Route::post('/universities', [UniversityController::class, 'store']);
Route::get('/universities', [UniversityController::class, 'index']);
Route::put('/universities/{id}', [UniversityController::class, 'update']);


// Route::apiResource('rooms', RoomController::class);
Route::get('/rooms', [RoomController::class, 'index']); // Get all rooms
Route::get('/rooms/{id}', [RoomController::class, 'show']); // Get single room
Route::post('/rooms', [RoomController::class, 'store']); // Create room
Route::put('/rooms/{id}', [RoomController::class, 'update']); // Update room
Route::delete('/rooms/{id}', [RoomController::class, 'destroy']); // Delete room
Route::get('/buildings/{id}/rooms', [RoomController::class, 'getRooms']);


// Route::apiResource('beds', BedController::class);
Route::get('/beds', [BedController::class, 'index']); // Get all beds
Route::get('/beds/{id}', [BedController::class, 'show']); // Get single bed
Route::post('/beds', [BedController::class, 'store']); // Create bed
Route::put('/beds/{id}', [BedController::class, 'update']); // Update bed
Route::delete('/beds/{id}', [BedController::class, 'destroy']); // Delete bed
Route::get('/rooms/{room_id}/available-beds', [BedController::class, 'getAvailableBeds']);



Route::post('/buildings', [BuildingController::class, 'store']);
Route::get('/buildings', [BuildingController::class, 'index']); // Get all buildings
Route::get('/buildings/{id}', [BuildingController::class, 'show']); // Get single building
Route::put('/buildings/{id}', [BuildingController::class, 'update']); // Update building
Route::delete('/buildings/{id}', [BuildingController::class, 'destroy']); // Delete building


Route::post('/staff', [StaffController::class, 'createStaff']);
Route::get('/get/staff', [StaffController::class, 'getAllStaff']);
Route::put('/staff/{id}', [StaffController::class, 'updateStaff']);



// Acccessories
Route::put('/accessories/{id}', [AccessoryController::class, 'update']);
Route::post('/resident/{resident_id}/accessories', [StudentAccessoryController::class, 'addAccessory']);
Route::get('/resident/{resident_id}/accessories', [PaymentController::class, 'getAccessoryPendingPayments']);
Route::post('/residents/{resident_id}/accessories/{accessory_id}/pay', [StudentAccessoryController::class, 'payAccessory']);
Route::post('/admin-send-accessory', [StudentAccessoryController::class, 'adminSendAccessoryToResident']);
Route::get('/accessories/active', [AccessoryController::class, 'getActiveAccessories']);
Route::get('/default/accessories/{resident_id}', [CheckoutController::class, 'getDefaultAccessoryByResidentId']);
Route::get('/accessories', [AccessoryController::class, 'getAllAccessories']);
Route::put('/admin/accessory/checking/{residentId}', [CheckoutController::class, 'adminAccessoryChecking']);



//Accessory head 
Route::prefix('accessory-heads')->group(function () {
    Route::post('/add', [AccessoryHeadController::class, 'store']);
    Route::post('/update', [AccessoryHeadController::class, 'update']); // No PUT, use POST
    Route::get('/', [AccessoryHeadController::class, 'index']);
});

Route::post('/create-or-update-accessories', [AccessoryController::class, 'createOrUpdate']);

// leave request APIs
Route::post('/residents/{resident_id}/leave', [LeaveRequestController::class, 'store']);
Route::patch('/leave-requests/{id}/hod-approve', [LeaveRequestController::class, 'hodApprove']);
Route::patch('/leave-requests/{id}/hod-deny', [LeaveRequestController::class, 'hodDeny']);
Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
Route::patch('/leave-requests/{id}/admin-approve', [LeaveRequestController::class, 'adminApprove']);
Route::patch('/leave-requests/{id}/admin-deny', [LeaveRequestController::class, 'adminDeny']);
Route::get('/residents/{residentId}/leave-requests', [LeaveRequestController::class, 'leaveReqById']);


Route::post('/residents', [AdminController::class, 'createResident']);


Route::post('/feedbacks/{resident_id}', [FeedbackController::class, 'store']);  // Submit Feedback
Route::get('/feedbacks', [FeedbackController::class, 'index']);   // View All Feedbacks
Route::get('/feedbacks/{id}', [FeedbackController::class, 'show']); // View Single Feedback


Route::post('/notices', [NoticeController::class, 'store']); // Create Notice
Route::get('/notices', [NoticeController::class, 'index']); // Get All Notices
Route::get('/notices/{id}', [NoticeController::class, 'show']); // Get Single Notice
Route::put('/notices/{id}', [NoticeController::class, 'update']); // Update Notice
Route::delete('/notices/{id}', [NoticeController::class, 'destroy']); // Delete Notice


// Route::post('/room-change/request/{resident_id}', [RoomChangeController::class, 'requestRoomChange']);
// Route::get('/room-change/requests', [RoomChangeController::class, 'getAllRequests']);
// Route::put('/room-change/respond/{request_id}', [RoomChangeController::class, 'respondToRequest']);
// Route::put('/room-change/confirm/{request_id}', [RoomChangeController::class, 'confirmRoomChange']);
// Route::get('/room-change-requests/resident/{residentId}', [RoomChangeController::class, 'getRequestsByResident']);

// Resident requests room change
Route::post('/room-change/request/{resident_id}', [RoomChangeController::class, 'requestRoomChange']);

// Admin fetches all room change requests
Route::get('/room-change/requests', [RoomChangeController::class, 'getAllRequests']);

// Resident fetches their room change requests
Route::get('/room-change-requests/{id}', [RoomChangeController::class, 'getRoomChangeRequestById']);
Route::get('/resident/{residentId}/room-change-requests', [RoomChangeController::class, 'getRoomChangeRequestsByResidentId']);


// Admin responds with available/not available + optional remark
Route::post('/room-change/respond/{request_id}', [RoomChangeController::class, 'respondToRequest']);

// Resident respond 
Route::post('/room-change/respond-to-admin/{request_id}', [RoomChangeController::class, 'respondToAdmin']);

// Resident sends agree/deny
Route::post('/room-change/confirm-by-resident/{request_id}', [RoomChangeController::class, 'confirmRoomChange']);

// ➡️ New: Chat Communication (RoomChangeMessageController)

// Send a message in the conversation
Route::post('/room-change/message/{request_id}', [RoomChangeMessageController::class, 'sendMessage']);

// Fetch all messages in a conversation
Route::get('/room-change/messages/{request_id}', [RoomChangeMessageController::class, 'getMessages']);

// ➡️ Final Approval by Admin when resident has agreed
Route::post('/room-change/final-approval/{request_id}', [RoomChangeController::class, 'finalApproval']);


Route::put('/room-change/deny/{request_id}', [RoomChangeController::class, 'denyRoomChangeByAdmin']);

Route::get('/room-change/requests', [RoomChangeController::class, 'getAllRoomChangeRequests']);



// Route::post('/grievances', [GrievanceController::class, 'submitGrievance']); // Resident submits grievance
// Route::get('/grievances', [GrievanceController::class, 'getAllGrievances']); // Admin views all grievances
// Route::get('/grievances/{id}', [GrievanceController::class, 'getGrievanceById']); // View single grievance
// Route::put('/grievances/{id}', [GrievanceController::class, 'respondToGrievance']); // Admin responds & closes grievance
// Route::get('/grievances/resident/{resident_id}', [GrievanceController::class, 'getResidentGrievances']); // Resident views grievances
// Route::delete('/grievances/{id}', [GrievanceController::class, 'deleteGrievance']); // Admin deletes grievance



Route::prefix('grievances')->group(function () {
    // Submit a grievance
    Route::post('/submit', [GrievanceController::class, 'submitGrievance']);
    
    // Admin can respond to a grievance
    Route::post('/respond/{id}', [GrievanceController::class, 'respondToGrievance']);
    
    // Resident can respond with a message and can agree/disagree with the response
    Route::post('/resident/respond/{id}', [GrievanceController::class, 'residentRespond']);
    
    // Fetch all grievances for the admin
    Route::get('/', [GrievanceController::class, 'getAllGrievances']);
    
    // Fetch grievance by ID for admin/resident
    Route::get('/{id}', [GrievanceController::class, 'getGrievanceById']);
    
    // Close grievance by resident (final resolution)
    Route::put('/close/{id}', [GrievanceController::class, 'closeGrievance']);

    Route::get('/resident/{resident_id}', [GrievanceController::class, 'getGrievancesByResident']);
});

Route::post('/checkout/request', [CheckoutController::class, 'requestCheckout']);
Route::put('/checkout/account-approval/{id}', [CheckoutController::class, 'accountApproval']); // Accounts approval
Route::put('/checkout/admin-approval/{id}', [CheckoutController::class, 'adminApproval']); // Admin final approval
Route::get('/resident/{resident_id}/checkout-status', [CheckoutController::class, 'getCheckoutStatus']);
Route::get('/checkout-requests', [CheckoutController::class, 'getAllCheckoutRequests']);
Route::get('/resident/all-checkout-requests', [CheckoutController::class, 'getAllCheckoutRequests']);
Route::put('/admin/accessory/checking/{residentId}', [CheckoutController::class, 'adminAccessoryChecking']);
Route::get('/resident-checkout-logs/{residentId}', [CheckoutController::class, 'getCheckoutLogs']);



// Guest Routes 
Route::post('/guests', [GuestController::class, 'register']); // Guest registers
Route::get('/guests/pending', [GuestController::class, 'pendingGuests']);
Route::get('/admin/check-rooms', [AdminController::class, 'checkAvailableRooms']); // Check available rooms
Route::post('/admin/approved-guest', [AdminController::class, 'adminApproved']); // Send payment request
Route::post('/guest-payments', [PaymentController::class, 'guestPayment']); // Guest makes payment
Route::post('/admin/assign-bed', [ResidentController::class, 'assignBed']); // Assign bed to resident
Route::get('/guest/{id}/total-amount', [GuestController::class, 'getGuestTotalAmount']);



//FETCH RESIDENTS
Route::get('/residents', [ResidentController::class, 'getAllResidents']);
Route::get('/residents/{id}', [ResidentController::class, 'getResidentById']);
Route::get('/admin/residents/unassigned', [ResidentController::class, 'getUnassignedResidents']);



Route::get('/fees', [FeeController::class, 'getAllFees']);
Route::put('/fees/{id}', [FeeController::class, 'updateFeeById']);
Route::post('/admin/add-fees', [FeeController::class, 'addOrUpdateFees']);


//Fee
Route::get('/fees', [FeeController::class, 'getAllFees']);
Route::get('/activeFees', [FeeController::class, 'getAllActiveFees']);
Route::post('/admin/addOrUpdateFees', [FeeController::class, 'createOrUpdate']);
Route::get('/fee-heads', [FeeHeadController::class, 'index']);


//Fee head
Route::post('/fee-heads', [FeeHeadController::class, 'store']);
Route::put('/fee-heads/{id}', [FeeHeadController::class, 'update']);


// Payment Routes
Route::post('/resident/pay', [PaymentController::class, 'payAsResident']);
Route::post('/payment/reject', [AdminController::class, 'rejectPaymentRequest']);
Route::get('/aproved/rejected/guest', [GuestController::class, 'getApprovedOrRejectedGuests']);



Route::post('/payments/{payment_id}/pay', [PaymentController::class, 'subscribePay']);
Route::get('/resident/{resident_id}/subscription', [SubscriptionController::class, 'getResidentSubscriptions']);
Route::get('/allPendisgPayments', [PaymentController::class, 'getAllPendingPayments']);

Route::post('/account/subscribe/pay', [PaymentController::class, 'accountSubscribePay']);


Route::post('/payments/pay', [PaymentController::class, 'makePayment']);
Route::get('/payments/pending/{resident_id}', [PaymentController::class, 'getPendingPayments']);
Route::get('/payments/resident/{id}', [PaymentController::class, 'getPaymentsByResident']);
Route::get('/payments', [PaymentController::class, 'getAllPayments']);


Route::post('/residents/subscribe', [SubscriptionController::class, 'subscribeToService']);
Route::post('/subscription/pay', [PaymentController::class, 'subscribePay']);
Route::get('/resident/{resident_id}/subscription', [SubscriptionController::class, 'getResidentSubscriptions']);
Route::get('/pending/{resident_id}/subscription', [SubscriptionController::class, 'getPendingResidentSubscriptions']);
Route::post('/admin/subscribe-resident', [SubscriptionController::class, 'adminSubscribeResident']);
Route::get('/payments/all/resident/{resident_id}', [PaymentController::class, 'getAllPaymentsByResidentId']);
Route::get('/combined/pending/subscription', [SubscriptionController::class, 'getCombinedSubscription']);



Route::get('/resident/all', [StaffController::class, 'getresidents']);
Route::get('/subscription_payment', function () {
    return view('resident.subscription_payment');
})->name('resident.subscription.payment');


Route::get('/allPendingPayments', [PaymentController::class, 'getAllPendingPayments']);


Route::get('/get-payment-id', function (Request $request) {
    $residentId = $request->query('resident_id');
    $subscriptionId = $request->query('subscription_id');

    //Log::info("Fetching payment ID for Resident ID: $residentId, Subscription ID: $subscriptionId ");

    $payment = Payment::where('resident_id', $residentId)
        ->where('subscription_id', $subscriptionId)
        ->first();

    if ($payment) {
        // Log::info("Payment ID found: " . $payment->id);
        return response()->json(['payment_id' => $payment->id]);
    } else {
        // Log::warning("No payment ID found for Resident ID: $residentId, Subscription ID: $subscriptionId");
        return response()->json(['error' => 'Payment ID not found'], 404);
    }
});


Route::get('/messes', [MessController::class, 'index']);
Route::get('/guests/paid', [GuestController::class, 'getPaidGuests']);
