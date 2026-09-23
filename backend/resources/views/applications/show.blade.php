@extends('layouts.app', ['title' => 'Application ' . $application->application_number . ' - LoanDesk'])

@section('content')
<div class="space-y-6">

    <!-- Top Navigation Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-semibold text-gray-500 hover:text-brand transition-colors">
            ← Back to Dashboard
        </a>
        <div class="flex items-center space-x-2">
            <span class="text-xs text-gray-400 font-mono">App ID:</span>
            <span class="text-sm font-bold font-mono text-brand">{{ $application->application_number }}</span>
        </div>
    </div>

    <!-- Application Status Hero Banner -->
    <div class="bg-white rounded-2xl border border-borderline shadow-sm p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div>
            <div class="flex items-center space-x-3 mb-2">
                @php
                    $badgeClasses = [
                        'NEW' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'IN_PROGRESS' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'SUBMITTED_FOR_REVIEW' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'READY_FOR_BANK' => 'bg-teal-50 text-teal-700 border-teal-200',
                        'PENDENCY_RAISED' => 'bg-rose-50 text-rose-700 border-rose-200',
                        'PENDENCY_RESOLVED' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                        'COMPLETED' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'REJECTED' => 'bg-gray-100 text-gray-700 border-gray-300',
                    ];
                @endphp
                <span class="inline-block px-3 py-1 text-xs font-bold rounded-full border {{ $badgeClasses[$application->status] ?? 'bg-gray-100 text-gray-700' }}">
                    STATUS: {{ str_replace('_', ' ', $application->status) }}
                </span>
                <span class="text-xs text-gray-400">Created: {{ $application->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <h1 class="text-2xl font-bold text-charcoal">{{ $application->applicant_name }}</h1>
            <p class="text-sm text-gray-500 mt-1 flex items-center gap-3">
                <span>📞 +91 {{ $application->applicant_phone }}</span>
                <span>•</span>
                <span>📍 {{ $application->city }} {{ $application->pincode ? '(' . $application->pincode . ')' : '' }}</span>
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
            <div class="bg-bglight p-4 rounded-xl border border-borderline text-right">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 block">Requested Amount</span>
                <span class="text-2xl font-bold text-brand">₹{{ number_format($application->requested_amount, 2) }}</span>
                <span class="text-xs text-gray-500 block mt-0.5">{{ $application->loanType->name ?? 'Loan Product' }}</span>
            </div>
        </div>
    </div>

    @if($application->status === 'REJECTED')
        <div class="p-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900">
            <div class="flex items-start space-x-3">
                <svg class="w-6 h-6 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <h3 class="text-sm font-bold">Application Was Rejected</h3>
                    <p class="text-sm mt-1"><strong>Reason recorded:</strong> {{ $application->rejection_reason }}</p>
                    <p class="text-xs text-rose-700 mt-1">This reason is visible to the customer on their mobile app.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Operations Action Bar -->
    <div class="bg-white rounded-2xl border border-borderline shadow-sm p-5">
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Workflow Actions & Transitions</h3>
        <div class="flex flex-wrap items-center gap-3">

            <!-- Lead Assignment Form -->
            <form action="{{ route('applications.assign', $application->id) }}" method="POST" class="inline-flex items-center gap-2">
                @csrf
                <select name="sales_id" class="px-3 py-2 rounded-xl border border-borderline text-xs bg-bglight focus:outline-none focus:ring-1 focus:ring-brand font-medium">
                    <option value="">-- Assign Sales Executive --</option>
                    @foreach($salesExecutives as $exec)
                        <option value="{{ $exec->id }}" {{ $application->assigned_sales_id == $exec->id ? 'selected' : '' }}>
                            {{ $exec->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="px-3.5 py-2 bg-gray-800 hover:bg-black text-white text-xs font-semibold rounded-xl transition-all">
                    Assign
                </button>
            </form>

            <!-- Sales Action: Submit for Review -->
            @if(in_array($application->status, ['NEW', 'IN_PROGRESS', 'PENDENCY_RESOLVED']))
                <form action="{{ route('applications.submit-review', $application->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Submit this application to Manager for review?')"
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all">
                        ✓ Submit for Manager Review
                    </button>
                </form>
            @endif

            <!-- Manager Actions -->
            @if($currentUser->isManagerOrAdmin())

                <!-- Ready for Bank (External Handoff) -->
                @if(in_array($application->status, ['SUBMITTED_FOR_REVIEW', 'PENDENCY_RESOLVED']))
                    <form action="{{ route('applications.ready-for-bank', $application->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Confirm details are verified and hand over externally to banker?')"
                            class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all">
                            🏦 Mark Ready for Bank
                        </button>
                    </form>
                @endif

                <!-- Add Banker Pendency -->
                @if(in_array($application->status, ['READY_FOR_BANK', 'SUBMITTED_FOR_REVIEW', 'IN_PROGRESS']))
                    <button type="button" onclick="document.getElementById('pendencyModal').classList.remove('hidden')"
                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all">
                        ⚠️ Add Banker Pendency
                    </button>
                @endif

                <!-- Complete / Disbursed -->
                @if(in_array($application->status, ['READY_FOR_BANK', 'PENDENCY_RESOLVED']))
                    <form action="{{ route('applications.complete', $application->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Banker has sanctioned & disbursed this loan?')"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all">
                            🎉 Mark Completed / Disbursed
                        </button>
                    </form>
                @endif

                <!-- Reject -->
                @if(!in_array($application->status, ['COMPLETED', 'REJECTED']))
                    <button type="button" onclick="document.getElementById('rejectModal').classList.remove('hidden')"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-semibold rounded-xl transition-all">
                        ✕ Reject Application
                    </button>
                @endif

            @endif
        </div>
    </div>

    <!-- 2 Column Workspace Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Sales Form & Pendencies -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Active / Past Pendencies Card -->
            <div class="bg-white rounded-2xl border border-borderline shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-charcoal flex items-center gap-2">
                        <span>⚠️ Banker Pendencies & Customer Responses</span>
                        <span class="text-xs bg-rose-100 text-rose-800 font-semibold px-2 py-0.5 rounded-full">{{ $application->pendencies->count() }}</span>
                    </h2>
                </div>

                @forelse($application->pendencies as $p)
                    <div class="p-4 rounded-xl border {{ $p->status === 'PENDING' ? 'border-rose-300 bg-rose-50/50' : 'border-emerald-200 bg-emerald-50/30' }} mb-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-bold text-charcoal">{{ $p->title }}</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $p->status === 'PENDING' ? 'bg-rose-200 text-rose-800' : 'bg-emerald-200 text-emerald-800' }}">
                                {{ $p->status }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-700 mb-2"><strong>Banker Requirement:</strong> {{ $p->description }}</p>
                        <div class="text-[11px] text-gray-400 mb-3">Raised by {{ $p->createdBy->name ?? 'Manager' }} on {{ $p->created_at->format('d M Y, h:i A') }}</div>

                        @if($p->status === 'RESOLVED')
                            <div class="pt-3 border-t border-emerald-200 bg-white p-3 rounded-lg text-xs space-y-1">
                                <span class="font-bold text-emerald-800 block">✓ Customer Response ({{ $p->resolved_at ? $p->resolved_at->format('d M Y, h:i A') : '' }}):</span>
                                @if($p->customer_response_text)
                                    <p class="text-gray-700 italic">"{{ $p->customer_response_text }}"</p>
                                @endif
                                @if($p->customer_response_file)
                                    <div class="mt-1">
                                        <a href="{{ asset('storage/' . $p->customer_response_file) }}" target="_blank"
                                            class="inline-flex items-center text-xs font-semibold text-brand hover:underline">
                                            📄 View / Download Customer Uploaded Document →
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-xs text-rose-700 font-medium">
                                ⏳ Waiting for customer response on mobile app...
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-gray-400 py-3 text-center">No pendencies raised for this application.</p>
                @endforelse
            </div>

            <!-- Detailed Sales Application Form (Caller form) -->
            <div class="bg-white rounded-2xl border border-borderline shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-charcoal">Sales Team Full Application Data</h2>
                        <p class="text-xs text-gray-500">Collected over call with customer.</p>
                    </div>
                    <span class="text-xs bg-brand/10 text-brand px-2.5 py-1 rounded-lg font-medium">Editable</span>
                </div>

                @php
                    $payload = $application->detailed_payload ?? [];
                @endphp

                <form action="{{ route('applications.update-details', $application->id) }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Employment Type</label>
                            <select name="employment_type" class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                                <option value="Salaried" {{ ($payload['employment_type'] ?? '') == 'Salaried' ? 'selected' : '' }}>Salaried</option>
                                <option value="Self Employed Business" {{ ($payload['employment_type'] ?? '') == 'Self Employed Business' ? 'selected' : '' }}>Self Employed Business</option>
                                <option value="Self Employed Professional" {{ ($payload['employment_type'] ?? '') == 'Self Employed Professional' ? 'selected' : '' }}>Self Employed Professional</option>
                                <option value="Other" {{ ($payload['employment_type'] ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Company / Business Name</label>
                            <input type="text" name="company_name" value="{{ $payload['company_name'] ?? '' }}"
                                placeholder="e.g. TCS / Self Owned"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Monthly In-hand Income (₹)</label>
                            <input type="number" name="monthly_income" value="{{ $payload['monthly_income'] ?? '' }}"
                                placeholder="e.g. 75000"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Existing Monthly EMIs (₹)</label>
                            <input type="number" name="existing_emis" value="{{ $payload['existing_emis'] ?? '0' }}"
                                placeholder="e.g. 15000"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">PAN Card Number</label>
                            <input type="text" name="pan_number" value="{{ $payload['pan_number'] ?? '' }}"
                                placeholder="ABCDE1234F" style="text-transform:uppercase"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Aadhaar Last 4 Digits</label>
                            <input type="text" name="aadhaar_last4" value="{{ $payload['aadhaar_last4'] ?? '' }}"
                                placeholder="1234" maxlength="4"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Primary Salary/Business Bank</label>
                            <input type="text" name="primary_bank" value="{{ $payload['primary_bank'] ?? '' }}"
                                placeholder="e.g. HDFC Bank, ICICI Bank"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Tentative CIBIL Score</label>
                            <input type="number" name="cibil_score" value="{{ $payload['cibil_score'] ?? '' }}"
                                placeholder="e.g. 750"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Sales Discussion Notes & Banker Handoff Remarks</label>
                        <textarea name="sales_notes" rows="3"
                            placeholder="Enter any discussion notes, customer urgency, preferred banker, etc..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">{{ $payload['sales_notes'] ?? '' }}</textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-brand hover:bg-brand-dark text-white text-xs font-semibold rounded-xl shadow-sm transition-all">
                            Save Detailed Form
                        </button>
                    </div>
                </form>
            </div>

            <!-- Documents Uploaded by Sales Team -->
            <div class="bg-white rounded-2xl border border-borderline shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-charcoal">Application Documents</h2>
                </div>

                <!-- Existing Documents List -->
                <div class="space-y-2 mb-4">
                    @forelse($application->documents as $doc)
                        <div class="flex items-center justify-between p-3 rounded-xl border border-borderline bg-bglight text-xs">
                            <div class="flex items-center space-x-2">
                                <span class="font-bold text-brand">📄 {{ $doc->document_name }}</span>
                                <span class="text-gray-400">({{ strtoupper($doc->file_type) }} • {{ round($doc->file_size / 1024, 1) }} KB)</span>
                            </div>
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                class="text-xs font-semibold text-brand hover:underline">
                                View File ↗
                            </a>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 text-center py-2">No documents attached yet.</p>
                    @endforelse
                </div>

                <!-- Upload New Document Form -->
                <form action="{{ route('applications.upload-doc', $application->id) }}" method="POST" enctype="multipart/form-data" class="pt-4 border-t border-borderline flex flex-col sm:flex-row gap-3 items-end">
                    @csrf
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Document Title</label>
                        <input type="text" name="document_name" required placeholder="e.g. 6 Months Bank Statement / PAN Card"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight">
                    </div>
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">File (PDF/Image max 10MB)</label>
                        <input type="file" name="document_file" required accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-borderline bg-bglight">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-black text-white text-xs font-semibold rounded-xl">
                        Upload
                    </button>
                </form>
            </div>

        </div>

        <!-- Right 1 Col: Customer Details & Timeline -->
        <div class="space-y-6">

            <!-- Customer & Marketing Attribution Card -->
            <div class="bg-white rounded-2xl border border-borderline shadow-sm p-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Lead & Attribution Info</h3>
                <dl class="space-y-2.5 text-xs">
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <dt class="text-gray-500">Applicant Mobile:</dt>
                        <dd class="font-semibold text-charcoal">+91 {{ $application->applicant_phone }}</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <dt class="text-gray-500">Location:</dt>
                        <dd class="font-semibold text-charcoal">{{ $application->city }} ({{ $application->pincode ?? 'N/A' }})</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <dt class="text-gray-500">Loan Product:</dt>
                        <dd class="font-semibold text-brand">{{ $application->loanType->name ?? 'Standard' }}</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <dt class="text-gray-500">Marketing Source:</dt>
                        <dd class="font-semibold text-charcoal">{{ $application->campaign_source ?? 'Organic App' }}</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <dt class="text-gray-500">Referral Code:</dt>
                        <dd class="font-mono font-semibold text-teal-700">{{ $application->referral_code ?? 'None' }}</dd>
                    </div>
                    <div class="flex justify-between py-1">
                        <dt class="text-gray-500">Assigned To:</dt>
                        <dd class="font-semibold text-charcoal">{{ $application->assignedSales->name ?? 'Unassigned' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Activity Audit Trail -->
            <div class="bg-white rounded-2xl border border-borderline shadow-sm p-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Activity Audit Trail</h3>
                <div class="relative pl-6 space-y-4 border-l-2 border-borderline ml-2">
                    @foreach($application->activities as $act)
                        <div class="relative">
                            <span class="absolute -left-[31px] top-1 w-3 h-3 rounded-full bg-brand ring-4 ring-white"></span>
                            <div class="text-xs font-bold text-charcoal">{{ str_replace('_', ' ', $act->action) }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ $act->remarks }}</div>
                            <div class="text-[10px] text-gray-400 mt-1">
                                {{ $act->created_at->format('d M, h:i A') }} • by {{ $act->user->name ?? 'Customer' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Add Banker Pendency (Manager Only) -->
<div id="pendencyModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-borderline">
        <h3 class="text-lg font-bold text-charcoal mb-1">Add Banker Pendency</h3>
        <p class="text-xs text-gray-500 mb-4">Enter what document or clarification the external banker has requested. This will trigger a notification in the customer's mobile app.</p>

        <form action="{{ route('applications.add-pendency', $application->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Pendency Requirement Title</label>
                <input type="text" name="title" required placeholder="e.g. Latest 6 Months Bank Statement Required"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Instructions for Customer</label>
                <textarea name="description" rows="3" required
                    placeholder="e.g. Please upload latest 6 months salary account bank statement showing salary credits from ABC Ltd."
                    class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand"></textarea>
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="document.getElementById('pendencyModal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl">
                    Send Pendency to Customer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reject Application (Manager Only) -->
<div id="rejectModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-borderline">
        <h3 class="text-lg font-bold text-charcoal mb-1">Reject Loan Application</h3>
        <p class="text-xs text-gray-500 mb-4">Enter the exact rejection reason. This reason will be displayed directly to the customer on their mobile app.</p>

        <form action="{{ route('applications.reject', $application->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Mandatory Rejection Reason</label>
                <textarea name="rejection_reason" rows="3" required
                    placeholder="e.g. Current CIBIL score is below the minimum lending threshold of 700."
                    class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand"></textarea>
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-gray-900 hover:bg-black text-white text-xs font-semibold rounded-xl">
                    Confirm & Reject Application
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
