<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use App\Models\Pendency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Metrics queries
        $counts = [
            'total' => LoanApplication::count(),
            'new' => LoanApplication::where('status', 'NEW')->count(),
            'in_progress' => LoanApplication::where('status', 'IN_PROGRESS')->count(),
            'submitted_for_review' => LoanApplication::where('status', 'SUBMITTED_FOR_REVIEW')->count(),
            'ready_for_bank' => LoanApplication::where('status', 'READY_FOR_BANK')->count(),
            'pendency_raised' => LoanApplication::where('status', 'PENDENCY_RAISED')->count(),
            'pendency_resolved' => LoanApplication::where('status', 'PENDENCY_RESOLVED')->count(),
            'completed' => LoanApplication::where('status', 'COMPLETED')->count(),
            'rejected' => LoanApplication::where('status', 'REJECTED')->count(),
        ];

        // Query applications with filters
        $query = LoanApplication::with(['customer', 'loanType', 'assignedSales', 'activePendency'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                  ->orWhere('applicant_name', 'like', "%{$search}%")
                  ->orWhere('applicant_phone', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // If Sales Executive, show assigned or unassigned new leads
        if ($user->role === 'sales_executive' && $request->get('scope') === 'my') {
            $query->where('assigned_sales_id', $user->id);
        }

        $applications = $query->paginate(15)->withQueryString();

        return view('dashboard.index', compact('counts', 'applications', 'user'));
    }
}
