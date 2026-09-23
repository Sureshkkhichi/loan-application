<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplicationActivityLog;
use App\Models\Pendency;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CustomerPendencyController extends Controller
{
    /**
     * Submit response / upload document to resolve banker pendency.
     */
    public function resolve(Request $request, $id)
    {
        $user = $request->user();

        $pendency = Pendency::with('application')->findOrFail($id);

        // Security check: customer must own the application
        if ((int) $pendency->application->customer_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this pendency.',
            ], 403);
        }

        if ($pendency->status === 'RESOLVED') {
            return response()->json([
                'success' => false,
                'message' => 'This pendency has already been resolved.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'response_text' => ['nullable', 'string', 'max:1000'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // Max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        // At least text or file must be provided
        if (!$request->filled('response_text') && !$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide either a message note or upload the requested file.',
            ], 422);
        }

        return DB::transaction(function () use ($request, $pendency, $user) {
            $filePath = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filePath = $file->store('pendency_docs/' . $pendency->application_id, 'public');
            }

            $pendency->update([
                'status' => 'RESOLVED',
                'customer_response_text' => $request->response_text,
                'customer_response_file' => $filePath,
                'resolved_at' => Carbon::now(),
            ]);

            // Update application status to PENDENCY_RESOLVED
            $application = $pendency->application;
            $application->update([
                'status' => 'PENDENCY_RESOLVED',
            ]);

            // Add activity log
            ApplicationActivityLog::create([
                'application_id' => $application->id,
                'user_id' => $user->id,
                'action' => 'PENDENCY_RESOLVED',
                'remarks' => 'Customer submitted resolution for pendency: "' . $pendency->title . '".',
                'created_at' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pendency details submitted successfully! Manager will review shortly.',
                'data' => [
                    'pendency' => $pendency,
                    'application_status' => $application->status,
                ],
            ]);
        });
    }
}
