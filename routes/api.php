<?php
use Carbon\Carbon;
use App\Models\User;
use App\Models\Payment;
use App\Services\SmsService;
use Illuminate\Http\Request;
use App\Services\MailService;
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
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\RoomChangeMessageController;
use App\Http\Controllers\FeeExceptionController;
use App\Notifications\CustomAppNotification;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Apis\V1\LoginController;



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



// Route::middleware(['auth:sanctum'])->get('/admin-dashboard', function () {
//     return response()->json(['message' => 'Welcome Admin']);
// });




Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Guest Routes 
Route::post('/guests', [GuestController::class, 'register']); // Guest registers
Route::post('/guest/login', [LoginController::class, 'guestLogin'])->name('guest.guest_login');

//Guest API Auth
Route::middleware(['guest_api_auth'])->group(function(){
    Route::get('/guest/approved-rejected-guest', [GuestController::class, 'getApprovedOrRejectedGuests']);    
    Route::get('/guest/{id}/total-amount', [GuestController::class, 'getGuestTotalAmount']);
    Route::get('/guests/paid', [GuestController::class, 'getPaidGuests']);
    Route::get('/guests/pending', [GuestController::class, 'pendingGuests']);

    Route::post('guests/guest-payments', [PaymentController::class, 'guestPayment']); // Guest makes payment
});

    Route::post('admin/login', [LoginController::class, 'adminLogin']);
    Route::post('authenticate-users', [LoginController::class, 'AuthenticateUsers']);
    Route::post('logout', [LoginController::class, 'logout']);

