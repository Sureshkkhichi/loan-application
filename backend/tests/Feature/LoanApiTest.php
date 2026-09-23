<?php

namespace Tests\Feature;

use App\Models\LoanType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_and_verify_otp_flow(): void
    {
        $this->seed(DatabaseSeeder::class);

        // 1. Send OTP
        $response = $this->postJson('/api/v1/auth/send-otp', [
            'phone' => '9876543210',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 2. Verify OTP
        $verifyRes = $this->postJson('/api/v1/auth/verify-otp', [
            'phone' => '9876543210',
            'otp' => '123456',
        ]);
        $verifyRes->assertStatus(200);
        $verifyRes->assertJsonStructure([
            'success',
            'data' => ['token', 'user'],
        ]);

        $token = $verifyRes->json('data.token');

        // 3. Fetch Loan Types
        $loanType = LoanType::first();

        // 4. Submit Application
        $applyRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/applications', [
                'applicant_name' => 'Karan Sharma',
                'loan_type_id' => $loanType->id,
                'requested_amount' => 500000,
                'city' => 'Jaipur',
                'pincode' => '302001',
                'referral_code' => 'PROMO50',
            ]);

        $applyRes->assertStatus(201);
        $applyRes->assertJson(['success' => true]);

        // 5. Get Active Application
        $activeRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/applications/active');

        $activeRes->assertStatus(200);
        $this->assertEquals('Karan Sharma', $activeRes->json('data.applicant_name'));
        $this->assertEquals('NEW', $activeRes->json('data.status'));
    }
}
