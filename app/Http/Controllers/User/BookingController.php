<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\DetailsBill;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\User\PaymentsController;

class BookingController extends Controller
{
    // Booking room
    public function bookingRoom(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Full_Name' => 'required|string|max:50',
                'Date_of_Birth' => 'required|date',
                'Phone' => 'required|string|max:11',
                'Email' => 'required|string|max:50',
                'Check_In' => 'required',
                'Check_Out' => 'required',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors(),
                'errors' => $e->errors(),
            ], 422);
        }

        $room = Room::find($request->Room_ID);
        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        $amount = $room->roomType->Price;
        $customer = Customer::where('Email', $request->Email)->first();

        $pay = new PaymentsController();
        $returnUrl = $request->Return_Url;

        if (!$customer) {
            $customer_new = Customer::create([
                'Full_Name' => $validatedData['Full_Name'],
                'Date_of_Birth' => $validatedData['Date_of_Birth'],
                'Email' => $validatedData['Email'],
                'Phone' => $validatedData['Phone'],
            ]);
            $bill = Bill::create([
                'Amount' => $amount,
                'Status' => 'Unpaid',
                'Customer_ID' => $customer_new->Customer_ID,
            ]);
            DetailsBill::create([
                'Bill_ID' => $bill->Bill_ID,
                'Room_ID' => $request->Room_ID,
                'Check_in_Date' => $request->Check_in_Date,
                'Check_out_Date' => $request->Check_out_Date,
            ]);

            // Chuyển hướng đến VNPAY để thanh toán 
            $vnpUrl = $pay->createPayment($bill, $returnUrl);

            return response()->json(['status' => true, 'message' => 'Redirecting to VNPAY', 'vnpUrl' => $vnpUrl], 200);
        } else {
            $bill = Bill::create([
                'Amount' => $amount,
                'Status' => 'Unpaid',
                'Customer_ID' => $customer->Customer_ID,
            ]);
            DetailsBill::create([
                'Bill_ID' => $bill->Bill_ID,
                'Room_ID' => $request->Room_ID,
                'Check_in_Date' => $request->Check_in_Date,
                'Check_out_Date' => $request->Check_out_Date,
            ]);
            // Chuyển hướng đến VNPAY để thanh toán
            $vnpUrl = $pay->createPayment($bill, $returnUrl);
            return response()->json(['status' => true, 'message' => 'Redirecting to VNPAY', 'vnpUrl' => $vnpUrl], 200);
        }
    }
    // View booking history
    public function bookingHistory(Request $request)
    {
        $customerID = $request->customerID;
        $bookingHistory = Bill::join('details_bills', 'bills.Bill_ID', '=', 'details_bills.Bill_ID')
            ->join('rooms', 'details_bills.Room_ID', '=', 'rooms.Room_ID')
            ->join('room_types', 'rooms.Type_ID', '=', 'room_types.Type_ID')
            ->where('bills.Customer_ID', $customerID)
            ->select(
                'date',
                'amount',
                'details_bills.check_in_date',
                'details_bills.check_out_date',
                'room_types.name',
                'room_types.price',
                'room_types.capacity',
                'rooms.image'
            )
            ->orderBy('bills.Date', 'desc')
            ->get();

        if ($bookingHistory->isEmpty()) {
            return response()->json(['message' => 'No booking history found for this customer'], 404);
        }

        return response()->json(['status' => true, 'message' => 'Booking history of customer', 'bills' => $bookingHistory], 200);
    }
}
