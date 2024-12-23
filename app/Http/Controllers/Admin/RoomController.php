<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\RoomResource;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $room = Room::orderBy('Room_ID', 'desc')->get();
        if ($room->isEmpty()) {
            $arr = [
                'status' => false,
                'message' => "No employee found",
                'data' => []
            ];
            return response()->json($arr, 404);
        }

        $arr = [
            'status' => true,
            'message' => "List room",
            'data' => RoomResource::collection($room)
        ];
        return response()->json($arr, 200);
    }

    public function searchRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }
        $id = $request->id;
        $room = Room::where('Room_ID', $id)->orderBy('Room_ID', 'desc')->get();
        
        if (!$room) {
            return response()->json(['status' => false, 'message' => 'Room not found'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Room found',
            'data' => RoomResource::collection($room)
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([              
                'Type' => 'required|integer|min:1',
                'Status' => 'required|string|max:50',   
                'Image' => 'required|string|max:50'            
            ]);
        } catch (ValidationException $e) {       
            return response()->json([
                'message' => $e->errors(),
            ], 422);
        }

        $roomTypeExists = RoomType::find($validatedData['Type']);

        if (!$roomTypeExists) {
            return response()->json(['message' => 'Room type does not exist'], 400);
        }

        $room = Room::create([
            'Type_ID' => $validatedData['Type'],
            'Status' => $validatedData['Status'], 
            'Image' => $validatedData['Image']      
        ]);

        return response()->json(['status' => true, 'message' => 'Room created successfully', 'data' => $room], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $room = Room::find($id);
         if (!$room) {
             return response()->json(['message' => 'Room not found'], 404);
         }
         return response()->json(['status' => true, 'data' => $room], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $room = Room::find($id);
    
        if (!$room) {
            return response()->json(['message' => 'Cannot find the room to update'], 404);
        }

        try {
            $validatedData = $request->validate([
                'Type' => 'required|integer|min:1',
                'Status' => 'required|string|max:50',
                'Image' => 'required|string|max:50'
            ]);
        } catch (ValidationException $e) {       
            return response()->json([
                'message' => $e->errors(),
            ], 422);
        }

        $roomTypeExists = RoomType::find($validatedData['Type']);

        if (!$roomTypeExists) {
            return response()->json(['message' => 'Room type does not exist'], 404);
        }

        $room->update($validatedData);
    
        return response()->json(['status' => true, 'message' => 'Room updated successfully', 'data' => $room], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $room = Room::find($id);

         if (!$room) {
             return response()->json(['message' => 'Room not found'], 404);
         }    
 
         $room->delete();
 
         return response()->json(['status' => true, 'message' => 'Room deleted successfully'], 200);
    }
}
