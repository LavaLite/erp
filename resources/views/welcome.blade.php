<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Lavalite ERP') }} - AI-Powered Enterprise Platform</title>

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
                            }
                        }
                    }
                }
            }
        </script>

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white min-h-screen antialiased overflow-x-hidden">
    <body class="bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white min-h-screen antialiased overflow-x-hidden">
        
        <!-- Animated Background Gradient -->
        <div class="fixed inset-0 bg-gradient-to-br from-violet-600/10 via-fuchsia-500/5 to-cyan-500/10 animate-gradient bg-[length:200%_200%] pointer-events-none"></div>
        
        <!-- Grid Pattern Overlay -->
        <div class="fixed inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgyNTUsMjU1LDI1NSwwLjAzKSIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2dyaWQpIi8+PC9zdmc+')] opacity-40 pointer-events-none"></div>

        <!-- Navigation -->
        <nav class="relative z-10 px-6 py-6 lg:px-12">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-fuchsia-500 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/50">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-display font-bold bg-gradient-to-r from-violet-400 to-fuchsia-400 bg-clip-text text-transparent">
                        Lavalite ERP
                    </span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" id="login-link" class="px-6 py-2.5 hover:bg-white/10 rounded-lg text-sm font-medium transition-all duration-200">
                        Log in
                    </a>
                    <a href="{{ route('register') }}" id="register-link" class="px-6 py-2.5 bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 rounded-lg text-sm font-semibold shadow-lg shadow-violet-500/50 transition-all duration-200 transform hover:scale-105">
                        Get Started
                    </a>
                    <a href="/dashboard" id="dashboard-link" class="hidden px-6 py-2.5 bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 rounded-lg text-sm font-semibold shadow-lg shadow-violet-500/50 transition-all duration-200 transform hover:scale-105">
                        Dashboard
                    </a>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="relative z-10 px-6 py-20 lg:py-32">
            <div class="max-w-7xl mx-auto">
                <!-- Hero Content -->
                <div class="text-center max-w-4xl mx-auto mb-20">
                    <div class="inline-flex items-center space-x-2 px-4 py-2 bg-violet-500/10 border border-violet-500/20 rounded-full mb-8 backdrop-blur-sm">
                        <span class="w-2 h-2 bg-violet-400 rounded-full animate-pulse"></span>
                        <span class="text-sm text-violet-300 font-medium">AI-Powered Enterprise Platform</span>
                    </div>
                    
                    <h1 class="text-5xl lg:text-7xl font-display font-bold mb-6 leading-tight">
                        <span class="bg-gradient-to-r from-white via-violet-200 to-fuchsia-200 bg-clip-text text-transparent">
                            Transform Your Business
                        </span>
                        <br/>
                        <span class="bg-gradient-to-r from-violet-400 via-fuchsia-400 to-cyan-400 bg-clip-text text-transparent">
                            With Intelligent ERP
                        </span>
                    </h1>
                    
                    <p class="text-xl text-slate-300 mb-10 max-w-2xl mx-auto leading-relaxed">
                        Experience next-generation enterprise resource planning powered by advanced AI. Streamline operations, boost productivity, and make data-driven decisions.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-8 py-4 bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 rounded-xl text-lg font-semibold shadow-2xl shadow-violet-500/50 transition-all duration-200 transform hover:scale-105">
                                Start Free Trial
                            </a>
                        @endif
                        <a href="#features" class="px-8 py-4 bg-white/5 hover:bg-white/10 backdrop-blur-lg border border-white/10 rounded-xl text-lg font-medium transition-all duration-200">
                            Learn More
                        </a>
                    </div>
                </div>

                <!-- Features Grid -->
                <div id="features" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-20">
                    <!-- Feature Card 1 -->
                    <div class="group p-8 bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl hover:bg-white/10 transition-all duration-300 hover:transform hover:scale-105">
                        <div class="w-14 h-14 bg-gradient-to-br from-violet-500 to-purple-500 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-violet-500/50 group-hover:animate-float">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-display font-semibold mb-3 text-white">AI Insights</h3>
                        <p class="text-slate-400">Leverage machine learning to uncover patterns, predict trends, and automate decision-making processes.</p>
                    </div>

                    <!-- Feature Card 2 -->
                    <div class="group p-8 bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl hover:bg-white/10 transition-all duration-300 hover:transform hover:scale-105">
                        <div class="w-14 h-14 bg-gradient-to-br from-fuchsia-500 to-pink-500 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-fuchsia-500/50 group-hover:animate-float">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-display font-semibold mb-3 text-white">Lightning Fast</h3>
                        <p class="text-slate-400">Built on modern architecture for blazing-fast performance and real-time data synchronization.</p>
                    </div>

                    <!-- Feature Card 3 -->
                    <div class="group p-8 bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl hover:bg-white/10 transition-all duration-300 hover:transform hover:scale-105">
                        <div class="w-14 h-14 bg-gradient-to-br from-cyan-500 to-blue-500 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-cyan-500/50 group-hover:animate-float">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-display font-semibold mb-3 text-white">Enterprise Security</h3>
                        <p class="text-slate-400">Bank-grade encryption, role-based access control, and compliance with global security standards.</p>
                    </div>

                    <!-- Feature Card 4 -->
                    <div class="group p-8 bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl hover:bg-white/10 transition-all duration-300 hover:transform hover:scale-105">
                        <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-emerald-500/50 group-hover:animate-float">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-display font-semibold mb-3 text-white">Team Collaboration</h3>
                        <p class="text-slate-400">Seamless multi-organization support with advanced team management and permission controls.</p>
                    </div>

                    <!-- Feature Card 5 -->
                    <div class="group p-8 bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl hover:bg-white/10 transition-all duration-300 hover:transform hover:scale-105">
                        <div class="w-14 h-14 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-orange-500/50 group-hover:animate-float">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-display font-semibold mb-3 text-white">Smart Analytics</h3>
                        <p class="text-slate-400">Real-time dashboards, predictive analytics, and customizable reports to drive business intelligence.</p>
                    </div>

                    <!-- Feature Card 6 -->
                    <div class="group p-8 bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl hover:bg-white/10 transition-all duration-300 hover:transform hover:scale-105">
                        <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-indigo-500/50 group-hover:animate-float">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-display font-semibold mb-3 text-white">Module Ecosystem</h3>
                        <p class="text-slate-400">Extensible architecture with customizable modules for billing, inventory, CRM, and more.</p>
                    </div>
                </div>

                <!-- Stats Section -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 p-8 lg:p-12 bg-white/5 backdrop-blur-lg border border-white/10 rounded-3xl">
                    <div class="text-center">
                        <div class="text-4xl lg:text-5xl font-display font-bold bg-gradient-to-r from-violet-400 to-fuchsia-400 bg-clip-text text-transparent mb-2">99.9%</div>
                        <div class="text-slate-400 text-sm">Uptime</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl lg:text-5xl font-display font-bold bg-gradient-to-r from-fuchsia-400 to-pink-400 bg-clip-text text-transparent mb-2">50ms</div>
                        <div class="text-slate-400 text-sm">Response Time</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl lg:text-5xl font-display font-bold bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent mb-2">256bit</div>
                        <div class="text-slate-400 text-sm">Encryption</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl lg:text-5xl font-display font-bold bg-gradient-to-r from-emerald-400 to-teal-400 bg-clip-text text-transparent mb-2">24/7</div>
                        <div class="text-slate-400 text-sm">Support</div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 px-6 py-12 border-t border-white/10">
            <div class="max-w-7xl mx-auto text-center text-slate-400 text-sm">
                <p>&copy; {{ date('Y') }} Lavalite ERP. Powered by AI. Built with Laravel.</p>
            </div>
        </footer>

        <script>
            // Check authentication status and update navigation
            document.addEventListener('DOMContentLoaded', function() {
                const authToken = localStorage.getItem('auth_token');
                const loginLink = document.getElementById('login-link');
                const registerLink = document.getElementById('register-link');
                const dashboardLink = document.getElementById('dashboard-link');

                if (authToken) {
                    // User is logged in - show dashboard link
                    loginLink.classList.add('hidden');
                    registerLink.classList.add('hidden');
                    dashboardLink.classList.remove('hidden');
                } else {
                    // User is not logged in - show login/register links
                    loginLink.classList.remove('hidden');
                    registerLink.classList.remove('hidden');
                    dashboardLink.classList.add('hidden');
                }
            });
        </script>

    </body>
</html>
