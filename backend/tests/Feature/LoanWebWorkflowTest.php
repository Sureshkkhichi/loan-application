<?php

namespace Tests\Feature;

use App\Models\LoanApplication;
use App\Models\LoanType;
use App\Models\Pendency;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanWebWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_lead_to_bank_and_pendency_workflow(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('role', 'admin')->first();
        $sales = User::where('role', 'sales_executive')->first();
        $loanType = LoanType::first();

        // 1. Customer creates application
        $customer = User::create([
            'name' => 'Vikram Patel',
            'phone' => '9988776655',
            'role' => 'customer',
        ]);

        $application = LoanApplication::create([
            'application_number' => 'LD-2026-00001',
            'customer_id' => $customer->id,
            'loan_type_id' => $loanType->id,
            'requested_amount' => 750000,
            'applicant_name' => 'Vikram Patel',
            'applicant_phone' => '9988776655',
            'city' => 'Mumbai',
            'status' => 'NEW',
        ]);

        // 2. Sales login & view dashboard
        $this->actingAs($sales)
            ->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('LD-2026-00001');

        // 3. Sales assigns lead to self
        $this->actingAs($sales)
            ->post("/applications/{$application->id}/assign", [
                'sales_id' => $sales->id,
            ])
            ->assertRedirect();

        $application->refresh();
        $this->assertEquals($sales->id, $application->assigned_sales_id);
        $this->assertEquals('IN_PROGRESS', $application->status);

        // 4. Sales updates caller form data
        $this->actingAs($sales)
            ->post("/applications/{$application->id}/update-details", [
                'employment_type' => 'Salaried',
                'company_name' => 'Infosys',
                'monthly_income' => 90000,
                'existing_emis' => 12000,
                'pan_number' => 'ABCDE1234F',
            ])
            ->assertRedirect();

        $application->refresh();
        $this->assertEquals('Infosys', $application->detailed_payload['company_name']);

        // 5. Sales submits for manager review
        $this->actingAs($sales)
            ->post("/applications/{$application->id}/submit-review")
            ->assertRedirect();

        $application->refresh();
        $this->assertEquals('SUBMITTED_FOR_REVIEW', $application->status);

        // 6. Manager marks ready for bank
        $this->actingAs($admin)
            ->post("/applications/{$application->id}/ready-for-bank")
            ->assertRedirect();

        $application->refresh();
        $this->assertEquals('READY_FOR_BANK', $application->status);

        // 7. Banker asks for pendency (Manager injects it)
        $this->actingAs($admin)
            ->post("/applications/{$application->id}/add-pendency", [
                'title' => 'Form 16 Required',
                'description' => 'Please provide latest 2 years Form 16.',
            ])
            ->assertRedirect();

        $application->refresh();
        $this->assertEquals('PENDENCY_RAISED', $application->status);
        $this->assertCount(1, $application->pendencies);

        $pendency = $application->pendencies->first();
        $this->assertEquals('PENDING', $pendency->status);

        // 8. Customer resolves pendency via mobile API
        \Laravel\Sanctum\Sanctum::actingAs($customer);
        $resolveRes = $this->postJson("/api/v1/pendencies/{$pendency->id}/resolve", [
            'response_text' => 'Uploaded Form 16 for AY 2025-26.',
        ]);

        $resolveRes->assertStatus(200);

        $application->refresh();
        $pendency->refresh();
        $this->assertEquals('RESOLVED', $pendency->status);
        $this->assertEquals('PENDENCY_RESOLVED', $application->status);

        // 9. Manager completes / disburses loan
        $this->actingAs($admin)
            ->post("/applications/{$application->id}/complete")
            ->assertRedirect();

        $application->refresh();
        $this->assertEquals('COMPLETED', $application->status);
    }
}
