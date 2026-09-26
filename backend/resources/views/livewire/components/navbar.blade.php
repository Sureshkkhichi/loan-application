<header class="bg-[#123B6D] text-white shadow-md sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                <div class="w-9 h-9 rounded-lg bg-teal-500/20 border border-teal-400/40 flex items-center justify-center font-bold text-teal-300 text-lg">
                    LD
                </div>
                <div>
                    <span class="text-xl font-bold tracking-tight text-white">Loan<span class="text-teal-400">Desk</span></span>
                    <span class="hidden sm:inline-block ml-2 text-xs uppercase px-2 py-0.5 rounded bg-blue-900/60 text-blue-200 border border-blue-700/50">Operations Portal</span>
                </div>
            </a>

            @auth
            <nav class="hidden md:flex items-center space-x-2 ml-4">
                <a href="{{ route('dashboard') }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request()->routeIs('dashboard') ? 'bg-white/15 text-white' : 'text-blue-200 hover:bg-white/10 hover:text-white' }} transition-colors">
                    Dashboard
                </a>
                <a href="{{ route('home') }}" target="_blank"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-200 hover:bg-white/10 hover:text-white transition-colors flex items-center gap-1">
                    Public Portal ↗
                </a>
            </nav>
            @endauth
        </div>

        @auth
        <div class="flex items-center space-x-4">
            <!-- Reactive Notification Badges -->
            <div class="hidden lg:flex items-center space-x-2">
                @if($newLeadsCount > 0)
                    <a href="{{ route('dashboard', ['status' => 'NEW']) }}"
                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-blue-500/20 text-blue-200 border border-blue-400/30 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-ping"></span>
                        New Leads: {{ $newLeadsCount }}
                    </a>
                @endif

                @if($pendingReviewsCount > 0)
                    <a href="{{ route('dashboard', ['status' => 'SUBMITTED_FOR_REVIEW']) }}"
                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-amber-500/20 text-amber-200 border border-amber-400/30 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                        For Review: {{ $pendingReviewsCount }}
                    </a>
                @endif
            </div>

            <!-- User Info & Role -->
            <div class="hidden sm:flex flex-col text-right">
                <span class="text-sm font-semibold text-white">{{ $user->name }}</span>
                <span class="text-xs text-blue-200 capitalize font-medium">
                    {{ str_replace('_', ' ', $user->role) }}
                </span>
            </div>

            <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full uppercase tracking-wider
                {{ $user->role === 'admin' ? 'bg-amber-500/20 text-amber-300 border border-amber-400/30' : ($user->role === 'manager' ? 'bg-purple-500/20 text-purple-300 border border-purple-400/30' : 'bg-teal-500/20 text-teal-300 border border-teal-400/30') }}">
                {{ $user->role }}
            </span>

            <!-- Livewire Logout Button -->
            <button type="button" wire:click="logout" wire:loading.attr="disabled"
                class="text-xs bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded-lg border border-white/20 transition-all font-medium disabled:opacity-50">
                <span wire:loading.remove wire:target="logout">Sign Out</span>
                <span wire:loading wire:target="logout">Signing out...</span>
            </button>
        </div>
        @else
        <div class="flex items-center space-x-3">
            <a href="{{ route('login') }}" class="text-xs bg-teal-500 hover:bg-teal-600 text-white font-semibold px-4 py-2 rounded-xl transition-all shadow-sm">
                Staff Sign In →
            </a>
        </div>
        @endauth
    </div>
</header>
