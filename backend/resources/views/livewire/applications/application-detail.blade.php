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

    <!-- Live Status Banner / Toast Alert -->
    @if($statusMessage)
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm animate-fade-in">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span class="text-sm font-medium">{{ $statusMessage }}</span>
            </div>
            <button type="button" wire:click="$set('statusMessage', null)" class="text-emerald-600 hover:text-emerald-900 text-xs font-bold">✕</button>
        </div>
    @endif

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
                    <p class="text-xs text-rose-700 mt-1">This reason is displayed live to the customer on their mobile app.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Operations Action Bar (Livewire Actions) -->
    <div class="bg-white rounded-2xl border border-borderline shadow-sm p-5">
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Workflow Actions & Transitions</h3>
        <div class="flex flex-wrap items-center gap-3">

            <!-- Lead Assignment Form -->
            <div class="inline-flex items-center gap-2">
                <select wire:model="assignedSalesId" class="px-3 py-2 rounded-xl border border-borderline text-xs bg-bglight focus:outline-none focus:ring-1 focus:ring-brand font-medium">
                    <option value="">-- Assign Sales Executive --</option>
                    @foreach($salesExecutives as $exec)
                        <option value="{{ $exec->id }}">
                            {{ $exec->name }}
                        </option>
                    @endforeach
                </select>
                <button type="button" wire:click="assignSales" wire:loading.attr="disabled"
                    class="px-3.5 py-2 bg-gray-800 hover:bg-black text-white text-xs font-semibold rounded-xl transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="assignSales">Assign</span>
                    <span wire:loading wire:target="assignSales">Saving...</span>
                </button>
            </div>

            <!-- Sales Action: Submit for Review -->
            @if(in_array($application->status, ['NEW', 'IN_PROGRESS', 'PENDENCY_RESOLVED']))
                <button type="button" wire:click="submitReview"
                    wire:confirm="Submit this application to Manager for review?"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="submitReview">✓ Submit for Manager Review</span>
                    <span wire:loading wire:target="submitReview">Submitting...</span>
                </button>
            @endif

            <!-- Manager Actions -->
            @if($currentUser->isManagerOrAdmin())

                <!-- Ready for Bank (External Handoff) -->
                @if(in_array($application->status, ['SUBMITTED_FOR_REVIEW', 'PENDENCY_RESOLVED']))
                    <button type="button" wire:click="readyForBank"
                        wire:confirm="Confirm details are verified and hand over externally to banker?"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all disabled:opacity-50">
                        <span wire:loading.remove wire:target="readyForBank">🏦 Mark Ready for Bank</span>
                        <span wire:loading wire:target="readyForBank">Processing...</span>
                    </button>
                @endif

                <!-- Add Banker Pendency -->
                @if(in_array($application->status, ['READY_FOR_BANK', 'SUBMITTED_FOR_REVIEW', 'IN_PROGRESS']))
                    <button type="button" wire:click="openPendencyModal"
                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all">
                        ⚠️ Add Banker Pendency
                    </button>
                @endif

                <!-- Complete / Disbursed -->
                @if(in_array($application->status, ['READY_FOR_BANK', 'PENDENCY_RESOLVED']))
                    <button type="button" wire:click="markCompleted"
                        wire:confirm="Banker has sanctioned & disbursed this loan?"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all disabled:opacity-50">
                        <span wire:loading.remove wire:target="markCompleted">🎉 Mark Completed / Disbursed</span>
                        <span wire:loading wire:target="markCompleted">Processing...</span>
                    </button>
                @endif

                <!-- Reject -->
                @if(!in_array($application->status, ['COMPLETED', 'REJECTED']))
                    <button type="button" wire:click="openRejectModal"
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

            <!-- Detailed Sales Application Form (Reactive livewire form) -->
            <div class="bg-white rounded-2xl border border-borderline shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-charcoal">Sales Team Full Application Data</h2>
                        <p class="text-xs text-gray-500">Collected over call with customer.</p>
                    </div>
                    <span class="text-xs bg-brand/10 text-brand px-2.5 py-1 rounded-lg font-medium">Reactive Auto-Save</span>
                </div>

                <form wire:submit.prevent="saveDetails" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Employment Type</label>
                            <select wire:model="employmentType" class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                                <option value="Salaried">Salaried</option>
                                <option value="Self Employed Business">Self Employed Business</option>
                                <option value="Self Employed Professional">Self Employed Professional</option>
                                <option value="Other">Other</option>
                            </select>
                            @error('employmentType') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Company / Business Name</label>
                            <input type="text" wire:model="companyName" placeholder="e.g. TCS / Self Owned"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                            @error('companyName') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Monthly In-hand Income (₹)</label>
                            <input type="number" wire:model="monthlyIncome" placeholder="e.g. 75000"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                            @error('monthlyIncome') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Existing Monthly EMIs (₹)</label>
                            <input type="number" wire:model="existingEmis" placeholder="e.g. 15000"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                            @error('existingEmis') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">PAN Card Number</label>
                            <input type="text" wire:model="panNumber" placeholder="ABCDE1234F" style="text-transform:uppercase"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                            @error('panNumber') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Aadhaar Last 4 Digits</label>
                            <input type="text" wire:model="aadhaarLast4" placeholder="1234" maxlength="4"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                            @error('aadhaarLast4') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Primary Salary/Business Bank</label>
                            <input type="text" wire:model="primaryBank" placeholder="e.g. HDFC Bank, ICICI Bank"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                            @error('primaryBank') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Tentative CIBIL Score</label>
                            <input type="number" wire:model="cibilScore" placeholder="e.g. 750"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                            @error('cibilScore') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Sales Discussion Notes & Banker Handoff Remarks</label>
                        <textarea wire:model="salesNotes" rows="3"
                            placeholder="Enter any discussion notes, customer urgency, preferred banker, etc..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand"></textarea>
                        @error('salesNotes') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-5 py-2.5 bg-brand hover:bg-brand-dark text-white text-xs font-semibold rounded-xl shadow-sm transition-all disabled:opacity-50">
                            <span wire:loading.remove wire:target="saveDetails">Save Detailed Form</span>
                            <span wire:loading wire:target="saveDetails">Saving Changes...</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Documents Uploaded by Sales Team (Livewire WithFileUploads) -->
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

                <!-- Livewire File Upload Form -->
                <form wire:submit.prevent="uploadDocument" class="pt-4 border-t border-borderline flex flex-col sm:flex-row gap-3 items-end">
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Document Title</label>
                        <input type="text" wire:model="documentName" placeholder="e.g. 6 Months Bank Statement / PAN Card"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight">
                        @error('documentName') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">File (PDF/Image max 10MB)</label>
                        <input type="file" wire:model="documentFile" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-borderline bg-bglight">
                        @error('documentFile') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        
                        <div wire:loading wire:target="documentFile" class="text-xs text-brand font-medium mt-1">
                            Uploading file...
                        </div>
                    </div>
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-gray-800 hover:bg-black text-white text-xs font-semibold rounded-xl disabled:opacity-50">
                        <span wire:loading.remove wire:target="uploadDocument">Upload</span>
                        <span wire:loading wire:target="uploadDocument">Processing...</span>
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

    <!-- Livewire Modal: Add Banker Pendency -->
    @if($showPendencyModal)
        <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-borderline animate-scale-up">
                <h3 class="text-lg font-bold text-charcoal mb-1">Add Banker Pendency</h3>
                <p class="text-xs text-gray-500 mb-4">Enter what document or clarification the external banker has requested. This will trigger a notification in the customer's mobile app.</p>

                <form wire:submit.prevent="savePendency" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Pendency Requirement Title</label>
                        <input type="text" wire:model="pendencyTitle" placeholder="e.g. Latest 6 Months Bank Statement Required"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand">
                        @error('pendencyTitle') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Instructions for Customer</label>
                        <textarea wire:model="pendencyDescription" rows="3"
                            placeholder="e.g. Please upload latest 6 months salary account bank statement showing salary credits from ABC Ltd."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand"></textarea>
                        @error('pendencyDescription') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-2 pt-2">
                        <button type="button" wire:click="closePendencyModal"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl disabled:opacity-50">
                            <span wire:loading.remove wire:target="savePendency">Send Pendency to Customer</span>
                            <span wire:loading wire:target="savePendency">Sending...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Livewire Modal: Reject Application -->
    @if($showRejectModal)
        <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-borderline animate-scale-up">
                <h3 class="text-lg font-bold text-charcoal mb-1">Reject Loan Application</h3>
                <p class="text-xs text-gray-500 mb-4">Enter the exact rejection reason. This reason will be displayed directly to the customer on their mobile app.</p>

                <form wire:submit.prevent="confirmReject" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Mandatory Rejection Reason</label>
                        <textarea wire:model="rejectionReason" rows="3"
                            placeholder="e.g. Current CIBIL score is below the minimum lending threshold of 700."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-borderline bg-bglight focus:ring-1 focus:ring-brand"></textarea>
                        @error('rejectionReason') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-2 pt-2">
                        <button type="button" wire:click="closeRejectModal"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-5 py-2 bg-gray-900 hover:bg-black text-white text-xs font-semibold rounded-xl disabled:opacity-50">
                            <span wire:loading.remove wire:target="confirmReject">Confirm & Reject Application</span>
                            <span wire:loading wire:target="confirmReject">Rejecting...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
