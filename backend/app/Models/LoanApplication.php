<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_number',
        'customer_id',
        'assigned_sales_id',
        'loan_type_id',
        'requested_amount',
        'applicant_name',
        'applicant_phone',
        'city',
        'pincode',
        'referral_code',
        'campaign_source',
        'status',
        'rejection_reason',
        'detailed_payload',
        'submitted_to_bank_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_amount' => 'decimal:2',
            'detailed_payload' => 'array',
            'submitted_to_bank_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function assignedSales()
    {
        return $this->belongsTo(User::class, 'assigned_sales_id');
    }

    public function loanType()
    {
        return $this->belongsTo(LoanType::class, 'loan_type_id');
    }

    public function documents()
    {
        return $this->hasMany(ApplicationDocument::class, 'application_id');
    }

    public function pendencies()
    {
        return $this->hasMany(Pendency::class, 'application_id');
    }

    public function activePendency()
    {
        return $this->hasOne(Pendency::class, 'application_id')->where('status', 'PENDING')->latestOfMany();
    }

    public function activities()
    {
        return $this->hasMany(ApplicationActivityLog::class, 'application_id')->latest();
    }
}
