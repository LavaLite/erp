<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Sign In') }} - Lavalite ERP</title>
    
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
            <!-- Logo & Back to Home -->
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
                    {{ __('Welcome back') }}
                </h2>
                <p class="text-slate-400">
                    {{ __("Don't have an account?") }}
                    <a href="{{ route('register') }}" class="font-medium text-violet-400 hover:text-violet-300 transition-colors">
                        {{ __('Sign up') }}
                    </a>
                </p>
            </div>

            <!-- Login Form Card -->
            <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl p-8 shadow-2xl">
                <form method="POST" action="/api/login" class="space-y-6">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-2">
                            {{ __('Email address') }}
                        </label>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            autocomplete="email" 
                            required 
                            value="{{ old('email') }}"
                            class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                            placeholder="you@example.com"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-300 mb-2">
                            {{ __('Password') }}
                        </label>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            autocomplete="current-password" 
                            required 
                            class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                            placeholder="••••••••"
                        >
                        @error('password')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                id="remember_me" 
                                name="remember" 
                                type="checkbox" 
                                class="h-4 w-4 rounded border-white/10 bg-white/5 text-violet-500 focus:ring-2 focus:ring-violet-500"
                            >
                            <label for="remember_me" class="ml-2 block text-sm text-slate-300">
                                {{ __('Remember me') }}
                            </label>
                        </div>

                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-violet-400 hover:text-violet-300 transition-colors">
                            {{ __('Forgot password?') }}
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full px-6 py-3 bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 rounded-lg text-white font-semibold shadow-lg shadow-violet-500/50 transition-all duration-200 transform hover:scale-105"
                    >
                        {{ __('Sign in') }}
                    </button>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-white/10"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-3 bg-transparent text-slate-400">
                                {{ __('Or continue with') }}
                            </span>
                        </div>
                    </div>

                    <!-- Social Login -->
                    <div class="grid grid-cols-2 gap-3">
                        <button 
                            type="button" 
                            class="flex items-center justify-center px-4 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg transition-all"
                        >
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span class="ml-2 text-sm font-medium text-slate-300">Google</span>
                        </button>
                        <button 
                            type="button" 
                            class="flex items-center justify-center px-4 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg transition-all"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.17 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.604-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.463-1.11-1.463-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0112 6.836c.85.004 1.705.114 2.504.336 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.167 22 16.418 22 12c0-5.523-4.477-10-10-10z"/>
                            </svg>
                            <span class="ml-2 text-sm font-medium text-slate-300">GitHub</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer Text -->
            <p class="mt-8 text-center text-sm text-slate-400">
                {{ __('By signing in, you agree to our') }}
                <a href="#" class="font-medium text-violet-400 hover:text-violet-300">{{ __('Terms') }}</a>
                {{ __('and') }}
                <a href="#" class="font-medium text-violet-400 hover:text-violet-300">{{ __('Privacy Policy') }}</a>
            </p>
        </div>
    </div>

    <script>
        // Redirect to dashboard if already logged in
        if (localStorage.getItem('auth_token')) {
            window.location.href = '/dashboard';
        }

        document.querySelector('form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            // Disable button and show loading
            submitButton.disabled = true;
            submitButton.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            
            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Store token if provided
                    if (data.access_token) {
                        localStorage.setItem('auth_token', data.access_token);
                    }
                    if (data.refresh_token) {
                        localStorage.setItem('refresh_token', data.refresh_token);
                    }
                    if (data.user) {
                        localStorage.setItem('user_data', JSON.stringify(data.user));
                    }
                    // Redirect to dashboard
                    window.location.href = '/dashboard';
                } else {
                    // Show error messages
                    if (data.message) {
                        alert(data.message);
                    } else if (data.errors) {
                        const errors = Object.values(data.errors).flat();
                        alert(errors.join('\n'));
                    }
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            } catch (error) {
                console.error('Login error:', error);
                alert('An error occurred during login. Please try again.');
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    </script>

</body>
</html>
