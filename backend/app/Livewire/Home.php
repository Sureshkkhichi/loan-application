<?php

namespace App\Livewire;

use App\Models\LoanType;
use Livewire\Component;

class Home extends Component
{
    public float $loanAmount = 500000;
    public float $interestRate = 10.5;
    public int $tenureYears = 5;

    public function getMonthlyEmiProperty(): float
    {
        $p = $this->loanAmount;
        $r = ($this->interestRate / 12) / 100;
        $n = $this->tenureYears * 12;

        if ($r <= 0 || $n <= 0 || $p <= 0) {
            return 0;
        }

        $emi = ($p * $r * pow(1 + $r, $n)) / (pow(1 + $r, $n) - 1);
        return round($emi, 2);
    }

    public function getTotalPayableProperty(): float
    {
        return round($this->monthlyEmi * ($this->tenureYears * 12), 2);
    }

    public function getTotalInterestProperty(): float
    {
        return max(0, round($this->totalPayable - $this->loanAmount, 2));
    }

    public function render()
    {
        $loanTypes = LoanType::where('is_active', true)->get();

        return view('livewire.home', [
            'loanTypes' => $loanTypes,
        ])->layout('layouts.app', ['title' => 'LoanDesk • Trusted Financial Lending Platform']);
    }
}
