<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Dashboard') }} - Lavalite ERP</title>
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
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
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

    <!-- Navigation -->
    <nav class="relative z-10 bg-white/5 backdrop-blur-lg border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="/" class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-fuchsia-500 rounded-lg flex items-center justify-center shadow-lg shadow-violet-500/50">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-display font-bold bg-gradient-to-r from-violet-400 to-fuchsia-400 bg-clip-text text-transparent">
                        Lavalite ERP
                    </span>
                </a>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-slate-400">{{ __('Welcome,') }} <span id="user-name" class="text-white font-medium">User</span></span>
                    <a href="{{ route('password.change') }}" class="text-sm text-slate-400 hover:text-white transition-colors">
                        {{ __('Settings') }}
                    </a>
                    <button onclick="logout()" class="px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg text-sm font-medium transition-all">
                        {{ __('Logout') }}
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Welcome Section -->
        <div class="mb-12">
            <div class="flex items-center space-x-4 mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-violet-500 to-fuchsia-500 rounded-2xl flex items-center justify-center shadow-lg shadow-violet-500/50 animate-float">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-4xl font-display font-bold text-white mb-2">
                        {{ __('Welcome to Lavalite ERP!') }}
                    </h1>
                    <p class="text-lg text-slate-400">
                        {{ __('Get started by installing the plugin to unlock powerful features.') }}
                    </p>
                </div>
            </div>

            <!-- Success Message -->
            <div class="bg-gradient-to-r from-emerald-500/10 to-cyan-500/10 border border-emerald-500/20 rounded-xl p-6 backdrop-blur-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-semibold text-emerald-300 mb-1">{{ __('Successfully Logged In!') }}</h3>
                        <p class="text-sm text-slate-300">
                            {{ __('Your account is active and ready to use. Follow the steps below to install and configure the Lavalite ERP plugin.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Installation Guide -->
        <div class="grid lg:grid-cols-3 gap-6 mb-12">
            
            <!-- Step 1 -->
            <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl p-6 hover:bg-white/10 transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl flex items-center justify-center text-white font-display font-bold text-lg shadow-lg shadow-violet-500/50">
                        1
                    </div>
                    <span class="px-3 py-1 bg-violet-500/10 border border-violet-500/20 rounded-full text-xs font-medium text-violet-300">
                        {{ __('Required') }}
                    </span>
                </div>
                <h3 class="text-xl font-display font-bold text-white mb-3">
                    {{ __('Download Plugin') }}
                </h3>
                <p class="text-sm text-slate-400 mb-4">
                    {{ __('Download the latest version of the Lavalite ERP plugin package from our official repository.') }}
                </p>
                <button class="w-full px-4 py-3 bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 rounded-lg text-white font-semibold shadow-lg shadow-violet-500/50 transition-all duration-200 transform hover:scale-105 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    {{ __('Download Now') }}
                </button>
            </div>

            <!-- Step 2 -->
            <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl p-6 hover:bg-white/10 transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-fuchsia-500 to-fuchsia-600 rounded-xl flex items-center justify-center text-white font-display font-bold text-lg shadow-lg shadow-fuchsia-500/50">
                        2
                    </div>
                    <span class="px-3 py-1 bg-fuchsia-500/10 border border-fuchsia-500/20 rounded-full text-xs font-medium text-fuchsia-300">
                        {{ __('Required') }}
                    </span>
                </div>
                <h3 class="text-xl font-display font-bold text-white mb-3">
                    {{ __('Install Package') }}
                </h3>
                <p class="text-sm text-slate-400 mb-4">
                    {{ __('Run the installation command in your terminal to add the plugin to your project.') }}
                </p>
                <div class="bg-slate-900/50 border border-white/10 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-slate-400">{{ __('Terminal') }}</span>
                        <button onclick="copyToClipboard('composer require lavalite/erp-plugin')" class="text-xs text-violet-400 hover:text-violet-300 transition-colors">
                            {{ __('Copy') }}
                        </button>
                    </div>
                    <code class="text-sm text-emerald-400 font-mono">
                        composer require lavalite/erp-plugin
                    </code>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl p-6 hover:bg-white/10 transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl flex items-center justify-center text-white font-display font-bold text-lg shadow-lg shadow-cyan-500/50">
                        3
                    </div>
                    <span class="px-3 py-1 bg-cyan-500/10 border border-cyan-500/20 rounded-full text-xs font-medium text-cyan-300">
                        {{ __('Required') }}
                    </span>
                </div>
                <h3 class="text-xl font-display font-bold text-white mb-3">
                    {{ __('Configure & Run') }}
                </h3>
                <p class="text-sm text-slate-400 mb-4">
                    {{ __('Publish configuration files and run migrations to set up your database tables.') }}
                </p>
                <div class="space-y-3">
                    <div class="bg-slate-900/50 border border-white/10 rounded-lg p-3">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-slate-400">{{ __('Publish config') }}</span>
                            <button onclick="copyToClipboard('php artisan vendor:publish --tag=lavalite-config')" class="text-xs text-violet-400 hover:text-violet-300 transition-colors">
                                {{ __('Copy') }}
                            </button>
                        </div>
                        <code class="text-xs text-emerald-400 font-mono break-all">
                            php artisan vendor:publish --tag=lavalite-config
                        </code>
                    </div>
                    <div class="bg-slate-900/50 border border-white/10 rounded-lg p-3">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-slate-400">{{ __('Run migrations') }}</span>
                            <button onclick="copyToClipboard('php artisan migrate')" class="text-xs text-violet-400 hover:text-violet-300 transition-colors">
                                {{ __('Copy') }}
                            </button>
                        </div>
                        <code class="text-xs text-emerald-400 font-mono">
                            php artisan migrate
                        </code>
                    </div>
                </div>
            </div>

        </div>

        <!-- Additional Resources -->
        <div class="grid md:grid-cols-2 gap-6">
            
            <!-- Documentation -->
            <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-xl p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-violet-500/20 to-fuchsia-500/20 border border-violet-500/30 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-display font-bold text-white">{{ __('Documentation') }}</h3>
                        <p class="text-sm text-slate-400">{{ __('Complete API reference & guides') }}</p>
                    </div>
                </div>
                <p class="text-sm text-slate-300 mb-4">
                    {{ __('Access comprehensive documentation including API references, tutorials, and best practices for using Lavalite ERP.') }}
                </p>
                <a href="/docs" class="inline-flex items-center text-sm font-medium text-violet-400 hover:text-violet-300 transition-colors">
                    {{ __('View Documentation') }}
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <!-- Support -->
            <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-xl p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500/20 to-cyan-500/20 border border-emerald-500/30 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-display font-bold text-white">{{ __('Need Help?') }}</h3>
                        <p class="text-sm text-slate-400">{{ __('24/7 support available') }}</p>
                    </div>
                </div>
                <p class="text-sm text-slate-300 mb-4">
                    {{ __('Our support team is ready to help you with installation, configuration, and any questions you may have.') }}
                </p>
                <a href="#" class="inline-flex items-center text-sm font-medium text-emerald-400 hover:text-emerald-300 transition-colors">
                    {{ __('Contact Support') }}
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

        </div>

    </div>

    <!-- Footer -->
    <footer class="relative z-10 mt-16 border-t border-white/10 bg-slate-950/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <div class="text-sm text-slate-400">
                    © {{ date('Y') }} Lavalite ERP. {{ __('All rights reserved.') }}
                </div>
                <div class="flex items-center space-x-6">
                    <a href="#" class="text-sm text-slate-400 hover:text-violet-400 transition-colors">{{ __('Privacy') }}</a>
                    <a href="#" class="text-sm text-slate-400 hover:text-violet-400 transition-colors">{{ __('Terms') }}</a>
                    <a href="#" class="text-sm text-slate-400 hover:text-violet-400 transition-colors">{{ __('Contact') }}</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
       

        function logout() {
            const token = localStorage.getItem('auth_token');
            
            if (token) {
                // Call logout API
                fetch('/api/logout', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).finally(() => {
                    // Clear local storage and redirect
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user_data');
                    localStorage.removeItem('refresh_token');
                    window.location.href = '/login';
                });
            } else {
                // Just redirect if no token
                localStorage.clear();
                window.location.href = '/login';
            }
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Show a temporary success message
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                button.classList.add('text-emerald-400');
                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove('text-emerald-400');
                }, 2000);
            });
        }
    </script>

</body>
</html>
