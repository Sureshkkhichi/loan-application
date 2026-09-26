<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApplicationActivityLog;
use App\Models\ApplicationDocument;
use App\Models\LoanApplication;
use App\Models\Pendency;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    public function show($id)
    {
        return redirect()->route('applications.show', $id);
    }

    /**
     * Assign application to sales executive or self-claim.
     */
    public function assign(Request $request, $id)
    {
        $application = LoanApplication::findOrFail($id);
        $user = Auth::user();

        $request->validate([
            'sales_id' => ['required', 'exists:users,id'],
        ]);

        $salesUser = User::findOrFail($request->sales_id);

        $application->update([
            'assigned_sales_id' => $salesUser->id,
            'status' => $application->status === 'NEW' ? 'IN_PROGRESS' : $application->status,
        ]);

        ApplicationActivityLog::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'action' => 'LEAD_ASSIGNED',
            'remarks' => "Application assigned to {$salesUser->name}.",
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', "Application assigned to {$salesUser->name} successfully.");
    }

    /**
     * Sales Team updates extended customer details collected over phone.
     */
    public function updateDetails(Request $request, $id)
    {
        $application = LoanApplication::findOrFail($id);
        $user = Auth::user();

        $data = $request->except(['_token', 'files']);
        
        $currentPayload = $application->detailed_payload ?? [];
        $mergedPayload = array_merge($currentPayload, $data);

        $application->update([
            'detailed_payload' => $mergedPayload,
            'status' => in_array($application->status, ['NEW']) ? 'IN_PROGRESS' : $application->status,
        ]);

        ApplicationActivityLog::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'action' => 'DETAILS_UPDATED',
            'remarks' => 'Sales details updated by ' . $user->name,
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Application details updated successfully.');
    }

    /**
     * Sales uploads customer documents.
     */
    public function uploadDocument(Request $request, $id)
    {
        $application = LoanApplication::findOrFail($id);
        $user = Auth::user();

        $request->validate([
            'document_name' => ['required', 'string', 'max:100'],
            'document_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10MB
        ]);

        $file = $request->file('document_file');
        $path = $file->store('application_docs/' . $application->id, 'public');

        ApplicationDocument::create([
            'application_id' => $application->id,
            'document_name' => $request->document_name,
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'uploaded_by_role' => $user->role,
            'uploaded_by_user_id' => $user->id,
        ]);

        ApplicationActivityLog::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'action' => 'DOCUMENT_UPLOADED',
            'remarks' => "Document '{$request->document_name}' uploaded by {$user->name}.",
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    /**
     * Sales Team marks application "Submit for Review".
     */
    public function submitReview(Request $request, $id)
    {
        $application = LoanApplication::findOrFail($id);
        $user = Auth::user();

        $application->update([
            'status' => 'SUBMITTED_FOR_REVIEW',
        ]);

        ApplicationActivityLog::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'action' => 'SUBMITTED_FOR_REVIEW',
            'remarks' => 'Sales executive completed form and submitted for Manager review.',
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Application submitted to Manager for review.');
    }

    /**
     * Manager marks application "Ready for Bank" (external handoff).
     */
    public function readyForBank(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isManagerOrAdmin()) {
            abort(403, 'Only Managers or Admins can perform this action.');
        }

        $application = LoanApplication::findOrFail($id);
        $application->update([
            'status' => 'READY_FOR_BANK',
            'submitted_to_bank_at' => Carbon::now(),
        ]);

        ApplicationActivityLog::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'action' => 'READY_FOR_BANK',
            'remarks' => 'Application verified and marked Ready for Bank. Details handed over externally to banker.',
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Application marked as Ready for Bank. You can now hand over details to the banker.');
    }

    /**
     * Manager rejects application with mandatory reason.
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isManagerOrAdmin()) {
            abort(403, 'Only Managers or Admins can reject applications.');
        }

        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $application = LoanApplication::findOrFail($id);
        $application->update([
            'status' => 'REJECTED',
            'rejection_reason' => $request->rejection_reason,
        ]);

        ApplicationActivityLog::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'action' => 'APPLICATION_REJECTED',
            'remarks' => "Application rejected with reason: '{$request->rejection_reason}'.",
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Application rejected. Customer will see the rejection reason in their app.');
    }

    /**
     * Manager injects Banker Pendency into system (triggers Customer alert).
     */
    public function addPendency(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isManagerOrAdmin()) {
            abort(403, 'Only Managers or Admins can raise pendencies.');
        }

        $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        $application = LoanApplication::findOrFail($id);

        $pendency = Pendency::create([
            'application_id' => $application->id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'PENDING',
            'created_by_user_id' => $user->id,
        ]);

        $application->update([
            'status' => 'PENDENCY_RAISED',
        ]);

        ApplicationActivityLog::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'action' => 'PENDENCY_RAISED',
            'remarks' => "Banker requested pendency: '{$request->title}'. Customer notification triggered.",
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Pendency created successfully. Customer notified in app to upload/provide details.');
    }

    /**
     * Manager marks loan as Completed / Disbursed.
     */
    public function complete(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isManagerOrAdmin()) {
            abort(403, 'Only Managers or Admins can complete applications.');
        }

        $application = LoanApplication::findOrFail($id);
        $application->update([
            'status' => 'COMPLETED',
            'completed_at' => Carbon::now(),
        ]);

        ApplicationActivityLog::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'action' => 'APPLICATION_COMPLETED',
            'remarks' => 'Loan successfully sanctioned and disbursed by external bank. Application completed.',
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Application marked as Completed / Disbursed.');
    }
}
