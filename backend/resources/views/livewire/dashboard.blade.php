<div class="space-y-6">

    <!-- Header & Welcome Banner -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-borderline shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-2xl font-bold text-charcoal">Operations Dashboard</h1>
                <span class="text-xs bg-brand/10 text-brand px-2.5 py-0.5 rounded-full font-semibold">Livewire 3 Active</span>
            </div>
            <p class="text-sm text-gray-500 mt-0.5">Manage incoming loan leads, sales reviews, and external banker handoffs in real-time.</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1.5 rounded-lg font-medium flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                System Live • Reactive Pipeline
            </span>
        </div>
    </div>

    <!-- Metric Pipeline Cards (Reactive Filter on Click) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
        <button type="button" wire:click="setStatusFilter('')"
            class="text-left p-4 rounded-xl border {{ empty($status) ? 'border-brand bg-brand/5 ring-1 ring-brand' : 'border-borderline bg-white' }} shadow-sm hover:shadow transition-all">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">All Leads</span>
            <div class="text-2xl font-bold text-charcoal mt-1">{{ $counts['total'] }}</div>
        </button>

        <button type="button" wire:click="setStatusFilter('NEW')"
            class="text-left p-4 rounded-xl border {{ $status === 'NEW' ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-borderline bg-white' }} shadow-sm hover:shadow transition-all">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-blue-600">New</span>
            <div class="text-2xl font-bold text-blue-700 mt-1">{{ $counts['new'] }}</div>
        </button>

        <button type="button" wire:click="setStatusFilter('IN_PROGRESS')"
            class="text-left p-4 rounded-xl border {{ $status === 'IN_PROGRESS' ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-borderline bg-white' }} shadow-sm hover:shadow transition-all">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Calling</span>
            <div class="text-2xl font-bold text-indigo-700 mt-1">{{ $counts['in_progress'] }}</div>
        </button>

        <button type="button" wire:click="setStatusFilter('SUBMITTED_FOR_REVIEW')"
            class="text-left p-4 rounded-xl border {{ $status === 'SUBMITTED_FOR_REVIEW' ? 'border-amber-500 bg-amber-50 ring-1 ring-amber-500' : 'border-borderline bg-white' }} shadow-sm hover:shadow transition-all">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-600">Review</span>
            <div class="text-2xl font-bold text-amber-700 mt-1">{{ $counts['submitted_for_review'] }}</div>
        </button>

        <button type="button" wire:click="setStatusFilter('READY_FOR_BANK')"
            class="text-left p-4 rounded-xl border {{ $status === 'READY_FOR_BANK' ? 'border-teal-500 bg-teal-50 ring-1 ring-teal-500' : 'border-borderline bg-white' }} shadow-sm hover:shadow transition-all">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-teal-600">With Bank</span>
            <div class="text-2xl font-bold text-teal-700 mt-1">{{ $counts['ready_for_bank'] }}</div>
        </button>

        <button type="button" wire:click="setStatusFilter('PENDENCY_RAISED')"
            class="text-left p-4 rounded-xl border {{ $status === 'PENDENCY_RAISED' ? 'border-rose-500 bg-rose-50 ring-1 ring-rose-500' : 'border-borderline bg-white' }} shadow-sm hover:shadow transition-all">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-rose-600">Pendency</span>
            <div class="text-2xl font-bold text-rose-700 mt-1">{{ $counts['pendency_raised'] }}</div>
        </button>

        <button type="button" wire:click="setStatusFilter('COMPLETED')"
            class="text-left p-4 rounded-xl border {{ $status === 'COMPLETED' ? 'border-emerald-500 bg-emerald-50 ring-1 ring-emerald-500' : 'border-borderline bg-white' }} shadow-sm hover:shadow transition-all">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600">Disbursed</span>
            <div class="text-2xl font-bold text-emerald-700 mt-1">{{ $counts['completed'] }}</div>
        </button>

        <button type="button" wire:click="setStatusFilter('REJECTED')"
            class="text-left p-4 rounded-xl border {{ $status === 'REJECTED' ? 'border-gray-500 bg-gray-50 ring-1 ring-gray-500' : 'border-borderline bg-white' }} shadow-sm hover:shadow transition-all">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-600">Rejected</span>
            <div class="text-2xl font-bold text-gray-700 mt-1">{{ $counts['rejected'] }}</div>
        </button>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-borderline shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex-1 flex flex-col sm:flex-row gap-3 w-full">
            <div class="relative flex-1">
                <input type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by Application ID, Customer Name, Phone, City..."
                    class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-borderline focus:outline-none focus:ring-2 focus:ring-brand/30 text-sm bg-bglight transition-all">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                
                <!-- Live Search Loading Indicator -->
                <div wire:loading wire:target="search" class="absolute right-3.5 top-3">
                    <svg class="animate-spin h-4 w-4 text-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>

            @if(!empty($search) || !empty($status) || !empty($scope))
                <button type="button" wire:click="resetFilters"
                    class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-medium transition-all text-center">
                    Reset Filters
                </button>
            @endif
        </div>

        @if($user && $user->role === 'sales_executive')
        <div class="flex items-center space-x-2">
            <button type="button" wire:click="toggleMyLeads"
                class="px-3.5 py-2 text-xs font-semibold rounded-xl border transition-all {{ $scope === 'my' ? 'bg-brand text-white border-brand' : 'bg-gray-50 text-gray-700 border-borderline' }}">
                {{ $scope === 'my' ? '✓ Showing My Leads' : 'Show Only My Leads' }}
            </button>
        </div>
        @endif
    </div>

    <!-- Applications Data Table (Livewire Reactive) -->
    <div class="bg-white rounded-2xl border border-borderline shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50/70 border-b border-borderline text-[11px] uppercase tracking-wider text-gray-500 font-semibold">
                    <tr>
                        <th class="py-4 px-6">Application ID</th>
                        <th class="py-4 px-6">Applicant Info</th>
                        <th class="py-4 px-6">Loan Product</th>
                        <th class="py-4 px-6">Amount (₹)</th>
                        <th class="py-4 px-6">Location</th>
                        <th class="py-4 px-6">Assigned Sales</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borderline">
                    @forelse($applications as $app)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="py-4 px-6 font-mono font-bold text-brand">
                            {{ $app->application_number }}
                            <div class="text-[11px] font-sans font-normal text-gray-400">
                                {{ $app->created_at->format('d M Y, h:i A') }}
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <div class="font-semibold text-charcoal">{{ $app->applicant_name }}</div>
                            <div class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                📞 +91 {{ $app->applicant_phone }}
                            </div>
                        </td>
                        <td class="py-4 px-6 font-medium text-gray-700">
                            {{ $app->loanType->name ?? 'Standard' }}
                        </td>
                        <td class="py-4 px-6 font-semibold text-charcoal">
                            ₹{{ number_format($app->requested_amount, 2) }}
                        </td>
                        <td class="py-4 px-6 text-gray-600 text-xs">
                            {{ $app->city }} {{ $app->pincode ? '(' . $app->pincode . ')' : '' }}
                        </td>
                        <td class="py-4 px-6 text-xs text-gray-600">
                            @if($app->assignedSales)
                                <span class="font-medium text-gray-800">{{ $app->assignedSales->name }}</span>
                            @else
                                <span class="text-amber-600 font-medium bg-amber-50 px-2 py-0.5 rounded border border-amber-200">Unassigned</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
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
                            <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full border {{ $badgeClasses[$app->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ str_replace('_', ' ', $app->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <a href="{{ route('applications.show', $app->id) }}"
                                class="inline-flex items-center px-3.5 py-1.5 bg-brand/10 hover:bg-brand hover:text-white text-brand rounded-lg text-xs font-semibold transition-all">
                                Open Details →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p class="font-medium text-sm">No applications found matching the selected filter.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
        <div class="p-4 border-t border-borderline bg-gray-50">
            {{ $applications->links() }}
        </div>
        @endif
    </div>

</div>
