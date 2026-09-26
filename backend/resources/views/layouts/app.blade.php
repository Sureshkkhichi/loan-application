<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'LoanDesk Operations Portal' }}</title>
    <!-- Tailwind CSS CDN for modern fintech layout -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#123B6D',
                            dark: '#0B294D',
                            light: '#1B4E8C',
                        },
                        accent: '#16A085',
                        surface: '#FFFFFF',
                        bglight: '#F6F8FB',
                        charcoal: '#172033',
                        borderline: '#E4E8EF',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #F6F8FB;
            color: #172033;
            font-family: 'Inter', sans-serif;
        }
    </style>
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="bg-[#123B6D] text-white shadow-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    <div class="w-9 h-9 rounded-lg bg-teal-500/20 border border-teal-400/40 flex items-center justify-center font-bold text-teal-300 text-lg">
                        LD
                    </div>
                    <div>
                        <span class="text-xl font-bold tracking-tight text-white">Loan<span class="text-teal-400">Desk</span></span>
                        <span class="hidden sm:inline-block ml-2 text-xs uppercase px-2 py-0.5 rounded bg-blue-900/60 text-blue-200 border border-blue-700/50">Operations Portal</span>
                    </div>
                </a>
            </div>

            @auth
            <div class="flex items-center space-x-4">
                <div class="hidden md:flex flex-col text-right">
                    <span class="text-sm font-semibold text-white">{{ Auth::user()->name }}</span>
                    <span class="text-xs text-blue-200 capitalize font-medium">
                        {{ str_replace('_', ' ', Auth::user()->role) }}
                    </span>
                </div>
                <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full uppercase tracking-wider
                    {{ Auth::user()->role === 'admin' ? 'bg-amber-500/20 text-amber-300 border border-amber-400/30' : (Auth::user()->role === 'manager' ? 'bg-purple-500/20 text-purple-300 border border-purple-400/30' : 'bg-teal-500/20 text-teal-300 border border-teal-400/30') }}">
                    {{ Auth::user()->role }}
                </span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded-lg border border-white/20 transition-all font-medium">
                        Sign Out
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm">
                <div class="flex items-center space-x-2 mb-1">
                    <svg class="w-5 h-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <span class="text-sm font-semibold">Please correct the following errors:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5 ml-6">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-borderline py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-gray-500">
            © {{ date('Y') }} LoanDesk Operations System • Modern Trust Fintech Platform
        </div>
    </footer>

    @livewireScripts
</body>
</html>
