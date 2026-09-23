<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_sales_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('loan_type_id')->constrained('loan_types')->onDelete('restrict');
            $table->decimal('requested_amount', 12, 2);
            $table->string('applicant_name');
            $table->string('applicant_phone', 20);
            $table->string('city');
            $table->string('pincode', 10)->nullable();
            $table->string('referral_code', 50)->nullable();
            $table->string('campaign_source', 100)->nullable();
            $table->enum('status', [
                'NEW',
                'IN_PROGRESS',
                'SUBMITTED_FOR_REVIEW',
                'READY_FOR_BANK',
                'PENDENCY_RAISED',
                'PENDENCY_RESOLVED',
                'REJECTED',
                'COMPLETED'
            ])->default('NEW')->index();
            $table->text('rejection_reason')->nullable();
            $table->json('detailed_payload')->nullable();
            $table->timestamp('submitted_to_bank_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};