Route::middleware(['admin_api_auth'])->group(function(){

    Route::get('admin/buildings', [BuildingController::class, 'index']); // Get all buildings
    Route::post('admin/buildings/create', [BuildingController::class, 'store']);
    Route::get('admin/buildings/{id}', [BuildingController::class, 'show']); // Get single building
    Route::put('admin/buildings/{id}', [BuildingController::class, 'update']); // Update building
    Route::delete('admin/buildings/{id}', [BuildingController::class, 'destroy']); // Delete building

    // Route::apiResource('rooms', RoomController::class);
    Route::get('admin/buildings/{id}/rooms', [RoomController::class, 'getRooms']); //Total Rooms status of a building
    Route::get('admin/rooms', [RoomController::class, 'index']); // Get all rooms from all buildings
    Route::get('admin/rooms/{id}', [RoomController::class, 'show']); // Get single room
    Route::post('admin/rooms/create', [RoomController::class, 'store']); // Create room
    Route::put('admin/rooms/{id}', [RoomController::class, 'update']); // Update room
    Route::delete('admin/rooms/{id}', [RoomController::class, 'destroy']); // Delete room

    // Route::get('/admin/check-rooms', [AdminController::class, 'checkAvailableRooms']); // Check available rooms

    // Route::apiResource('beds', BedController::class);
    Route::get('admin/beds', [BedController::class, 'index']); // Get all beds
    Route::get('admin/beds/{id}', [BedController::class, 'show']); // Get single bed
    Route::post('admin/beds/create', [BedController::class, 'store']); // Create bed
    Route::put('admin/beds/update/{id}', [BedController::class, 'update']); // Update bed
    Route::delete('admin/beds/{id}', [BedController::class, 'destroy']); // Delete bed
    Route::get('admin/rooms/{room_id}/available-beds', [BedController::class, 'getAvailableBeds']);

    //FETCH RESIDENTS
    Route::get('admin/residents', [ResidentController::class, 'getAllResidents']);
    Route::get('admin/residents/unassigned', [ResidentController::class, 'getUnassignedResidents']);
    Route::get('admin/residents/{id}', [ResidentController::class, 'getResidentById']);
    Route::post('/admin/assign-bed', [ResidentController::class, 'assignBed']); // Assign bed to resident

    //For Guests Apis
    Route::get('/admin/guests/pending', [GuestController::class, 'pendingGuestsForAccountant']);
    Route::get('/admin/guests/status', [GuestController::class, 'guestsStatus']);
    Route::post('/admin/approve-guest', [AdminController::class, 'guestApproval']); // Send payment request

    //For Waiver Guests
    // Route::post('/admin/approved-waiver', [FeeExceptionController::class, 'adminWaiverApproved']); // Send payment request
    Route::post('/admin/modify-waiver/payments', [FeeExceptionController::class, 'store']); // Send payment request
    Route::post('/admin/reject-waiver', [FeeExceptionController::class, 'waiverRejected']); // Send payment request

    Route::get('/admin/allPendingPayments', [PaymentController::class, 'getAllPendingPayments']);

    // Acccessories
    Route::get('admin/accessories', [AccessoryController::class, 'getAllAccessories']);
    Route::post('admin/accessories/create-or-update', [AccessoryController::class, 'createOrUpdate']);
    // Route::put('admin/accessories/{id}', [AccessoryController::class, 'update']);
    Route::post('admin/assign-accessories', [StudentAccessoryController::class, 'adminSendAccessoryToResident']);
    Route::get('admin/accessories/active', [AccessoryController::class, 'getActiveAccessories']);
    Route::get('admin/accessories/{resident_id}', [CheckoutController::class, 'getAccessoryByResidentId']);
    // Route::get('admin/default/accessories/{resident_id}', [CheckoutController::class, 'getDefaultAccessoryByResidentId']);



    //Accessory head 
    Route::prefix('admin/accessories-master')->group(function () {
        Route::post('/add', [AccessoryHeadController::class, 'store']);
        Route::post('/update/{id}', [AccessoryHeadController::class, 'update']); // No PUT, use POST
        Route::get('/', [AccessoryHeadController::class, 'index']);
    });


    Route::post('admin/staff/create', [StaffController::class, 'createStaff']);
    Route::get('admin/staff-list', [StaffController::class, 'getAllStaff']);
    Route::get('admin/staff/{id}', [StaffController::class, 'getStaffDetails']);
    Route::put('admin/staff/update/{id}', [StaffController::class, 'updateStaff']);

    // Admin fetches all room change requests
    Route::get('admin/room-change/requests', [RoomChangeController::class, 'getAllRoomChangeRequests']);
    // Route::get('admin/room-change/requests', [RoomChangeController::class, 'getAllRequests']);

    // Admin responds with available/not available + optional remark
    Route::post('admin/room-change/respond/{request_id}', [RoomChangeController::class, 'respondToRequest']);
    // Send a message in the conversation
    Route::post('admin/room-change/message/{request_id}', [RoomChangeMessageController::class, 'sendMessage']);
    // Fetch all messages in a conversation
    Route::get('admin/room-change/all-messages/{request_id}', [RoomChangeMessageController::class, 'getMessages']);

    // ➡️ Final Approval by Admin when resident has agreed
    Route::post('admin/room-change/final-approval/{request_id}', [RoomChangeController::class, 'finalApproval']);
    Route::put('admin/room-change/deny/{request_id}', [RoomChangeController::class, 'denyRoomChangeByAdmin']);

    //Checkout Process
    Route::put('admin/accessory/checking/{residentId}', [CheckoutController::class, 'adminAccessoryChecking']);
    Route::put('admin/checkout/admin-approval/{id}', [CheckoutController::class, 'adminApproval']); // Admin final approval
    Route::get('admin/resident/all-checkout-requests', [CheckoutController::class, 'getAllCheckoutRequests']);
    Route::get('admin/resident-checkout-logs/{residentId}', [CheckoutController::class, 'adminGetCheckoutLogs']);

    // leave request APIs
    Route::post('admin/residents/{resident_id}/leave', [LeaveRequestController::class, 'store']);
    Route::patch('admin/leave-requests/{id}/hod-approve', [LeaveRequestController::class, 'hodApprove']);
    Route::patch('admin/leave-requests/{id}/hod-deny', [LeaveRequestController::class, 'hodDeny']);
    Route::get('admin/leave-requests', [LeaveRequestController::class, 'index']);
    Route::patch('admin/leave-requests/{id}/admin-approve', [LeaveRequestController::class, 'adminApprove']);
    Route::patch('admin/leave-requests/{id}/admin-deny', [LeaveRequestController::class, 'adminDeny']);
    Route::get('admin/residents/{residentId}/leave-requests', [LeaveRequestController::class, 'leaveReqById']);



    // Admin Created Resident
    Route::post('admin/residents/create', [AdminController::class, 'createResident']);


    // Accountant Routes
    Route::put('accountant/checkout/account-approval/{id}', [CheckoutController::class, 'accountApproval']); // Accounts approval
    Route::post('accountant/update-guest-status', [FeeExceptionController::class, 'updateGuestStatusWithRemark']);


    //Super Admin Routes    
    Route::post('admin/universities/create', [UniversityController::class, 'store']);
    Route::get('admin/universities', [UniversityController::class, 'index']);
    Route::put('admin/universities/{id}', [UniversityController::class, 'update']);

    Route::post('admin/create-admin', [SuperAdminController::class, 'createAdmin']);
    Route::get('admin/admins', [SuperAdminController::class, 'getAdmins']);
    Route::get('admin/admins/{id}', [SuperAdminController::class, 'getAdmin']);
    Route::put('admin/admins/{id}', [SuperAdminController::class, 'updateAdmin']);
    // Route::delete('admin/admins/{id}', [SuperAdminController::class, 'deleteAdmin']);


    //Resident Routes
    // Resident requests room change
    Route::post('resident/room-change/request/{resident_id}', [RoomChangeController::class, 'requestRoomChange']);
    // Resident fetches their room change requests
    Route::get('resident/room-change/requests', [RoomChangeController::class, 'getRoomChangeRequests']);
    Route::get('resident/room-change/requests/{id}', [RoomChangeController::class, 'getRoomChangeRequestsById']);
    Route::get('/resident/{residentId}/room-change-requests', [RoomChangeController::class, 'getRoomChangeRequestsByResidentId']);
    //Accessory Routes
    Route::post('resident/{resident_id}/accessories', [StudentAccessoryController::class, 'addAccessory']); // Resident Add Accessory
    Route::get('resident/{resident_id}/accessories', [PaymentController::class, 'getAccessoryPendingPayments']);
    Route::post('resident/{resident_id}/accessories/{accessory_id}/pay', [StudentAccessoryController::class, 'payAccessory']);

    Route::post('resident/checkout/request', [CheckoutController::class, 'requestCheckout']);
    Route::get('resident/checkout-status', [CheckoutController::class, 'getCheckoutStatus']);
    Route::get('resident/checkout-logs', [CheckoutController::class, 'getCheckoutLogs']);









// Resident respond 
Route::post('/room-change/respond-to-admin/{request_id}', [RoomChangeController::class, 'respondToAdmin']);

// Resident sends agree/deny
Route::post('/room-change/confirm-by-resident/{request_id}', [RoomChangeController::class, 'confirmRoomChange']);


});


    Route::get('/accountant/guests/pending', [GuestController::class, 'pendingGuestsForAccountant']);






