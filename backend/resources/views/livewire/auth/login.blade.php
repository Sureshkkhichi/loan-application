<div class="max-w-md mx-auto mt-12">
    <div class="bg-white rounded-2xl shadow-sm border border-borderline overflow-hidden p-8">
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-brand/10 border border-brand/20 items-center justify-center text-brand font-bold text-2xl mb-4">
                LD
            </div>
            <h1 class="text-2xl font-bold text-charcoal tracking-tight">Staff Portal Sign In</h1>
            <p class="text-sm text-gray-500 mt-1">LoanDesk Sales & Operations Management</p>
        </div>

        <form wire:submit.prevent="login" class="space-y-5">
            <div>
                <label for="email" class="block text-xs font-semibold text-charcoal uppercase tracking-wider mb-2">Work Email Address</label>
                <input type="email" wire:model="email" id="email" required autofocus
                    placeholder="e.g. admin@loandesk.com"
                    class="w-full px-4 py-3 rounded-xl border border-borderline focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand text-sm bg-bglight transition-all">
                @error('email') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-charcoal uppercase tracking-wider mb-2">Password</label>
                <input type="password" wire:model="password" id="password" required
                    placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-xl border border-borderline focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand text-sm bg-bglight transition-all">
                @error('password') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center space-x-2 text-gray-600">
                    <input type="checkbox" wire:model="remember" class="rounded border-gray-300 text-brand focus:ring-brand">
                    <span>Remember this device</span>
                </label>
            </div>

            <button type="submit" wire:loading.attr="disabled"
                class="w-full py-3.5 bg-brand hover:bg-brand-dark text-white font-semibold rounded-xl text-sm shadow-sm hover:shadow transition-all duration-150 disabled:opacity-50">
                <span wire:loading.remove wire:target="login">Sign In to Dashboard</span>
                <span wire:loading wire:target="login">Signing In...</span>
            </button>
        </form>

        <!-- Demo accounts helper box -->
        <div class="mt-8 pt-6 border-t border-borderline text-xs bg-gray-50 -mx-8 -mb-8 p-6">
            <p class="font-semibold text-gray-700 mb-2">Default Staff Credentials (Seeded):</p>
            <div class="space-y-1.5 text-gray-600">
                <div class="flex justify-between">
                    <span>👑 Admin/Manager:</span>
                    <code class="bg-gray-200 px-1.5 py-0.5 rounded text-gray-800">admin@loandesk.com / password123</code>
                </div>
                <div class="flex justify-between">
                    <span>💼 Sales Executive:</span>
                    <code class="bg-gray-200 px-1.5 py-0.5 rounded text-gray-800">sales@loandesk.com / password123</code>
                </div>
            </div>
        </div>
    </div>
</div>
