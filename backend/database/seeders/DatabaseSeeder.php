<?php

namespace Database\Seeders;

use App\Models\LoanType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Default Staff Accounts
        $admin = User::updateOrCreate(
            ['email' => 'admin@loandesk.com'],
            [
                'name' => 'LoanDesk Admin',
                'phone' => '9876543210',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $sales = User::updateOrCreate(
            ['email' => 'sales@loandesk.com'],
            [
                'name' => 'Rahul Sharma (Sales)',
                'phone' => '9876543211',
                'password' => Hash::make('password123'),
                'role' => 'sales_executive',
                'is_active' => true,
            ]
        );

        // 2. Seed Standard Loan Types
        $loanTypes = [
            [
                'name' => 'Personal Loan',
                'code' => 'personal',
                'description' => 'Quick unsecured funds for personal and emergency needs.',
                'min_amount' => 25000,
                'max_amount' => 1500000,
            ],
            [
                'name' => 'Business Loan',
                'code' => 'business',
                'description' => 'Collateral-free working capital and growth capital for enterprises.',
                'min_amount' => 100000,
                'max_amount' => 5000000,
            ],
            [
                'name' => 'Home Loan',
                'code' => 'home',
                'description' => 'Lowest interest home purchase and construction financing.',
                'min_amount' => 500000,
                'max_amount' => 20000000,
            ],
            [
                'name' => 'Loan Against Property (LAP)',
                'code' => 'lap',
                'description' => 'Unlock property value with low rates and long tenure.',
                'min_amount' => 500000,
                'max_amount' => 15000000,
            ],
            [
                'name' => 'Education Loan',
                'code' => 'education',
                'description' => 'Higher education funding in India and overseas universities.',
                'min_amount' => 100000,
                'max_amount' => 5000000,
            ],
        ];

        foreach ($loanTypes as $lt) {
            LoanType::updateOrCreate(['code' => $lt['code']], $lt);
        }
    }
}
