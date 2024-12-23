<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //Login
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $email = Customer::where('Email', $request->email)->value('Email');
        if (!$email) {
            return response()->json(['status' => false, 'message' => 'Customer email not found'], 200);
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
            'status' => true,
            'message' => 'Verification code sent successfully',
            'code' => $verifyOtp
        ], 200);
    }

    public function login(Request $request)
    {
        $email_verification = Auth::where('email', $request->Email)->value('email');

        if (!$email_verification) {
            return response()->json(['message' => 'The authentication email is incorrect'], 422);
        }

        $storedOtp = Auth::where('email', $request->Email)
            ->where('OTP', $request->OTP)
            ->value('OTP');
        $expires = Auth::where('email', $request->Email)
            ->where('OTP', $request->OTP)
            ->value('Expired');

        if (!$storedOtp || Carbon::now()->greaterThan($expires)) {
            return response()->json(['message' => 'Verification code is invalid or has expired'], 422);
        }

        if ($request->OTP != $storedOtp) {
            return response()->json(['message' => 'Verification code is incorrect'], 422);
        }

        $auth = Auth::where('email', $request->Email)->where('OTP', $request->OTP)->first();
        $auth->status = 1;
        $auth->save();
        return response()->json(['status' => true, 'message' => 'Login successfully'], 200);
    }

    public function logout(Request $request)
    {
        $email_login = $request->Email;
        $auth = Auth::where('email', $email_login)->first();
        $auth->status = 0;
        $auth->save();
        return response()->json(['status' => true, 'message' => 'Logout successfully'], 200);
    }

    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Full_Name' => 'required|string|max:100',
                'Date_of_Birth' => 'required|date',
                'Email' => 'required|email|unique:customers,email|max:50',
                'Phone' => 'required|string|unique:customers,phone|max:11',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors(),
                'errors' => $e->errors(),
            ], 422);
        }

        $customer = Customer::create([
            'Full_Name' => $validatedData['Full_Name'],
            'Date_of_Birth' => $validatedData['Date_of_Birth'],
            'Email' => $validatedData['Email'],
            'Phone' => $validatedData['Phone'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Customer registered successfully',
            'data' => $customer
        ], 201);
    }
}
