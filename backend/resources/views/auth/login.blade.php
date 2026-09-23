@extends('layouts.app', ['title' => 'Sign In - LoanDesk Operations'])

@section('content')
<div class="max-w-md mx-auto mt-12">
    <div class="bg-white rounded-2xl shadow-sm border border-borderline overflow-hidden p-8">
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-brand/10 border border-brand/20 items-center justify-center text-brand font-bold text-2xl mb-4">
                LD
            </div>
            <h1 class="text-2xl font-bold text-charcoal tracking-tight">Staff Portal Sign In</h1>
            <p class="text-sm text-gray-500 mt-1">LoanDesk Sales & Operations Management</p>
        </div>

        <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold text-charcoal uppercase tracking-wider mb-2">Work Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                    placeholder="e.g. admin@loandesk.com"
                    class="w-full px-4 py-3 rounded-xl border border-borderline focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand text-sm bg-bglight transition-all">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-charcoal uppercase tracking-wider mb-2">Password</label>
                <input type="password" name="password" id="password" required
                    placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-xl border border-borderline focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand text-sm bg-bglight transition-all">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center space-x-2 text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-brand focus:ring-brand">
                    <span>Remember this device</span>
                </label>
            </div>

            <button type="submit"
                class="w-full py-3.5 bg-brand hover:bg-brand-dark text-white font-semibold rounded-xl text-sm shadow-sm hover:shadow transition-all duration-150">
                Sign In to Dashboard
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
@endsection
