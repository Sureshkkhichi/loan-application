<?php

namespace App\Livewire;

use App\Models\LoanApplication;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $scope = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setStatusFilter(string $status = ''): void
    {
        $this->status = $status;
        $this->resetPage();
    }

    public function toggleMyLeads(): void
    {
        $this->scope = $this->scope === 'my' ? '' : 'my';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->scope = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        // Real-time metric pipeline counts
        $counts = [
            'total' => LoanApplication::count(),
            'new' => LoanApplication::where('status', 'NEW')->count(),
            'in_progress' => LoanApplication::where('status', 'IN_PROGRESS')->count(),
            'submitted_for_review' => LoanApplication::where('status', 'SUBMITTED_FOR_REVIEW')->count(),
            'ready_for_bank' => LoanApplication::where('status', 'READY_FOR_BANK')->count(),
            'pendency_raised' => LoanApplication::where('status', 'PENDENCY_RAISED')->count(),
            'completed' => LoanApplication::where('status', 'COMPLETED')->count(),
            'rejected' => LoanApplication::where('status', 'REJECTED')->count(),
        ];

        // Query applications with filters
        $query = LoanApplication::with(['customer', 'loanType', 'assignedSales', 'activePendency'])
            ->latest();

        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        if (!empty(trim($this->search))) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                  ->orWhere('applicant_name', 'like', "%{$search}%")
                  ->orWhere('applicant_phone', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($user && $user->role === 'sales_executive' && $this->scope === 'my') {
            $query->where('assigned_sales_id', $user->id);
        }

        $applications = $query->paginate(15);

        return view('livewire.dashboard', [
            'counts' => $counts,
            'applications' => $applications,
            'user' => $user,
        ])->layout('layouts.app', ['title' => 'Live Operations Dashboard - LoanDesk']);
    }
}
