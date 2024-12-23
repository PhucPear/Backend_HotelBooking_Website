<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CustomerResource;
use App\Models\Bill;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customer = Customer::orderBy('Customer_ID', 'desc')->get();
        if ($customer->isEmpty()) {
            $arr = [
                'status' => false,
                'message' => "No customer found",
                'data' => []
            ];
            return response()->json($arr, 404);
        }

        $arr = [
            'status' => true,
            'message' => "List customer",
            'data' => CustomerResource::collection($customer)
        ];
        return response()->json($arr, 200);
    }

    public function searchCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $name = $request->Name;

        $customer = Customer::where('Full_Name', 'like', '%' . $name . '%')->orderBy('Customer_ID', 'desc')->get();

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Customer found',
            'data' => $customer
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
        try {
            $validatedData = $request->validate([              
                'Full_Name' => 'required|string|max:100',
                'Date' => 'required|date',
                'Email' => 'required|email|unique:customers,email|max:50',    
                'Phone' => 'required|string|max:11',
                'ID' => 'required|string|max:20',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors(),
            ], 422);
        }

        $customer = Customer::create([
            'Full_Name' => $validatedData['Full_Name'],
            'Date_of_Birth' => $validatedData['Date'],   
            'Email' => $validatedData['Email'],   
            'Phone' => $validatedData['Phone'],   
            'ID' => $validatedData['ID']    
        ]);

        return response()->json(['status' => true, 'message' => 'Customer created successfully', 'data' => $customer], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($customerID)
    {
        $customer = Customer::find($customerID);
         if (!$customer) {
             return response()->json(['message' => 'Customer not found'], 404);
         }
         return response()->json(['status' => true, 'data' => $customer], 200);
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
    public function update(Request $request, $customerID)
    {
        $customer = Customer::find($customerID);
    
        if (!$customer) {
            return response()->json(['message' => 'Cannot find the customer to update'], 404);
        }

        try {
            $validatedData = $request->validate([              
                'Full_Name' => 'required|string|max:100',
                'Date_of_Birth' => 'required|date',
                'Email' => 'required|email|unique:customers,email|max:50',    
                'Phone' => 'required|string|max:11',
                'ID' => 'required|string|max:20',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors(),
            ], 422);
        }

        $customer->update($validatedData);
    
        return response()->json(['status' => true, 'message' => 'Customer updated successfully', 'data' => $customer], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($customerID)
    {
        $customer = Customer::find($customerID);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $bill = Bill::where('Customer_ID', $customer->Customer_ID)->first();
        if ($bill) {
            return response()->json(['message' => 'Cannot delete customer, there are bill associated with this customer !'], 400);
        }

        $customer->delete();

        return response()->json(['status' => true, 'message' => 'Customer deleted successfully'], 200);
    }
}
