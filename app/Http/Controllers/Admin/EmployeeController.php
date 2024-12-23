<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\EmployeeResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {       
        $employees = Employee::orderBy('Employee_ID', 'desc')->get();
        if ($employees->isEmpty()) {
            $arr = [
                'status' => false,
                'message' => "No employee found",
                'data' => []
            ];
            return response()->json($arr, 404);
        }

        $arr = [
            'status' => true,
            'message' => "List employees",
            'data' => EmployeeResource::collection($employees)
        ];
        return response()->json($arr, 200);
    }

    public function searchEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $name = $request->Name;

        $employee = Employee::where('Full_Name', 'like', '%' . $name . '%')->orderBy('Employee_ID', 'desc')->get();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Employee found',
            'data' => $employee
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
                'Email' => 'required|email|unique:employees,email|max:50',    
                'Position' => 'required|string|max:10'           
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }

        $emp = Employee::create([
            'Full_Name' => $validatedData['Full_Name'],
            'Email' => $validatedData['Email'],   
            'Position' => $validatedData['Position'],       
        ]);

        return response()->json(['status' => true, 'message' => 'Employee created successfully', 'data' => $emp], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($employeeID)
    {
        $employee = Employee::find($employeeID);
         if (!$employee) {
             return response()->json(['message' => 'Employee not found'], 404);
         }
         return response()->json(['status' => true, 'data' => $employee], 200);
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
    public function update(Request $request, $employeeID)
    {
        $employee = Employee::find($employeeID);
    
        if (!$employee) {
            return response()->json(['message' => 'Cannot find the employee to update'], 404);
        }

        try {
            $validatedData = $request->validate([              
                'Full_Name' => 'required|string|max:100',
                'Email' => 'required|string|max:50',    
                'Position' => 'required|string|max:10'           
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }

        $employee->update($validatedData);
    
        return response()->json(['status' => true, 'message' => 'Employee updated successfully', 'data' => $employee], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($employeeID)
    {
        $employee = Employee::find($employeeID);

         if (!$employee) {
             return response()->json(['message' => 'Employee not found'], 404);
         }    
 
         $employee->delete();
 
         return response()->json(['status' => true, 'message' => 'Employee deleted successfully'], 200);
    }

}
