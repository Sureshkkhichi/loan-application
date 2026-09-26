<div class="space-y-12">

    <!-- Hero Section -->
    <div class="text-center max-w-3xl mx-auto pt-4 pb-2">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand/10 border border-brand/20 text-brand text-xs font-semibold mb-4">
            <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
            Modern Trust Fintech Platform • Transparent Lending
        </div>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-charcoal tracking-tight leading-tight">
            Fast, Simple & Secure <br><span class="text-brand">Loan Application Management</span>
        </h1>
        <p class="text-base text-gray-600 mt-4 leading-relaxed">
            Apply seamlessly from our customer mobile app with just your phone number. Track real-time banker review, resolve pendencies in-app, and experience hassle-free loan disbursal.
        </p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('login') }}" class="px-6 py-3 bg-brand hover:bg-brand-dark text-white font-semibold text-sm rounded-xl shadow-md transition-all">
                Staff Operations Login →
            </a>
            <a href="#calculator" class="px-6 py-3 bg-white hover:bg-gray-50 text-charcoal border border-borderline font-semibold text-sm rounded-xl shadow-sm transition-all">
                🧮 Live EMI Calculator
            </a>
        </div>
    </div>

    <!-- Live Interactive EMI Calculator (Livewire Reactive) -->
    <div id="calculator" class="bg-white rounded-3xl border border-borderline shadow-sm p-6 sm:p-10 max-w-4xl mx-auto">
        <div class="border-b border-borderline pb-4 mb-6">
            <h2 class="text-xl font-bold text-charcoal flex items-center gap-2">
                <span>🧮 Interactive EMI Calculator</span>
                <span class="text-xs font-semibold bg-teal-50 text-teal-700 px-2.5 py-0.5 rounded-full border border-teal-200">Live Reactive Calculation</span>
            </h2>
            <p class="text-xs text-gray-500 mt-1">Estimate your monthly instalment and total interest instantly with live sliders.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <!-- Controls -->
            <div class="space-y-6">
                <!-- Loan Amount -->
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Loan Amount (₹)</label>
                        <span class="text-sm font-bold text-brand">₹{{ number_format($loanAmount) }}</span>
                    </div>
                    <input type="range" wire:model.live="loanAmount" min="50000" max="5000000" step="25000"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-brand">
                    <div class="flex justify-between text-[11px] text-gray-400 mt-1">
                        <span>₹50,000</span>
                        <span>₹50,00,000</span>
                    </div>
                </div>

                <!-- Interest Rate -->
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Annual Interest Rate (%)</label>
                        <span class="text-sm font-bold text-teal-700">{{ $interestRate }}% p.a.</span>
                    </div>
                    <input type="range" wire:model.live="interestRate" min="8" max="24" step="0.25"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-teal-600">
                    <div class="flex justify-between text-[11px] text-gray-400 mt-1">
                        <span>8%</span>
                        <span>24%</span>
                    </div>
                </div>

                <!-- Tenure -->
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Tenure (Years)</label>
                        <span class="text-sm font-bold text-brand">{{ $tenureYears }} Years ({{ $tenureYears * 12 }} Months)</span>
                    </div>
                    <input type="range" wire:model.live="tenureYears" min="1" max="10" step="1"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-brand">
                    <div class="flex justify-between text-[11px] text-gray-400 mt-1">
                        <span>1 Year</span>
                        <span>10 Years</span>
                    </div>
                </div>
            </div>

            <!-- Calculated Summary Card -->
            <div class="bg-gradient-to-br from-[#123B6D] to-[#0B294D] text-white p-6 sm:p-8 rounded-2xl shadow-md space-y-6">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-200 block">Estimated Monthly EMI</span>
                    <div class="text-3xl sm:text-4xl font-extrabold text-teal-300 mt-1">
                        ₹{{ number_format($this->monthlyEmi, 2) }}
                    </div>
                    <span class="text-[11px] text-blue-200 mt-0.5 block">per month for {{ $tenureYears * 12 }} months</span>
                </div>

                <div class="pt-4 border-t border-white/15 space-y-3 text-xs">
                    <div class="flex justify-between text-blue-100">
                        <span>Principal Amount:</span>
                        <span class="font-bold text-white">₹{{ number_format($loanAmount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-blue-100">
                        <span>Total Interest Payable:</span>
                        <span class="font-bold text-teal-300">₹{{ number_format($this->totalInterest, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-blue-100 pt-2 border-t border-white/10 font-medium">
                        <span>Total Amount Payable:</span>
                        <span class="font-bold text-white text-sm">₹{{ number_format($this->totalPayable, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan Products Available -->
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-charcoal">Available Loan Products</h2>
            <p class="text-sm text-gray-500 mt-1">Tailored financial products to meet individual & business needs.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($loanTypes as $type)
                <div class="bg-white p-6 rounded-2xl border border-borderline shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand flex items-center justify-center font-bold text-lg mb-3">
                            {{ substr($type->name, 0, 1) }}
                        </div>
                        <h3 class="text-base font-bold text-charcoal">{{ $type->name }}</h3>
                        <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">{{ $type->description }}</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs font-semibold text-brand">
                        <span>Starting @ 10.5%</span>
                        <span>Fast Disbursal →</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
