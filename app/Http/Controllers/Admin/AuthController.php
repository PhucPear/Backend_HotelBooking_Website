<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AuthResource;
use App\Models\Auth;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $auth = Auth::orderBy('ID', 'desc')->get();
        if ($auth->isEmpty()) {
            $arr = [
                'status' => false,
                'message' => "No auth found",
                'data' => []
            ];
            return response()->json($arr, 404);
        }

        $arr = [
            'status' => true,
            'message' => "List auth",
            'data' => AuthResource::collection($auth)
        ];
        return response()->json($arr, 200);
    }

    public function searchAuth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $email = $request->email;

        $auth = Auth::where('Email', 'like', '%' . $email . '%')->orderBy('ID', 'desc')->get();

        if (!$auth) {
            return response()->json(['message' => 'Auth not found'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Auth found',
            'data' => $auth
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
    public function show($id)
    {
        $auth = Auth::find($id);
         if (!$auth) {
             return response()->json(['message' => 'Auth not found'], 404);
         }
         return response()->json(['status' => true, 'data' => $auth], 200);
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
        $auth = Auth::find($id);
    
        if (!$auth) {
            return response()->json(['message' => 'Cannot find the auth to update'], 404);
        }

        try {
            $validatedData = $request->validate([                        
                'Email' => 'required|string|max:50',    
                'OTP' => 'required|integer',
                'Status' => 'required|integer',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }

        $auth->update($validatedData);
    
        return response()->json(['status' => true, 'message' => 'Auth updated successfully', 'data' => $auth], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $auth = Auth::find($id);

        if (!$auth) {
             return response()->json(['message' => 'Auth not found'], 404);
        }    
 
        $auth->delete();
 
         return response()->json(['status' => true, 'message' => 'Auth deleted successfully'], 200);
    }


    //Login
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = Employee::where('Email', $request->email)->value('Email');
        if (!$email) {
            return response()->json(['message' => 'Employee email not found'], 404);
        }
        $verifyOtp = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(10);
        $email_verification = Auth::where('email', $email)->first();

        if (!$email_verification) {
            Auth::create([
                'Email' => $email,
                'OTP' => $verifyOtp,
                'Expired' => $expiresAt,
            ]);
        }
        
        $email_verification->OTP = $verifyOtp;
        $email_verification->Expired = $expiresAt;
        $email_verification->save();
        
        // if ($email) {
        //     Mail::raw("Your login code is: $verifyOtp", function ($message) use ($email) {
        //         $message->to($email)
        //             ->subject('Email Verification Code');
        //     });
        // } else {
        //     return response()->json(['message' => 'Invalid email address'], 400);
        // }

        return response()->json([
            'message' => 'Verification code sent successfully',
            'code' => $verifyOtp
        ]);
    }

    /**
     * login function
     *
     * @param Request $request
     */
    public function login(Request $request)
    {
        $email_verification = Auth::where('email', $request->Email)->value('email');

        if (!$email_verification) {
            return response()->json(['message' => 'The authentication email is incorrect'], 404);
        }

        $storedOtp = Auth::where('email', $request->Email)
            ->where('OTP', $request->OTP)
            ->value('OTP');
        $expires = Auth::where('email', $request->Email)
            ->where('OTP', $request->OTP)
            ->value('Expired');

        if (!$storedOtp || Carbon::now()->greaterThan($expires)) {
            return response()->json(['message' => 'Verification code is invalid or has expired'], 400);
        }

        if ($request->OTP != $storedOtp) {
            return response()->json(['message' => 'Verification code is incorrect'], 400);
        }

        return response()->json(['message' => 'Login successfully']);
    }

    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Full_Name' => 'required|string|max:100',
                'Email' => 'required|email|unique:employees,email|max:50',
                'Position' => 'required|string|max:10'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The entered data is invalid',
                'errors' => $e->errors(),
            ], 422);
        }

        $customer = Employee::create([
            'Full_Name' => $validatedData['Full_Name'],
            'Email' => $validatedData['Email'],
            'Position' => $validatedData['Position'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Employee registered successfully',
            'data' => $customer
        ], 201);
    }
}
