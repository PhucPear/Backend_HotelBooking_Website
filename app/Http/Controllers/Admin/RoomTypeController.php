<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\RoomTypeResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class RoomTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roomType = RoomType::orderBy('Type_ID', 'desc')->get();
        if ($roomType->isEmpty()) {
            $arr = [
                'status' => false,
                'message' => "No room type found",
                'data' => []
            ];
            return response()->json($arr, 404);
        }

        $arr = [
            'status' => true,
            'message' => "List room type",
            'data' => RoomTypeResource::collection($roomType)
        ];
        return response()->json($arr, 200);
    }

    public function searchRoomType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }
        $name = $request->Name;
        $roomType = RoomType::where('Name', 'like', '%' . $name . '%')->orderBy('Type_ID', 'desc')->get();
        if (!$roomType) {
            return response()->json(['status' => false, 'message' => 'Room type not found'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Room type found',
            'data' => $roomType
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request) {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Name' => 'required|string|max:100',
                'Price' => 'required|integer|min:1',
                'Capacity' => 'required|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors(),
            ], 422);
        }

        $roomType = RoomType::create([
            'Name' => $validatedData['Name'],
            'Price' => $validatedData['Price'],
            'Capacity' => $validatedData['Capacity'],
        ]);

        return response()->json(['status' => true, 'message' => 'Room type created successfully', 'data' => $roomType], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($typeID)
    {
        $roomType = RoomType::find($typeID);
        if (!$roomType) {
            return response()->json(['message' => 'Room type not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $roomType], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $typeID)
    {
        $roomType = RoomType::find($typeID);

        if (!$roomType) {
            return response()->json(['message' => 'Cannot find the room type to update'], 404);
        }

        try {
            $validatedData = $request->validate([
                'Name' => 'required|string|max:100',
                'Price' => 'required|integer|min:1',
                'Capacity' => 'required|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors(),
            ], 422);
        }

        $roomType->update($validatedData);

        return response()->json(['status' => true, 'message' => 'Room type updated successfully', 'data' => $roomType], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($typeID)
    {
        $roomType = RoomType::find($typeID);

        if (!$roomType) {
            return response()->json(['message' => 'Room type not found'], 404);
        }

        if ($roomType->rooms()->exists()) {
            return response()->json(['message' => 'Cannot delete room type, there are rooms associated with this type !'], 400);
        }

        $roomType->delete();

        return response()->json(['status' => true, 'message' => 'Room type deleted successfully'], 200);
    }
}
