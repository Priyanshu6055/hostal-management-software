<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\GuestAccessory;
use App\Models\Accessory;
use App\Models\Fee;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class GuestController extends Controller
{

    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:guests,email',
                'gender' => 'required|in:Male,Female,Other',
                'scholar_no' => 'required|unique:guests,scholar_no',
                'fathers_name' => 'required|string|max:255',
                'mothers_name' => 'required|string|max:255',
                'local_guardian_name' => 'nullable|string|max:255',
                'emergency_no' => 'required|string|max:20',
                'room_preference' => 'required|string|max:255', // Consider Rule::in(['Single', 'Double', 'Triple']) for better validation
                'food_preference' => 'required|string|max:255', // Consider Rule::in(['Veg', 'Non-Veg']) for better validation
                'months' => 'nullable|integer|min:1|max:12',
                'accessory_head_ids' => 'nullable|array',
                'accessory_head_ids.*' => 'exists:accessory_heads,id',
                // New fields:
                'fee_waiver' => 'boolean', // It will be true/false (0/1 from checkbox)
                'remarks' => [
                    'nullable',
                    'string',
                    'max:1000',
                    'required_if:fee_waiver,true', // Required only if fee_waiver is true
                ],
                'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // <--- NEW: Optional file attachment (Max 5MB)
            ]);

            // Start a database transaction
            DB::beginTransaction();

            $months = $validatedData['months'] ?? 3;

            $attachmentPath = null;
            // Handle attachment upload if a file is present
            if ($request->hasFile('attachment')) {
                // Store the file in the 'attachments' directory within the 'public' disk.
                // This will typically save to `storage/app/public/attachments/`
                // and be publicly accessible via `your-app-url/storage/attachments/filename.ext`
                $attachmentPath = $request->file('attachment')->store('attachments', 'public');
            }

            // Prepare guest data for creation
            $guestData = collect($validatedData)->except(['accessory_head_ids', 'attachment'])->toArray();
            $guestData['months'] = $months;
            $guestData['attachment_path'] = $attachmentPath; // Add the attachment path

            // Ensure fee_waiver is a proper boolean, as FormData might send 'true'/'false' strings
            $guestData['fee_waiver'] = filter_var($validatedData['fee_waiver'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // Create the Guest record
            $guest = Guest::create($guestData);

            // Handle accessories if provided
            if (!empty($validatedData['accessory_head_ids'])) {
                $fromDate = Carbon::now();
                $toDate = Carbon::now()->addMonths($months);

                foreach ($validatedData['accessory_head_ids'] as $headId) {
                    $accessory = Accessory::where('accessory_head_id', $headId)
                        ->where('is_active', true)
                        ->latest('from_date')
                        ->first();

                    if ($accessory) {
                        GuestAccessory::create([
                            'guest_id' => $guest->id,
                            'accessory_head_id' => $headId,
                            'price' => $accessory->price,
                            'total_amount' => $accessory->price * $months,
                            'from_date' => $fromDate,
                            'to_date' => $toDate
                        ]);
                    }
                }
            }

            // Commit the transaction if everything was successful
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Guest registered successfully.',
                'data' => $guest,
                'errors' => null
            ], 201);
        } catch (ValidationException $e) {
            // Rollback the transaction on validation failure
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) { // Catching a general Exception for broader error handling
            // Rollback the transaction on any other unexpected error
            DB::rollBack();
            Log::error('Guest registration failed: ' . $e->getMessage(), ['exception' => $e]); // Log the full exception

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong during guest registration.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }


    // public function getGuestTotalAmount(Request $request, $guest_id)
    // {
    //     try {
    //         $guest = Guest::findOrFail($guest_id);

    //         $months = $guest->months ?? 1;

    //         $guestAccessories = GuestAccessory::where('guest_id', $guest_id)->get();
    //         $accessoryTotal = $guestAccessories->sum('total_amount');
    //         $accessoryHeadIds = $guestAccessories->pluck('accessory_head_id');

    //         $hostelFeePerMonth = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Hostel Fee'))
    //             ->where('is_active', true)
    //             ->latest('from_date')
    //             ->value('amount') ?? 0;

    //         $messFeePerMonth = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Mess Fee'))
    //             ->where('is_active', true)
    //             ->latest('from_date')
    //             ->value('amount') ?? 0;

    //         $cautionMoney = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Caution Money'))
    //             ->where('is_active', true)
    //             ->latest('from_date')
    //             ->value('amount') ?? 0;

    //         $hostelFee = $hostelFeePerMonth * $months;
    //         $messFee = $messFeePerMonth * $months;

    //         $finalTotal = $accessoryTotal + $hostelFee + $messFee + $cautionMoney;

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Guest total amount fetched successfully.',
    //             'data' => [
    //                 'guest_id' => $guest_id,
    //                 'months' => $months,
    //                 'total_accessory_amount' => $accessoryTotal,
    //                 'hostel_fee' => $hostelFee + $messFee,
    //                 'caution_money' => $cautionMoney,
    //                 'final_total_amount' => $finalTotal,
    //                 'accessory_head_ids' => $accessoryHeadIds,
    //             ],
    //             'errors' => null
    //         ]);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Guest not found',
    //             'data' => null,
    //             'errors' => null
    //         ], 404);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch data',
    //             'data' => null,
    //             'errors' => ['exception' => $e->getMessage()]
    //         ], 500);
    //     }
    // } old method


    // public function getGuestTotalAmount(Request $request, $guest_id)
    // {
    //     try {
    //         $guest = Guest::findOrFail($guest_id);
    //         $months = $guest->months ?? 1;

    //         // Get all guest accessories and total
    //         $guestAccessories = GuestAccessory::where('guest_id', $guest_id)->get();
    //         $accessoryTotal = $guestAccessories->sum('total_amount');
    //         $accessoryHeadIds = $guestAccessories->pluck('accessory_head_id');

    //         $hostelFee = 0;
    //         $messFee = 0;
    //         $cautionMoney = 0;
    //         $waiverFeeUpdated = false;

    //         // If waiver is approved, pull from fee_exceptions
    //         if ($guest->status === 'waiver_approved') {
    //             $feeException = \App\Models\FeeException::where('guest_id', $guest_id)->first();

    //             if ($feeException) {
    //                 $hostelFee = $feeException->hostel_fee ?? 0;
    //                 $cautionMoney = $feeException->caution_money ?? 0;
    //                 $waiverFeeUpdated = true;
    //             }
    //         }

    //         // If not waiver_approved or exception not found, use regular fee table
    //         if (!$waiverFeeUpdated) {
    //             $hostelFeePerMonth = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Hostel Fee'))
    //                 ->where('is_active', true)
    //                 ->latest('from_date')
    //                 ->value('amount') ?? 0;

    //             $messFeePerMonth = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Mess Fee'))
    //                 ->where('is_active', true)
    //                 ->latest('from_date')
    //                 ->value('amount') ?? 0;

    //             $cautionMoney = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Caution Money'))
    //                 ->where('is_active', true)
    //                 ->latest('from_date')
    //                 ->value('amount') ?? 0;

    //             $hostelFee = $hostelFeePerMonth * $months;
    //             $messFee = $messFeePerMonth * $months;
    //         }

    //         $finalTotal = $accessoryTotal + $hostelFee + $messFee + $cautionMoney;

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Guest total amount fetched successfully.',
    //             'data' => [
    //                 'guest_id' => $guest_id,
    //                 'months' => $months,
    //                 'total_accessory_amount' => $accessoryTotal,
    //                 'hostel_fee' => $hostelFee + $messFee,
    //                 'caution_money' => $cautionMoney,
    //                 'final_total_amount' => $finalTotal,
    //                 'accessory_head_ids' => $accessoryHeadIds,
    //                 'waiver_fee_updated' => $waiverFeeUpdated,
    //             ],
    //             'errors' => null
    //         ]);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Guest not found',
    //             'data' => null,
    //             'errors' => null
    //         ], 404);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch data',
    //             'data' => null,
    //             'errors' => ['exception' => $e->getMessage()]
    //         ], 500);
    //     }
    // } old without days


    public function getGuestTotalAmount(Request $request, $guest_id)
    {
        try {
            $guest = Guest::select('id', 'months', 'days', 'status', 'fee_waiver')->findOrFail($guest_id);

            $months = $guest->months ?? 1;
            $days = $guest->days ?? 0;

            $guestAccessories = GuestAccessory::where('guest_id', $guest_id)->get();
            $accessoryTotal = $guestAccessories->sum('total_amount');
            $accessoryHeadIds = $guestAccessories->pluck('accessory_head_id');

            $hostelFee = 0;
            $messFee = 0;
            $cautionMoney = 0;
            $waiverFeeUpdated = false;

            if ($guest->status === 'waiver_approved') {
                $feeException = \App\Models\FeeException::where('guest_id', $guest_id)->first();

                if ($feeException) {
                    $hostelFee = $feeException->hostel_fee ?? 0;
                    $cautionMoney = $feeException->caution_money ?? 0;
                    $waiverFeeUpdated = true;
                }
            }

            if (!$waiverFeeUpdated) {
                $hostelFeePerMonth = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Hostel Fee'))
                    ->where('is_active', true)
                    ->latest('from_date')
                    ->value('amount') ?? 0;

                $messFeePerMonth = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Mess Fee'))
                    ->where('is_active', true)
                    ->latest('from_date')
                    ->value('amount') ?? 0;

                $cautionMoney = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Caution Money'))
                    ->where('is_active', true)
                    ->latest('from_date')
                    ->value('amount') ?? 0;

                $hostelFee = $hostelFeePerMonth * $months;
                $messFee = $messFeePerMonth * $months;
            }

            $finalTotal = $accessoryTotal + $hostelFee + $messFee + $cautionMoney;

            return response()->json([
                'success' => true,
                'message' => 'Guest total amount fetched successfully.',
                'data' => [
                    'guest_id' => $guest->id,
                    'months' => $months,
                    'days' => $days,
                    'total_accessory_amount' => $accessoryTotal,
                    'hostel_fee' => $hostelFee + $messFee,
                    'caution_money' => $cautionMoney,
                    'final_total_amount' => $finalTotal,
                    'accessory_head_ids' => $accessoryHeadIds,
                    'waiver_fee_updated' => $waiverFeeUpdated,
                ],
                'errors' => null
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Guest not found',
                'data' => null,
                'errors' => null
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }




    public function pendingGuests()
    {
        try {
            // Only fetch guests whose status is NOT 'paid' or 'rejected'
            $guests = Guest::with([
                'accessories.accessoryHead:id,name'
            ])->whereNotIn('status', ['paid', 'approved', 'rejected'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Pending guests with accessories fetched successfully',
                'data' => $guests,
                'errors' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending guests',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }


    public function getPaidGuests()
    {
        try {
            $guests = Guest::where('status', 'paid')->get();

            if ($guests->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No guests with paid status found.',
                    'data' => [],
                    'errors' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Paid guests fetched successfully.',
                'data' => $guests,
                'errors' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }


    public function getApprovedOrRejectedGuests()
    {
        try {
            $guests = Guest::whereIn('status', ['approved', 'rejected', 'pending','waiver_approved'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Approved, rejected, or pending guests retrieved successfully',
                'data' => $guests,
                'errors' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch guests',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }


    public function showPendingGuests()
    {
        $guests = Guest::with(['accessories.accessoryHead:id,name'])
            ->whereNotIn('status', ['paid', 'approved', 'rejected'])
            ->get();

        return view('admin.Pending_guest', compact('guests'));
    }
}
