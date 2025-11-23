<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Two Factor Authentication') }} - Lavalite ERP</title>
    
    <!-- Fonts -->
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
                        display: ['Space Grotesk', 'Inter', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'gradient': 'gradient 8s linear infinite',
                    },
                    keyframes: {
                        gradient: {
                            '0%, 100%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white min-h-screen antialiased overflow-x-hidden">
    
    <!-- Animated Background Gradient -->
    <div class="fixed inset-0 bg-gradient-to-br from-violet-600/10 via-fuchsia-500/5 to-cyan-500/10 animate-gradient bg-[length:200%_200%] pointer-events-none"></div>
    
    <!-- Grid Pattern Overlay -->
    <div class="fixed inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgyNTUsMjU1LDI1NSwwLjAzKSIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2dyaWQpIi8+PC9zdmc+')] opacity-40 pointer-events-none"></div>

    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center space-x-3 mb-8">
                    <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-fuchsia-500 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/50">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-display font-bold bg-gradient-to-r from-violet-400 to-fuchsia-400 bg-clip-text text-transparent">
                        Lavalite ERP
                    </span>
                </div>
                
                <h2 class="text-3xl font-display font-bold text-white mb-3">
                    {{ __('Two-Factor Authentication') }}
                </h2>
                <p class="text-slate-400">
                    {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
                </p>
            </div>

            <!-- Challenge Form Card -->
            <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl p-8 shadow-2xl">
                <form method="POST" action="{{ route('2fa.challenge.store') }}" class="space-y-6">
                    @csrf

                    <!-- Code -->
                    <div id="code-group">
                        <label for="code" class="block text-sm font-medium text-slate-300 mb-2">
                            {{ __('Authentication Code') }}
                        </label>
                        <input 
                            id="code" 
                            name="code" 
                            type="text" 
                            inputmode="numeric" 
                            pattern="[0-9]*" 
                            autocomplete="one-time-code" 
                            autofocus
                            class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all text-center tracking-widest text-xl"
                            placeholder="XXXXXX"
                        >
                    </div>

                    <!-- Recovery Code (Hidden by default) -->
                    <div id="recovery-group" class="hidden">
                        <label for="recovery_code" class="block text-sm font-medium text-slate-300 mb-2">
                            {{ __('Recovery Code') }}
                        </label>
                        <input 
                            id="recovery_code" 
                            name="recovery_code" 
                            type="text" 
                            autocomplete="off"
                            class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                            placeholder="Enter recovery code"
                        >
                    </div>

                    @if ($errors->any())
                        <div class="p-4 rounded-lg bg-red-500/10 border border-red-500/20">
                            <ul class="list-disc list-inside text-sm text-red-400">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full px-6 py-3 bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 rounded-lg text-white font-semibold shadow-lg shadow-violet-500/50 transition-all duration-200 transform hover:scale-105"
                    >
                        {{ __('Verify') }}
                    </button>

                    <!-- Toggle Recovery Code -->
                    <div class="text-center">
                        <button type="button" id="toggle-recovery" class="text-sm font-medium text-violet-400 hover:text-violet-300 transition-colors">
                            {{ __('Use a recovery code') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const toggleBtn = document.getElementById('toggle-recovery');
        const codeGroup = document.getElementById('code-group');
        const recoveryGroup = document.getElementById('recovery-group');
        const codeInput = document.getElementById('code');
        const recoveryInput = document.getElementById('recovery_code');
        let usingRecovery = false;

        toggleBtn.addEventListener('click', function() {
            usingRecovery = !usingRecovery;
            
            if (usingRecovery) {
                codeGroup.classList.add('hidden');
                recoveryGroup.classList.remove('hidden');
                codeInput.value = '';
                recoveryInput.focus();
                toggleBtn.textContent = '{{ __('Use an authentication code') }}';
            } else {
                recoveryGroup.classList.add('hidden');
                codeGroup.classList.remove('hidden');
                recoveryInput.value = '';
                codeInput.focus();
                toggleBtn.textContent = '{{ __('Use a recovery code') }}';
            }
        });
    </script>

</body>
</html>
