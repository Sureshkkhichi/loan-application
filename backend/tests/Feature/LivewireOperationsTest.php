<?php

namespace Tests\Feature;

use App\Livewire\Applications\ApplicationDetail;
use App\Livewire\Dashboard;
use App\Models\LoanApplication;
use App\Models\LoanType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_livewire_dashboard_renders_and_filters_correctly(): void
    {
        $this->seed(DatabaseSeeder::class);

        $sales = User::where('role', 'sales_executive')->first();
        $loanType = LoanType::first();

        $customer = User::create([
            'name' => 'Amit Sharma',
            'phone' => '9876543299',
            'role' => 'customer',
        ]);

        $app = LoanApplication::create([
            'application_number' => 'LD-TEST-9999',
            'customer_id' => $customer->id,
            'loan_type_id' => $loanType->id,
            'requested_amount' => 500000,
            'applicant_name' => 'Amit Sharma',
            'applicant_phone' => '9876543299',
            'city' => 'Jaipur',
            'status' => 'NEW',
        ]);

        $this->actingAs($sales);

        Livewire::test(Dashboard::class)
            ->assertSee('LD-TEST-9999')
            ->assertSee('Amit Sharma')
            ->set('search', 'Jaipur')
            ->assertSee('LD-TEST-9999')
            ->set('search', 'NonExistentCity')
            ->assertDontSee('LD-TEST-9999')
            ->call('setStatusFilter', 'NEW')
            ->assertSet('status', 'NEW');
    }

    public function test_livewire_application_detail_workflow_actions(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('role', 'admin')->first();
        $sales = User::where('role', 'sales_executive')->first();
        $loanType = LoanType::first();

        $customer = User::create([
            'name' => 'Pooja Verma',
            'phone' => '9811223344',
            'role' => 'customer',
        ]);

        $app = LoanApplication::create([
            'application_number' => 'LD-TEST-8888',
            'customer_id' => $customer->id,
            'loan_type_id' => $loanType->id,
            'requested_amount' => 300000,
            'applicant_name' => 'Pooja Verma',
            'applicant_phone' => '9811223344',
            'city' => 'Delhi',
            'status' => 'NEW',
        ]);

        // 1. Sales assigns and updates details
        $this->actingAs($sales);

        Livewire::test(ApplicationDetail::class, ['id' => $app->id])
            ->set('assignedSalesId', $sales->id)
            ->call('assignSales')
            ->set('companyName', 'Tech Mahindra')
            ->set('monthlyIncome', '65000')
            ->call('saveDetails')
            ->call('submitReview');

        $app->refresh();
        $this->assertEquals($sales->id, $app->assigned_sales_id);
        $this->assertEquals('SUBMITTED_FOR_REVIEW', $app->status);
        $this->assertEquals('Tech Mahindra', $app->detailed_payload['company_name']);

        // 2. Manager adds pendency via Livewire modal
        $this->actingAs($admin);

        Livewire::test(ApplicationDetail::class, ['id' => $app->id])
            ->call('openPendencyModal')
            ->assertSet('showPendencyModal', true)
            ->set('pendencyTitle', 'Salary Slip Required')
            ->set('pendencyDescription', 'Please provide latest 3 months salary slips.')
            ->call('savePendency')
            ->assertSet('showPendencyModal', false);

        $app->refresh();
        $this->assertEquals('PENDENCY_RAISED', $app->status);
        $this->assertCount(1, $app->pendencies);
    }

    public function test_livewire_home_emi_calculator(): void
    {
        $this->seed(DatabaseSeeder::class);

        Livewire::test(\App\Livewire\Home::class)
            ->assertSee('Interactive EMI Calculator')
            ->set('loanAmount', 1000000)
            ->set('interestRate', 12)
            ->set('tenureYears', 5)
            ->assertSee('Personal Loan');
    }

    public function test_livewire_navbar_logout(): void
    {
        $this->seed(DatabaseSeeder::class);
        $sales = User::where('role', 'sales_executive')->first();

        $this->actingAs($sales);

        Livewire::test(\App\Livewire\Components\Navbar::class)
            ->assertSee($sales->name)
            ->assertSee('sales_executive')
            ->call('logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
