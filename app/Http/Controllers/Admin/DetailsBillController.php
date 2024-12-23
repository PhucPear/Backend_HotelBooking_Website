<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DetailsBillResource;
use App\Models\Bill;
use App\Models\DetailsBill;
use Illuminate\Http\Request;

class DetailsBillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $details_bill = DetailsBill::orderBy('Bill_ID', 'desc')->get();
        $arr = [
            'status' => true,
            'message' => "List details bill",
            'data' => DetailsBillResource::collection($details_bill)
        ];
        return response()->json($arr, 200);
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
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show($bilId)
    {
        $detailsBill = Bill::find($bilId);
        if (!$detailsBill) {
            return response()->json(['message' => 'Details bill not found'], 404);
        }
        return response()->json(['employee' => $detailsBill], 200);
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */

    public function deleteDetailsBill(Request $request)
    { 
        $billID = $request->id;
        $order = $request->order;

        $detailsBill = DetailsBill::where('Bill_ID', $billID)
                              ->where('Order', $order)
                              ->first();
        dd($detailsBill->toArray());
        if (!$detailsBill) {
            return response()->json(['message' => 'Details bill not found'], 404);
        }

        $detailsBill->Remove();

        $bill = Bill::findOrFail($billID);
        $bill->calculateTotalAmount();

        return response()->json(['status' => true, 'message' => 'Details bill deleted successfully'], 200);
    }


    public function destroy($id)
    {

    }
}
