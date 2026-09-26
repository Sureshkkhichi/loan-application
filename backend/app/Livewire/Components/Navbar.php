<?php

namespace App\Livewire\Components;

use App\Models\LoanApplication;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Navbar extends Component
{
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }

    public function render()
    {
        $user = Auth::user();
        $newLeadsCount = $user ? LoanApplication::where('status', 'NEW')->count() : 0;
        $pendingReviewsCount = $user && $user->isManagerOrAdmin()
            ? LoanApplication::where('status', 'SUBMITTED_FOR_REVIEW')->count()
            : 0;

        return view('livewire.components.navbar', [
            'user' => $user,
            'newLeadsCount' => $newLeadsCount,
            'pendingReviewsCount' => $pendingReviewsCount,
        ]);
    }
}
