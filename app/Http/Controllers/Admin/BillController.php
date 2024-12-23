<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\BillResource;
use App\Models\Bill;
use App\Models\DetailsBill;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {      
        $bill =  Bill::orderBy('Bill_ID', 'desc')->get();
        if ($bill->isEmpty()) {
            $arr = [
                'status' => false,
                'message' => "No bill found",
                'data' => []
            ];
            return response()->json($arr, 404);
        }

        $arr = [
            'status' => true,
            'message' => "List bill",
            'data' => BillResource::collection($bill)
        ];
        return response()->json($arr, 200);
    }

    public function searchBill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }
        $id = $request->id;
        $bill = Bill::where('Bill_ID', $id)->orderBy('Bill_ID', 'desc')->get();
        
        if (!$bill) {
            return response()->json(['status' => false, 'message' => 'Room not found'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Bill found',
            'data' => BillResource::collection($bill)
        ], 200);
    }

    public function getDetailsBill($billID)
    {
        $detailsBill = DetailsBill::where('Bill_ID', $billID)->orderBy('Bill_ID', 'desc')->get();

        if (!$detailsBill) {
            return response()->json(['message' => 'Details Bill not found'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Details Bill found',
            'data' => $detailsBill
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($bilId)
    {
        $bill = Bill::find($bilId);
         if (!$bill) {
             return response()->json(['message' => 'Bill not found'], 404);
         }
         return response()->json(['status' => true, 'data' => $bill], 200);
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
    public function update(Request $request, $billID)
    {
        $bill = Bill::find($billID);
    
        if (!$bill) {
            return response()->json(['message' => 'Cannot find the bill to update'], 404);
        }

        try {
            $validatedData = $request->validate([              
                'Amount' => 'required|integer',
                'Payments' => 'required|string|max:50',
                'Employee_ID' => 'required|integer',    
                'Status' => 'required|string|max:100',                
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors(),
            ], 422);
        }

        $bill->update($validatedData);
    
        return response()->json(['status' => true, 'message' => 'Bill updated successfully', 'data' => $bill], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($bilId)
    {
        $bill = Bill::find($bilId);

         if (!$bill) {
             return response()->json(['message' => 'Bill not found'], 404);
         }
 
         if ($bill->details()->exists()) {
             return response()->json(['message' => 'Cannot delete bill, there are details bill associated with this bill'], 400);
         }
 
         $bill->delete();
 
         return response()->json(['status' => true, 'message' => 'Bill deleted successfully'], 200);
    }
}
