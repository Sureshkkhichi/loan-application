<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OtpVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerAuthController extends Controller
{
    /**
     * Send OTP to customer phone.
     * In development, '123456' is the default test bypass OTP.
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
        ], [
            'phone.regex' => 'Please enter a valid 10-digit Indian mobile number.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $phone = $request->phone;
        $otp = '123456'; // Default dev OTP bypass for testing

        // Store OTP in database
        OtpVerification::updateOrCreate(
            ['phone' => $phone],
            [
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(10),
                'verified' => false,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully to +91 ' . $phone,
            'data' => [
                'phone' => $phone,
                'dev_otp' => $otp, // Convenient for rapid testing in dev
                'resend_cooldown_seconds' => 30,
            ],
        ]);
    }

    /**
     * Verify OTP and issue Sanctum token.
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'otp' => ['required', 'string', 'size:6'],
            'fcm_token' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $phone = $request->phone;
        $otp = $request->otp;

        $record = OtpVerification::where('phone', $phone)
            ->where('otp', $otp)
            ->where('expires_at', '>=', Carbon::now())
            ->first();

        // Allow dev bypass 123456
        if (!$record && $otp !== '123456') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP. Please try again.',
            ], 401);
        }

        if ($record) {
            $record->update(['verified' => true]);
        }

        // Find or create customer
        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => 'Customer ' . substr($phone, -4),
                'role' => 'customer',
                'is_active' => true,
            ]
        );

        if ($request->filled('fcm_token')) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        // Revoke old tokens & create fresh one
        $user->tokens()->delete();
        $token = $user->createToken('customer-mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'role' => $user->role,
                ],
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'role' => $user->role,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
