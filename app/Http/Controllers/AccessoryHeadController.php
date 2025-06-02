<?php

namespace App\Http\Controllers;

use App\Models\AccessoryHead;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class AccessoryHeadController extends Controller
{
    private function apiResponse($success, $message, $data = null, $statusCode = 200, $errors = null)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data ?? null,
            'errors' => $errors ?? null
        ], $statusCode);
    }

    // ✅ Add new Accessory Head
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:accessory_heads,name',
            'created_by' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $validator->errors());
        }

        try {
            $accessoryHead = AccessoryHead::create([
                'name' => $request->name,
                'created_by' => $request->created_by,
            ]);

            return $this->apiResponse(true, 'Accessory head added successfully.', $accessoryHead, 201);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Error creating accessory head.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // 🔁 Update Accessory Head
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:accessory_heads,name,' . $id,
            'created_by' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $validator->errors());
        }

        try {
            $accessoryHead = AccessoryHead::findOrFail($id);

            $accessoryHead->update([
                'name' => $request->name,
                'created_by' => $request->created_by,
            ]);

            return $this->apiResponse(true, 'Accessory head updated successfully.', $accessoryHead);
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Accessory head not found.', null, 404, [
                'error' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Error updating accessory head.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // 📄 Get All Accessory Heads
    public function index()
    {
        try {
            $accessoryHeads = AccessoryHead::all();
            return $this->apiResponse(true, 'Accessory heads fetched successfully.', $accessoryHeads);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch accessory heads.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // 📄 Get Accessory Head By ID
    public function show($id)
    {
        try {
            $accessoryHead = AccessoryHead::findOrFail($id);
            return $this->apiResponse(true, 'Accessory head fetched successfully.', $accessoryHead);
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Accessory head not found.', null, 404, [
                'error' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch accessory head.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // ❌ Delete Accessory Head
    public function destroy($id)
    {
        try {
            $accessoryHead = AccessoryHead::findOrFail($id);
            $accessoryHead->delete();

            return $this->apiResponse(true, 'Accessory head deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Accessory head not found.', null, 404, [
                'error' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to delete accessory head.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}