Route::post('/feedbacks/{resident_id}', [FeedbackController::class, 'store']);  // Submit Feedback
Route::get('/feedbacks', [FeedbackController::class, 'index']);   // View All Feedbacks
Route::get('/feedbacks/{id}', [FeedbackController::class, 'show']); // View Single Feedback


Route::post('/notices', [NoticeController::class, 'store']); // Create Notice
Route::get('/notices', [NoticeController::class, 'index']); // Get All Notices
Route::get('/notices/{id}', [NoticeController::class, 'show']); // Get Single Notice
Route::put('/notices/{id}', [NoticeController::class, 'update']); // Update Notice
Route::delete('/notices/{id}', [NoticeController::class, 'destroy']); // Delete Notice






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

Route::get('/payments/all/resident/{resident_id}', [PaymentController::class, 'getAllPaymentsByResidentId']);
Route::get('/combined/pending/subscription', [SubscriptionController::class, 'getCombinedSubscription']);
Route::post('/admin/subscribe-resident', [SubscriptionController::class, 'adminSubscribeResident']);

// Fine Routes
Route::post('/admin/fine', [FineController::class, 'adminSetFine']);
Route::post('/accountant/set-fine-amount', [FineController::class, 'accountantSetFineAmount']);
Route::get('/accountant/view-fine-details', [FineController::class, 'viewAllFineDetails']);


Route::get('/resident/all', [StaffController::class, 'getresidents']);

Route::get('/subscription_payment', function () {
    return view('resident.subscription_payment');
})->name('resident.subscription.payment');




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



//In app notifications
Route::post('/send-notification', function (Request $request) {
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'message' => 'required|string'
    ]);

    $user = User::find($request->user_id);
    $user->notify(new CustomAppNotification($request->message));

    return response()->json(['success' => true, 'message' => 'Notification sent.']);
});



//AWS SMS Service for test
Route::post('/send-sms', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'phone' => 'required|string',
        'message' => 'required|string|max:160'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }

    $response = SmsService::send($request->phone, $request->message);

    return response()->json([
        'success' => $response['success'],
        'message' => $response['success'] ? 'SMS sent successfully' : 'SMS failed to send',
        'data' => $response,
    ]);
});



//AWS Mail Service for test
Route::post('/send-mail', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'subject' => 'required|string',
        'name' => 'required|string',
        'body' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    $response = MailService::send(
        $request->email,
        $request->subject,
        'emails.hostel_welcome',
        [
            'name' => $request->name,
            'body' => $request->body
        ]
    );

    return response()->json([
        'success' => $response['success'],
        'message' => $response['message'] ?? 'Mail operation done',
        'data' => $response
    ]);
});



// Notification API Routes
Route::prefix('notifications')->group(function () {

    Route::get('resident/{residentId}', [NotificationController::class, 'getPaginatedResidentNotifications']);

    Route::get('resident/{residentId}/all', [NotificationController::class, 'getAllResidentNotifications']);

    Route::get('resident/{residentId}/unread', [NotificationController::class, 'getUnreadResidentNotifications']);

    Route::post('{id}/read', [NotificationController::class, 'markNotificationAsRead']);

    Route::get('payments', [NotificationController::class, 'getPaymentNotifications']);
});
