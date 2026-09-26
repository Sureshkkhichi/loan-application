<?php

namespace App\Livewire\Applications;

use App\Models\ApplicationActivityLog;
use App\Models\ApplicationDocument;
use App\Models\LoanApplication;
use App\Models\Pendency;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ApplicationDetail extends Component
{
    use WithFileUploads;

    public int $applicationId;
    public ?int $assignedSalesId = null;

    // Detailed Sales Form Fields
    public string $employmentType = 'Salaried';
    public string $companyName = '';
    public string $monthlyIncome = '';
    public string $existingEmis = '';
    public string $panNumber = '';
    public string $aadhaarLast4 = '';
    public string $primaryBank = '';
    public string $cibilScore = '';
    public string $salesNotes = '';

    // Document Upload
    public string $documentName = '';
    public $documentFile = null;

    // Modals
    public bool $showPendencyModal = false;
    public string $pendencyTitle = '';
    public string $pendencyDescription = '';

    public bool $showRejectModal = false;
    public string $rejectionReason = '';

    // Status Message / Flash inside Livewire
    public ?string $statusMessage = null;

    public function mount(int $id): void
    {
        $this->applicationId = $id;
        $application = LoanApplication::findOrFail($id);

        $this->assignedSalesId = $application->assigned_sales_id;

        $payload = $application->detailed_payload ?? [];
        $this->employmentType = $payload['employment_type'] ?? 'Salaried';
        $this->companyName = $payload['company_name'] ?? '';
        $this->monthlyIncome = isset($payload['monthly_income']) ? (string)$payload['monthly_income'] : '';
        $this->existingEmis = isset($payload['existing_emis']) ? (string)$payload['existing_emis'] : '';
        $this->panNumber = $payload['pan_number'] ?? '';
        $this->aadhaarLast4 = $payload['aadhaar_last4'] ?? '';
        $this->primaryBank = $payload['primary_bank'] ?? '';
        $this->cibilScore = isset($payload['cibil_score']) ? (string)$payload['cibil_score'] : '';
        $this->salesNotes = $payload['sales_notes'] ?? '';
    }

    public function assignSales(?int $salesId = null): void
    {
        $targetId = $salesId ?? $this->assignedSalesId;
        if (!$targetId) {
            $this->addError('assignedSalesId', 'Please select a sales executive.');
            return;
        }

        $application = LoanApplication::findOrFail($this->applicationId);
        $user = Auth::user();
        $salesUser = User::findOrFail($targetId);

        $application->update([
            'assigned_sales_id' => $salesUser->id,
            'status' => $application->status === 'NEW' ? 'IN_PROGRESS' : $application->status,
        ]);

        $this->assignedSalesId = $salesUser->id;

        ApplicationActivityLog::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'action' => 'LEAD_ASSIGNED',
            'remarks' => "Application assigned to {$salesUser->name}.",
            'created_at' => Carbon::now(),
        ]);

        $this->statusMessage = "Application successfully assigned to {$salesUser->name}.";
    }

    public function saveDetails(): void
    {
        $this->validate([
            'employmentType' => ['required', 'string'],
            'companyName' => ['nullable', 'string', 'max:150'],
            'monthlyIncome' => ['nullable', 'numeric', 'min:0'],
            'existingEmis' => ['nullable', 'numeric', 'min:0'],
            'panNumber' => ['nullable', 'string', 'max:10'],
            'aadhaarLast4' => ['nullable', 'string', 'max:4'],
            'primaryBank' => ['nullable', 'string', 'max:100'],
            'cibilScore' => ['nullable', 'numeric', 'min:300', 'max:900'],
            'salesNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $application = LoanApplication::findOrFail($this->applicationId);
        $user = Auth::user();

        $currentPayload = $application->detailed_payload ?? [];
        $newPayload = array_merge($currentPayload, [
            'employment_type' => $this->employmentType,
            'company_name' => $this->companyName,
            'monthly_income' => $this->monthlyIncome,
            'existing_emis' => $this->existingEmis,
            'pan_number' => strtoupper($this->panNumber),
            'aadhaar_last4' => $this->aadhaarLast4,
            'primary_bank' => $this->primaryBank,
            'cibil_score' => $this->cibilScore,
            'sales_notes' => $this->salesNotes,
        ]);

        $application->update([
            'detailed_payload' => $newPayload,
            'status' => in_array($application->status, ['NEW']) ? 'IN_PROGRESS' : $application->status,
        ]);

        ApplicationActivityLog::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'action' => 'DETAILS_UPDATED',
            'remarks' => 'Sales details updated by ' . $user->name,
            'created_at' => Carbon::now(),
        ]);

        $this->statusMessage = 'Application details saved successfully.';
    }

    public function uploadDocument(): void
    {
        $this->validate([
            'documentName' => ['required', 'string', 'max:100'],
            'documentFile' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $application = LoanApplication::findOrFail($this->applicationId);
        $user = Auth::user();

        $path = $this->documentFile->store('application_docs/' . $application->id, 'public');

        ApplicationDocument::create([
            'application_id' => $application->id,
            'document_name' => $this->documentName,
            'file_path' => $path,
            'file_type' => $this->documentFile->getClientOriginalExtension(),
            'file_size' => $this->documentFile->getSize(),
            'uploaded_by_role' => $user->role,
            'uploaded_by_user_id' => $user->id,
        ]);

        ApplicationActivityLog::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'action' => 'DOCUMENT_UPLOADED',
            'remarks' => "Document '{$this->documentName}' uploaded by {$user->name}.",
            'created_at' => Carbon::now(),
        ]);

        $this->reset(['documentName', 'documentFile']);
        $this->statusMessage = 'Document uploaded successfully.';
    }

    public function submitReview(): void
    {
        $application = LoanApplication::findOrFail($this->applicationId);
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

        $this->statusMessage = 'Application submitted to Manager for review.';
    }

    public function readyForBank(): void
    {
        $user = Auth::user();
        if (!$user->isManagerOrAdmin()) {
            abort(403, 'Only Managers or Admins can perform this action.');
        }

        $application = LoanApplication::findOrFail($this->applicationId);
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

        $this->statusMessage = 'Application marked as Ready for Bank. You can now hand over details to the banker.';
    }

    public function openPendencyModal(): void
    {
        $this->resetValidation();
        $this->pendencyTitle = '';
        $this->pendencyDescription = '';
        $this->showPendencyModal = true;
    }

    public function closePendencyModal(): void
    {
        $this->showPendencyModal = false;
    }

    public function savePendency(): void
    {
        $user = Auth::user();
        if (!$user->isManagerOrAdmin()) {
            abort(403, 'Only Managers or Admins can raise pendencies.');
        }

        $this->validate([
            'pendencyTitle' => ['required', 'string', 'max:150'],
            'pendencyDescription' => ['required', 'string', 'max:1000'],
        ]);

        $application = LoanApplication::findOrFail($this->applicationId);

        Pendency::create([
            'application_id' => $application->id,
            'title' => $this->pendencyTitle,
            'description' => $this->pendencyDescription,
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
            'remarks' => "Banker requested pendency: '{$this->pendencyTitle}'. Customer notification triggered.",
            'created_at' => Carbon::now(),
        ]);

        $this->showPendencyModal = false;
        $this->statusMessage = 'Banker pendency recorded. Customer has been alerted in their app.';
    }

    public function openRejectModal(): void
    {
        $this->resetValidation();
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
    }

    public function confirmReject(): void
    {
        $user = Auth::user();
        if (!$user->isManagerOrAdmin()) {
            abort(403, 'Only Managers or Admins can reject applications.');
        }

        $this->validate([
            'rejectionReason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $application = LoanApplication::findOrFail($this->applicationId);

        $application->update([
            'status' => 'REJECTED',
            'rejection_reason' => $this->rejectionReason,
        ]);

        ApplicationActivityLog::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'action' => 'APPLICATION_REJECTED',
            'remarks' => "Application rejected with reason: '{$this->rejectionReason}'.",
            'created_at' => Carbon::now(),
        ]);

        $this->showRejectModal = false;
        $this->statusMessage = 'Application rejected. Customer will see the rejection reason in their mobile app.';
    }

    public function markCompleted(): void
    {
        $user = Auth::user();
        if (!$user->isManagerOrAdmin()) {
            abort(403, 'Only Managers or Admins can complete applications.');
        }

        $application = LoanApplication::findOrFail($this->applicationId);

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

        $this->statusMessage = 'Application marked as Completed / Disbursed.';
    }

    public function render()
    {
        $application = LoanApplication::with([
            'customer',
            'loanType',
            'assignedSales',
            'documents.uploadedBy',
            'pendencies.createdBy',
            'activities.user',
        ])->findOrFail($this->applicationId);

        $salesExecutives = User::where('role', 'sales_executive')->where('is_active', true)->get();
        $currentUser = Auth::user();

        return view('livewire.applications.application-detail', [
            'application' => $application,
            'salesExecutives' => $salesExecutives,
            'currentUser' => $currentUser,
        ])->layout('layouts.app', ['title' => 'Application ' . $application->application_number . ' - LoanDesk']);
    }
}
