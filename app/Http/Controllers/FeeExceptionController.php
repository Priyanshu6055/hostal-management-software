<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeeException;
use App\Models\Guest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Validation\ValidationException;

class FeeExceptionController extends Controller
{

    private function apiResponse($success, $message, $data = null, $statusCode = 200, $errors = null)
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        $response['data'] = $data !== null ? $data : null;
        $response['errors'] = $errors !== null ? $errors : null;

        return response()->json($response, $statusCode);
    }


    // public function store(Request $request)
    // {
    //     // Step 1: Validate input
    //     $validator = Validator::make($request->all(), [
    //         'guest_id'       => 'required|exists:guests,id',
    //         'created_by'     => 'required|integer',
    //         'hostel_fee'     => 'required|numeric|min:0',
    //         'caution_money'  => 'required|numeric|min:0',
    //         'facility'       => 'nullable|string|max:255',
    //         'remarks'        => 'nullable|string',
    //         'approved_by'    => 'nullable|string|max:255',
    //         'document'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
    //     ]);

    //     // Step 2: Return validation errors if any
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'data' => null,
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     // Step 3: Get validated input
    //     $validated = $validator->validated();

    //     try {
    //         // Step 4: Fetch guest and check conditions
    //         $guest = Guest::findOrFail($validated['guest_id']);

    //         if ($guest->status != 'waiver_approved') {
    //             return $this->apiResponse(false, 'Waiver is not approved.', null, 400);
    //         } elseif ($guest->fee_waiver === 0) {
    //             return $this->apiResponse(false, 'Guest did not apply for waiver.', null, 400);
    //         } elseif ($guest->status === 'paid') {
    //             return $this->apiResponse(false, 'Guest has already paid.', null, 400);
    //         }

    //         // Step 5: Prepare data
    //         $data = [
    //             'guest_id'       => $validated['guest_id'],
    //             'created_by'     => $validated['created_by'],
    //             'hostel_fee'     => $validated['hostel_fee'],
    //             'caution_money'  => $validated['caution_money'],
    //             'total_amount'   => $validated['hostel_fee'] + $validated['caution_money'],
    //             'facility'       => $validated['facility'] ?? null,
    //             'remarks'        => $validated['remarks'] ?? null,
    //             'approved_by'    => $validated['approved_by'] ?? null,
    //         ];

    //         // Step 6: Handle document upload
    //         if ($request->hasFile('document')) {
    //             $file = $request->file('document');
    //             $filename = time() . '_' . $file->getClientOriginalName();
    //             $path = $file->storeAs('fee_documents', $filename, 'public');
    //             $data['document_path'] = 'storage/' . $path;
    //         }

    //         // Step 7: Create or update fee exception
    //         $feeException = FeeException::updateOrCreate(
    //             ['guest_id' => $data['guest_id']],
    //             $data
    //         );

    //         // Step 8: Update guest status (optional)
    //         $guest->status = 'waiver_approved';
    //         $guest->save();

    //         // Step 9: Return success response
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Fee exception saved successfully.',
    //             'data' => $feeException,
    //             'errors' => null
    //         ]);
    //     } catch (Exception $e) {
    //         Log::error('Fee exception store error: ' . $e->getMessage());

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An unexpected error occurred',
    //             'data' => null,
    //             'errors' => ['exception' => [$e->getMessage()]]
    //         ], 500);
    //     }
    // }




    public function store(Request $request)
    {
        // Step 1: Validate input
        $validator = Validator::make($request->all(), [
            'guest_id'       => 'required|exists:guests,id',
            'created_by'     => 'required|integer',
            'hostel_fee'     => 'required|numeric|min:0',
            'caution_money'  => 'required|numeric|min:0',
            'facility'       => 'nullable|string|max:255',
            'remarks'        => 'nullable|string',
            'approved_by'    => 'nullable|string|max:255',
            'months'         => 'nullable|integer|min:0',
            'days'           => 'nullable|integer|min:0',
            'document'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Step 2: Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $validator->errors()
            ], 422);
        }

        // Step 3: Get validated input
        $validated = $validator->validated();

        try {
            // Step 4: Fetch guest and check conditions
            $guest = Guest::findOrFail($validated['guest_id']);

            if ($guest->status != 'waiver_approved') {
                return $this->apiResponse(false, 'Waiver is not approved.', null, 400);
            } elseif ($guest->fee_waiver === 0) {
                return $this->apiResponse(false, 'Guest did not apply for waiver.', null, 400);
            } elseif ($guest->status === 'paid') {
                return $this->apiResponse(false, 'Guest has already paid.', null, 400);
            }

            // Step 5: Prepare data
            $data = [
                'guest_id'       => $validated['guest_id'],
                'created_by'     => $validated['created_by'],
                'hostel_fee'     => $validated['hostel_fee'],
                'caution_money'  => $validated['caution_money'],
                'total_amount'   => $validated['hostel_fee'] + $validated['caution_money'],
                'facility'       => $validated['facility'] ?? null,
                'remarks'        => $validated['remarks'] ?? null,
                'approved_by'    => $validated['approved_by'] ?? null,
            ];

            // Step 6: Handle document upload
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('fee_documents', $filename, 'public');
                $data['document_path'] = 'storage/' . $path;
            }

            // Step 7: Create or update fee exception
            $feeException = FeeException::updateOrCreate(
                ['guest_id' => $data['guest_id']],
                $data
            );

            // Step 8: Update guest status, months, and days
            $guest->status = 'waiver_approved';
            if (isset($validated['months'])) {
                $guest->months = $validated['months'];
            }
            if (isset($validated['days'])) {
                $guest->days = $validated['days'];
            }
            $guest->save();

            // Step 9: Return success response
            return response()->json([
                'success' => true,
                'message' => 'Fee exception saved successfully.',
                'data' => $feeException,
                'errors' => null
            ]);
        } catch (Exception $e) {
            Log::error('Fee exception store error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'data' => null,
                'errors' => ['exception' => [$e->getMessage()]]
            ], 500);
        }
    }




    public function adminWaiverApproved(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'guest_id' => 'required|exists:guests,id'
            ]);

            $guest = Guest::findOrFail($validatedData['guest_id']);

            if ($guest->status === 'waiver_approved') {
                return $this->apiResponse(false, 'Waiver already approved.', null, 400);
            } elseif ($guest->fee_waiver === 0) {
                return $this->apiResponse(false, 'Guest not applyed for waiver', null, 400);
            } elseif ($guest->status === 'paid') {
                return $this->apiResponse(false, 'Guest is already paid', null, 400);
            }

            $guest->status = 'waiver_approved';
            $guest->save();

            return $this->apiResponse(true, 'Guest waiver approved successfully.', [
                'guest' => [
                    'id' => $guest->id,
                    'status' => $guest->status,
                ]
            ]);
        } catch (ValidationException $e) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $e->errors());
        } catch (Exception $e) {
            return $this->apiResponse(false, 'An error occurred while approving guest.', null, 500, ['error' => $e->getMessage()]);
        }
    }
}
