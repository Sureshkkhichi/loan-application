<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplicationActivityLog;
use App\Models\LoanApplication;
use App\Models\LoanType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerApplicationController extends Controller
{
    /**
     * Get customer's current active loan application.
     */
    public function getActive(Request $request)
    {
        $user = $request->user();

        // Get latest application
        $application = LoanApplication::with([
            'loanType',
            'activePendency',
            'activities',
            'documents',
        ])
        ->where('customer_id', $user->id)
        ->latest()
        ->first();

        return response()->json([
            'success' => true,
            'data' => $application,
        ]);
    }

    /**
     * Submit new loan application (Basic Lead form).
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Check if customer already has an active application
        $activeApplication = LoanApplication::where('customer_id', $user->id)
            ->whereNotIn('status', ['REJECTED', 'COMPLETED'])
            ->first();

        if ($activeApplication) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active loan application in progress (' . $activeApplication->application_number . ').',
                'data' => $activeApplication,
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'applicant_name' => ['required', 'string', 'max:150'],
            'loan_type_id' => ['required', 'exists:loan_types,id'],
            'requested_amount' => ['required', 'numeric', 'min:10000'],
            'city' => ['required', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'referral_code' => ['nullable', 'string', 'max:50'],
            'campaign_source' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($request, $user) {
            // Generate Application Number: LD-YYYY-XXXX
            $year = date('Y');
            $count = LoanApplication::whereYear('created_at', $year)->count() + 1;
            $appNumber = sprintf('LD-%s-%05d', $year, $count);

            // Update user's name if default
            if ($user->name === 'Customer ' . substr($user->phone, -4) || empty($user->name)) {
                $user->update(['name' => $request->applicant_name]);
            }

            $application = LoanApplication::create([
                'application_number' => $appNumber,
                'customer_id' => $user->id,
                'loan_type_id' => $request->loan_type_id,
                'requested_amount' => $request->requested_amount,
                'applicant_name' => $request->applicant_name,
                'applicant_phone' => $user->phone,
                'city' => $request->city,
                'pincode' => $request->pincode,
                'referral_code' => $request->referral_code,
                'campaign_source' => $request->campaign_source ?? 'Customer App',
                'status' => 'NEW',
            ]);

            // Log activity
            ApplicationActivityLog::create([
                'application_id' => $application->id,
                'user_id' => $user->id,
                'action' => 'APPLICATION_SUBMITTED',
                'remarks' => 'Basic loan application submitted by customer.',
                'created_at' => Carbon::now(),
            ]);

            $application->load(['loanType', 'activePendency', 'activities']);

            return response()->json([
                'success' => true,
                'message' => 'Loan application submitted successfully! Application ID: ' . $appNumber,
                'data' => $application,
            ], 201);
        });
    }
}
