<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Change Password') }} - Lavalite ERP</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=space-grotesk:500,600,700" rel="stylesheet" />
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        display: ['Space Grotesk', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'gradient': 'gradient 8s ease infinite',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        gradient: {
                            '0%, 100%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white min-h-screen antialiased overflow-x-hidden">
    
    <!-- Animated Background Gradient -->
    <div class="fixed inset-0 bg-gradient-to-br from-violet-600/10 via-fuchsia-500/5 to-cyan-500/10 animate-gradient bg-[length:200%_200%] pointer-events-none"></div>
    
    <!-- Grid Pattern Overlay -->
    <div class="fixed inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgyNTUsMjU1LDI1NSwwLjAzKSIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2dyaWQpIi8+PC9zdmc+')] opacity-40 pointer-events-none"></div>

    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
        <div class="w-full max-w-2xl">
            <!-- Header -->
            <div class="text-center mb-8">
                <a href="/" class="inline-flex items-center justify-center space-x-3 mb-8">
                    <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-fuchsia-500 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/50">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-display font-bold bg-gradient-to-r from-violet-400 to-fuchsia-400 bg-clip-text text-transparent">
                        Lavalite ERP
                    </span>
                </a>
                
                <h2 class="text-3xl font-display font-bold text-white mb-3">
                    {{ __('Change Password') }}
                </h2>
                <p class="text-slate-400">
                    {{ __('Keep your account secure by using a strong password.') }}
                </p>
            </div>

            <!-- Change Password Form Card -->
            <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl p-8 shadow-2xl">
                @if (session('success'))
                    <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-emerald-300">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="/api/profile/password" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-slate-300 mb-2">
                            {{ __('Current password') }}
                        </label>
                        <input 
                            id="current_password" 
                            name="current_password" 
                            type="password" 
                            autocomplete="current-password" 
                            required 
                            class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                            placeholder="••••••••"
                        >
                        @error('current_password')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="border-t border-white/10"></div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-300 mb-2">
                            {{ __('New password') }}
                        </label>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            autocomplete="new-password" 
                            required 
                            class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                            placeholder="••••••••"
                        >
                        @error('password')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">
                            {{ __('Confirm new password') }}
                        </label>
                        <input 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            type="password" 
                            autocomplete="new-password" 
                            required 
                            class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                            placeholder="••••••••"
                        >
                    </div>

                    <!-- Password Strength Indicator -->
                    <div class="bg-white/5 border border-white/10 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-medium text-slate-300">{{ __('Password strength:') }}</p>
                            <span id="strength-text" class="text-xs font-semibold text-slate-400">{{ __('Enter password') }}</span>
                        </div>
                        <div class="flex space-x-1 mb-3">
                            <div id="strength-bar-1" class="h-1 flex-1 bg-white/10 rounded transition-colors"></div>
                            <div id="strength-bar-2" class="h-1 flex-1 bg-white/10 rounded transition-colors"></div>
                            <div id="strength-bar-3" class="h-1 flex-1 bg-white/10 rounded transition-colors"></div>
                            <div id="strength-bar-4" class="h-1 flex-1 bg-white/10 rounded transition-colors"></div>
                        </div>
                        <ul class="space-y-1 text-xs text-slate-400">
                            <li class="flex items-center">
                                <svg id="check-length" class="w-3 h-3 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('At least 8 characters') }}
                            </li>
                            <li class="flex items-center">
                                <svg id="check-upper" class="w-3 h-3 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('One uppercase letter') }}
                            </li>
                            <li class="flex items-center">
                                <svg id="check-lower" class="w-3 h-3 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('One lowercase letter') }}
                            </li>
                            <li class="flex items-center">
                                <svg id="check-number" class="w-3 h-3 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('One number or special character') }}
                            </li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-4 pt-4">
                        <button 
                            type="submit" 
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 rounded-lg text-white font-semibold shadow-lg shadow-violet-500/50 transition-all duration-200 transform hover:scale-105"
                        >
                            {{ __('Update password') }}
                        </button>
                        <a 
                            href="/" 
                            class="px-6 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg text-white font-semibold transition-all text-center"
                        >
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Security Tips -->
            <div class="mt-8 bg-white/5 backdrop-blur-lg border border-white/10 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-white mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    {{ __('Password Security Tips') }}
                </h3>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 text-violet-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('Use a unique password that you don\'t use elsewhere') }}
                    </li>
                    <li class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 text-violet-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('Avoid common words or easily guessable information') }}
                    </li>
                    <li class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 text-violet-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('Consider using a password manager for better security') }}
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthText = document.getElementById('strength-text');
        const strengthBars = [
            document.getElementById('strength-bar-1'),
            document.getElementById('strength-bar-2'),
            document.getElementById('strength-bar-3'),
            document.getElementById('strength-bar-4')
        ];
        const checks = {
            length: document.getElementById('check-length'),
            upper: document.getElementById('check-upper'),
            lower: document.getElementById('check-lower'),
            number: document.getElementById('check-number')
        };

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Reset bars
            strengthBars.forEach(bar => {
                bar.className = 'h-1 flex-1 bg-white/10 rounded transition-colors';
            });

            // Check requirements
            const hasLength = password.length >= 8;
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /[0-9!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);

            // Update checkmarks
            checks.length.className = hasLength ? 'w-3 h-3 mr-2 text-emerald-400' : 'w-3 h-3 mr-2 text-slate-500';
            checks.upper.className = hasUpper ? 'w-3 h-3 mr-2 text-emerald-400' : 'w-3 h-3 mr-2 text-slate-500';
            checks.lower.className = hasLower ? 'w-3 h-3 mr-2 text-emerald-400' : 'w-3 h-3 mr-2 text-slate-500';
            checks.number.className = hasNumber ? 'w-3 h-3 mr-2 text-emerald-400' : 'w-3 h-3 mr-2 text-slate-500';

            // Calculate strength
            if (hasLength) strength++;
            if (hasUpper) strength++;
            if (hasLower) strength++;
            if (hasNumber) strength++;

            // Update strength bars and text
            if (password.length === 0) {
                strengthText.textContent = 'Enter password';
                strengthText.className = 'text-xs font-semibold text-slate-400';
            } else if (strength <= 1) {
                strengthText.textContent = 'Weak';
                strengthText.className = 'text-xs font-semibold text-red-400';
                strengthBars[0].className = 'h-1 flex-1 bg-red-500 rounded transition-colors';
            } else if (strength === 2) {
                strengthText.textContent = 'Fair';
                strengthText.className = 'text-xs font-semibold text-orange-400';
                strengthBars[0].className = 'h-1 flex-1 bg-orange-500 rounded transition-colors';
                strengthBars[1].className = 'h-1 flex-1 bg-orange-500 rounded transition-colors';
            } else if (strength === 3) {
                strengthText.textContent = 'Good';
                strengthText.className = 'text-xs font-semibold text-yellow-400';
                strengthBars[0].className = 'h-1 flex-1 bg-yellow-500 rounded transition-colors';
                strengthBars[1].className = 'h-1 flex-1 bg-yellow-500 rounded transition-colors';
                strengthBars[2].className = 'h-1 flex-1 bg-yellow-500 rounded transition-colors';
            } else {
                strengthText.textContent = 'Strong';
                strengthText.className = 'text-xs font-semibold text-emerald-400';
                strengthBars.forEach(bar => {
                    bar.className = 'h-1 flex-1 bg-emerald-500 rounded transition-colors';
                });
            }
        });
    </script>

</body>
</html>
