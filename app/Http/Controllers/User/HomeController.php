<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Http\Resources\User\RoomResource;
use App\Models\Auth;
use App\Models\Customer;
use Illuminate\Validation\ValidationException;

class HomeController extends Controller
{
    public function index()
    {
        $rooms = Room::where('status', 'empty')->get();

        if ($rooms->isEmpty()) {
            $arr = [
                'status' => false,
                'message' => "No rooms found",
                'data' => []
            ];
            return response()->json($arr, 404);
        }

        $arr = [
            'status' => true,
            'message' => "List of rooms",
            'data' => RoomResource::collection($rooms)
        ];
        return response()->json($arr, 200);
    }

    public function searchRoom(Request $request)
    {
        try {
            $request->validate([
                'capacity' => 'required|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }

        $capacity = $request->capacity;

        $rooms = Room::join('room_types', 'rooms.Type_ID', '=', 'room_types.Type_ID')
            ->where('room_types.capacity', '=', $capacity)
            ->where('rooms.status', 'Empty')
            ->orderBy('room_types.price', 'asc')
            ->select(
                'room_types.name',
                'room_types.price',
                'room_types.capacity',
                'image'
            )
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Rooms found',
            'data' => $rooms
        ], 200);
    }

    public function getCustomer()
    {
        $email_verification = Auth::where('Status', 1)->orderBy('Expired', 'desc')->value('Email');
        $customer = Customer::where('Email', $email_verification)->first();
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }
         return response()->json(['status' => true, 'Customer' => $customer], 200);
    }

    public function findRoom(Request $request)
    {
        $roomID = $request->roomID;

        $room = Room::join('room_types', 'rooms.Type_ID', '=', 'room_types.Type_ID')
            ->where('rooms.Room_ID', '=', $roomID)
            ->where('rooms.status', 'Empty')
            ->select(
                'room_types.name',
                'room_types.price',
                'room_types.capacity',
                'image'
            )
            ->first();

        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $room], 200);
    }
}
