<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('loan_applications')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['PENDING', 'RESOLVED'])->default('PENDING')->index();
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
            $table->text('customer_response_text')->nullable();
            $table->string('customer_response_file')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendencies');
    }
};
