<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Settings') }} - Lavalite ERP</title>
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

    <div class="relative z-10 min-h-screen px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="max-w-4xl mx-auto mb-8">
            <div class="flex items-center justify-between mb-6">
                <a href="/dashboard" class="inline-flex items-center text-sm text-slate-400 hover:text-white transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('Back to Dashboard') }}
                </a>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-violet-500 to-fuchsia-500 rounded-2xl flex items-center justify-center shadow-lg shadow-violet-500/50">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-display font-bold text-white">
                        {{ __('Settings') }}
                    </h1>
                    <p class="text-slate-400">
                        {{ __('Manage your account settings and preferences') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="max-w-4xl mx-auto mb-8">
            <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-xl p-2 inline-flex space-x-2">
                <button onclick="switchTab('profile')" id="tab-profile" class="tab-button active px-6 py-2 rounded-lg text-sm font-medium transition-all">
                    {{ __('Profile') }}
                </button>
                <button onclick="switchTab('password')" id="tab-password" class="tab-button px-6 py-2 rounded-lg text-sm font-medium transition-all">
                    {{ __('Password') }}
                </button>
                <button onclick="switchTab('2fa')" id="tab-2fa" class="tab-button px-6 py-2 rounded-lg text-sm font-medium transition-all">
                    {{ __('Two Factor Auth') }}
                </button>
            </div>
        </div>

        <div class="max-w-4xl mx-auto">
            <!-- Profile Tab -->
            <div id="content-profile" class="tab-content">
                <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl p-8 shadow-2xl">
                    <h3 class="text-xl font-display font-bold text-white mb-6">{{ __('Profile Information') }}</h3>
                    
                    <div id="profile-success" class="hidden mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-emerald-300">{{ __('Profile updated successfully!') }}</p>
                        </div>
                    </div>

                    <form id="profile-form" class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- First Name -->
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-slate-300 mb-2">
                                    {{ __('First Name') }}
                                </label>
                                <input 
                                    id="first_name" 
                                    name="first_name" 
                                    type="text" 
                                    required 
                                    class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                    placeholder="John"
                                >
                            </div>

                            <!-- Last Name -->
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-slate-300 mb-2">
                                    {{ __('Last Name') }}
                                </label>
                                <input 
                                    id="last_name" 
                                    name="last_name" 
                                    type="text" 
                                    required 
                                    class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                    placeholder="Doe"
                                >
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-300 mb-2">
                                {{ __('Email Address') }}
                            </label>
                            <input 
                                id="email" 
                                name="email" 
                                type="email" 
                                required 
                                class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                placeholder="john@example.com"
                            >
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-300 mb-2">
                                {{ __('Phone Number') }}
                            </label>
                            <input 
                                id="phone" 
                                name="phone" 
                                type="tel" 
                                class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                placeholder="+1 (555) 000-0000"
                            >
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- City -->
                            <div>
                                <label for="city" class="block text-sm font-medium text-slate-300 mb-2">
                                    {{ __('City') }}
                                </label>
                                <input 
                                    id="city" 
                                    name="city" 
                                    type="text" 
                                    class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                    placeholder="San Francisco"
                                >
                            </div>

                            <!-- Country -->
                            <div>
                                <label for="country" class="block text-sm font-medium text-slate-300 mb-2">
                                    {{ __('Country') }}
                                </label>
                                <input 
                                    id="country" 
                                    name="country" 
                                    type="text" 
                                    class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                    placeholder="USA"
                                >
                            </div>
                        </div>

                        <!-- Bio -->
                        <div>
                            <label for="bio" class="block text-sm font-medium text-slate-300 mb-2">
                                {{ __('Bio') }}
                            </label>
                            <textarea 
                                id="bio" 
                                name="bio" 
                                rows="4"
                                class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all resize-none"
                                placeholder="Tell us about yourself..."
                            ></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-4 pt-4">
                            <button 
                                type="submit" 
                                id="profile-submit"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 rounded-lg text-white font-semibold shadow-lg shadow-violet-500/50 transition-all duration-200 transform hover:scale-105"
                            >
                                {{ __('Update Profile') }}
                            </button>
                            <a 
                                href="/dashboard" 
                                class="px-6 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg text-white font-semibold transition-all text-center"
                            >
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Password Tab -->
            <div id="content-password" class="tab-content hidden">
                <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl p-8 shadow-2xl">
                    <h3 class="text-xl font-display font-bold text-white mb-6">{{ __('Change Password') }}</h3>
                    
                    <div id="password-success" class="hidden mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-emerald-300">{{ __('Password updated successfully!') }}</p>
                        </div>
                    </div>

                    <form id="password-form" class="space-y-6">
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
                                id="password-submit"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 rounded-lg text-white font-semibold shadow-lg shadow-violet-500/50 transition-all duration-200 transform hover:scale-105"
                            >
                                {{ __('Update password') }}
                            </button>
                            <a 
                                href="/dashboard" 
                                class="px-6 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg text-white font-semibold transition-all text-center"
                            >
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Security Tips -->
                <div class="mt-6 bg-white/5 backdrop-blur-lg border border-white/10 rounded-xl p-6">
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

            <!-- 2FA Tab -->
            <div id="content-2fa" class="tab-content hidden">
                <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl p-8 shadow-2xl">
                    <h3 class="text-xl font-display font-bold text-white mb-6">{{ __('Two Factor Authentication') }}</h3>

                    <!-- Notification Container -->
                    <div id="2fa-notification-container" class="hidden"></div>

                    <!-- Status Indicator -->
                    <div id="2fa-status-container" class="mb-8 p-4 rounded-lg bg-white/5 border border-white/10 flex items-center justify-between">
                        <div class="flex items-center">
                            <div id="2fa-status-icon" class="w-10 h-10 rounded-full flex items-center justify-center mr-4 bg-slate-700 text-slate-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 id="2fa-status-title" class="text-lg font-semibold text-white">{{ __('2FA is currently disabled') }}</h4>
                                <p id="2fa-status-desc" class="text-sm text-slate-400">{{ __('Enable two-factor authentication to add an extra layer of security to your account.') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Enable 2FA Section -->
                    <div id="2fa-enable-section">
                        <p class="text-slate-300 mb-6">
                            {{ __('When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
                        </p>
                        <button id="btn-enable-2fa" class="px-6 py-3 bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 rounded-lg text-white font-semibold shadow-lg shadow-violet-500/50 transition-all">
                            {{ __('Enable Two-Factor Authentication') }}
                        </button>
                    </div>

                    <!-- Setup 2FA (QR Code) Section -->
                    <div id="2fa-setup-section" class="hidden space-y-6">
                        <div class="border-t border-white/10 pt-6">
                            <h4 class="text-lg font-semibold text-white mb-4">{{ __('Finish enabling two-factor authentication.') }}</h4>
                            <p class="text-slate-300 mb-4">
                                {{ __('To finish enabling two-factor authentication, scan the following QR code using your phone\'s authenticator application or enter the setup key and provide the generated OTP code.') }}
                            </p>
                            
                            <div class="flex flex-col md:flex-row gap-8 items-start">
                                <div class="bg-white p-4 rounded-lg" id="qr-code-container">
                                    <!-- QR Code SVG will be injected here -->
                                </div>
                                <div class="flex-1 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-400 mb-1">{{ __('Setup Key') }}</label>
                                        <div class="flex items-center space-x-2">
                                            <code id="setup-key" class="bg-black/30 px-3 py-2 rounded text-violet-300 font-mono text-sm"></code>
                                            <button type="button" onclick="copyToClipboard('setup-key')" class="text-slate-400 hover:text-white">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <form id="2fa-confirm-form" class="space-y-4">
                                        <div>
                                            <label for="confirm-code" class="block text-sm font-medium text-slate-300 mb-2">{{ __('Code') }}</label>
                                            <input id="confirm-code" name="code" type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" required class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="XXXXXX">
                                        </div>
                                        <div class="flex space-x-3">
                                            <button type="submit" class="px-6 py-2 bg-emerald-500 hover:bg-emerald-600 rounded-lg text-white font-semibold transition-all">
                                                {{ __('Confirm & Enable') }}
                                            </button>
                                            <button type="button" id="btn-cancel-setup" class="px-6 py-2 bg-white/5 hover:bg-white/10 rounded-lg text-white font-semibold transition-all">
                                                {{ __('Cancel') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Manage 2FA Section (Active) -->
                    <div id="2fa-manage-section" class="hidden space-y-6">
                        <p class="text-slate-300">
                            {{ __('Two-factor authentication is enabled. You may disable it or regenerate your recovery codes.') }}
                        </p>

                        <div class="border-t border-white/10 pt-6">
                            <h4 class="text-lg font-semibold text-white mb-4">{{ __('Recovery Codes') }}</h4>
                            <p class="text-slate-400 text-sm mb-4">
                                {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.') }}
                            </p>
                            
                            <div id="recovery-codes-container" class="bg-black/30 p-4 rounded-lg grid grid-cols-2 gap-2 font-mono text-sm text-slate-300 mb-4">
                                <!-- Codes injected here -->
                            </div>

                            <div class="flex flex-wrap gap-4">
                                <button id="btn-regenerate-codes" class="px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg text-white text-sm font-medium transition-all">
                                    {{ __('Regenerate Recovery Codes') }}
                                </button>
                                <button id="btn-disable-2fa" class="px-4 py-2 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 rounded-lg text-red-400 text-sm font-medium transition-all">
                                    {{ __('Disable 2FA') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Password Confirmation Modal -->
                    <div id="password-confirm-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center">
                        <div class="bg-slate-900 border border-white/10 rounded-2xl p-8 max-w-md w-full shadow-2xl mx-4">
                            <h3 class="text-xl font-bold text-white mb-4" id="pwd-confirm-title">{{ __('Confirm Password') }}</h3>
                            <p class="text-slate-400 mb-6" id="pwd-confirm-desc">{{ __('For your security, please confirm your password to continue.') }}</p>
                            <form id="password-confirm-form" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-2">{{ __('Password') }}</label>
                                    <input type="password" id="confirm-action-password" required class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white focus:ring-2 focus:ring-violet-500">
                                </div>
                                <div class="flex space-x-3 justify-end">
                                    <button type="button" onclick="closePasswordModal()" class="px-4 py-2 text-slate-300 hover:text-white">{{ __('Cancel') }}</button>
                                    <button type="submit" class="px-6 py-2 bg-violet-600 hover:bg-violet-700 rounded-lg text-white font-semibold">{{ __('Confirm') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching
        function switchTab(tab) {
            // Hide all content
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-button').forEach(el => el.classList.remove('active'));
            
            // Show selected content
            document.getElementById('content-' + tab).classList.remove('hidden');
            document.getElementById('tab-' + tab).classList.add('active');
        }

        // Notification System
        function showNotification(message, type = 'success', containerId = '2fa-notification-container') {
            const container = document.getElementById(containerId);
            if (!container) return;

            const bgColor = type === 'success' ? 'bg-emerald-500/10' : 'bg-red-500/10';
            const borderColor = type === 'success' ? 'border-emerald-500/20' : 'border-red-500/20';
            const textColor = type === 'success' ? 'text-emerald-300' : 'text-red-300';
            const iconColor = type === 'success' ? 'text-emerald-400' : 'text-red-400';
            
            container.className = `mb-6 p-4 rounded-lg border ${bgColor} ${borderColor} flex items-center justify-between`;
            container.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 ${iconColor} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${type === 'success' 
                            ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                            : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'}
                    </svg>
                    <p class="text-sm ${textColor}">${message}</p>
                </div>
                <button onclick="this.parentElement.classList.add('hidden')" class="text-slate-400 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            `;
            container.classList.remove('hidden');

            if (type === 'success') {
                setTimeout(() => {
                    container.classList.add('hidden');
                }, 5000);
            }
        }

        // Load user profile data
        document.addEventListener('DOMContentLoaded', function() {
            const userData = localStorage.getItem('user_data');
            if (userData) {
                try {
                    const user = JSON.parse(userData);
                    document.getElementById('first_name').value = user.first_name || '';
                    document.getElementById('last_name').value = user.last_name || '';
                    document.getElementById('email').value = user.email || '';
                    document.getElementById('phone').value = user.phone || '';
                    document.getElementById('city').value = user.city || '';
                    document.getElementById('country').value = user.country || '';
                    document.getElementById('bio').value = user.bio || '';
                } catch (e) {
                    console.error('Error loading user data:', e);
                }
            }
        });

        // Profile form submission
        document.getElementById('profile-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitButton = document.getElementById('profile-submit');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('/settings/profile', {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    if (data.user) {
                        localStorage.setItem('user_data', JSON.stringify(data.user));
                    }
                    document.getElementById('profile-success').classList.remove('hidden');
                    setTimeout(() => {
                        document.getElementById('profile-success').classList.add('hidden');
                    }, 5000);
                } else {
                    showNotification(data.message || 'Failed to update profile', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }
        });

        // Password form submission
        document.getElementById('password-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitButton = document.getElementById('password-submit');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('/settings/password', {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    document.getElementById('password-success').classList.remove('hidden');
                    e.target.reset();
                    setTimeout(() => {
                        document.getElementById('password-success').classList.add('hidden');
                    }, 5000);
                } else {
                    showNotification(data.message || 'Failed to update password', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }
        });

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

        // 2FA Logic
        let pendingAction = null;

        function update2FAUI(enabled) {
            const statusTitle = document.getElementById('2fa-status-title');
            const statusDesc = document.getElementById('2fa-status-desc');
            const statusIcon = document.getElementById('2fa-status-icon');
            
            if (enabled) {
                statusTitle.textContent = '{{ __('2FA is currently enabled') }}';
                statusDesc.textContent = '{{ __('Your account is secured with two-factor authentication.') }}';
                statusIcon.className = 'w-10 h-10 rounded-full flex items-center justify-center mr-4 bg-emerald-500/10 text-emerald-400';
                
                document.getElementById('2fa-enable-section').classList.add('hidden');
                document.getElementById('2fa-setup-section').classList.add('hidden');
                document.getElementById('2fa-manage-section').classList.remove('hidden');
            } else {
                statusTitle.textContent = '{{ __('2FA is currently disabled') }}';
                statusDesc.textContent = '{{ __('Enable two-factor authentication to add an extra layer of security to your account.') }}';
                statusIcon.className = 'w-10 h-10 rounded-full flex items-center justify-center mr-4 bg-slate-700 text-slate-400';
                
                document.getElementById('2fa-enable-section').classList.remove('hidden');
                document.getElementById('2fa-setup-section').classList.add('hidden');
                document.getElementById('2fa-manage-section').classList.add('hidden');
            }
        }

        // Check initial status
        document.addEventListener('DOMContentLoaded', function() {
            const userData = localStorage.getItem('user_data');
            if (userData) {
                const user = JSON.parse(userData);
                update2FAUI(user.two_factor_enabled);
            }
        });

        // Enable 2FA Click
        document.getElementById('btn-enable-2fa').addEventListener('click', async function() {
            console.log('Enable 2FA button clicked');
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                console.log('CSRF Token:', csrfToken ? csrfToken.content : 'NOT FOUND');
                
                const response = await fetch('/settings/2fa/enable', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin'
                });
                
                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Response data:', data);
                
                if (response.ok) {
                    // Show setup section
                    document.getElementById('2fa-enable-section').classList.add('hidden');
                    document.getElementById('2fa-setup-section').classList.remove('hidden');
                    
                    // Display QR Code
                    document.getElementById('qr-code-container').innerHTML = data.qr_code_svg;
                    document.getElementById('setup-key').textContent = data.secret;
                    showNotification(data.message || 'Please scan the QR code with your authenticator app', 'success');
                } else {
                    showNotification(data.error || 'Failed to enable 2FA', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred: ' + error.message, 'error');
            }
        });

        // Cancel Setup
        document.getElementById('btn-cancel-setup').addEventListener('click', function() {
            document.getElementById('2fa-setup-section').classList.add('hidden');
            document.getElementById('2fa-enable-section').classList.remove('hidden');
        });

        // Confirm 2FA
        document.getElementById('2fa-confirm-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const code = document.getElementById('confirm-code').value;
            
            try {
                const response = await fetch('/settings/2fa/confirm', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ code: code })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Update local storage
                    const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
                    userData.two_factor_enabled = true;
                    localStorage.setItem('user_data', JSON.stringify(userData));
                    
                    update2FAUI(true);
                    
                    // Show recovery codes
                    if (data.recovery_codes) {
                        const container = document.getElementById('recovery-codes-container');
                        container.innerHTML = data.recovery_codes.map(code => `<div>${code}</div>`).join('');
                        showNotification('{{ __('Two-factor authentication enabled! Please save your recovery codes.') }}', 'success');
                    }
                } else {
                    showNotification(data.error || 'Invalid code', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            }
        });

        // Disable 2FA Click
        document.getElementById('btn-disable-2fa').addEventListener('click', function() {
            pendingAction = 'disable';
            document.getElementById('pwd-confirm-title').textContent = '{{ __('Disable Two-Factor Authentication') }}';
            document.getElementById('pwd-confirm-desc').textContent = '{{ __('Please confirm your password to disable two-factor authentication.') }}';
            document.getElementById('password-confirm-modal').classList.remove('hidden');
        });

        // Regenerate Codes Click
        document.getElementById('btn-regenerate-codes').addEventListener('click', function() {
            pendingAction = 'regenerate';
            document.getElementById('pwd-confirm-title').textContent = '{{ __('Regenerate Recovery Codes') }}';
            document.getElementById('pwd-confirm-desc').textContent = '{{ __('Please confirm your password to regenerate recovery codes.') }}';
            document.getElementById('password-confirm-modal').classList.remove('hidden');
        });

        function closePasswordModal() {
            document.getElementById('password-confirm-modal').classList.add('hidden');
            document.getElementById('confirm-action-password').value = '';
            pendingAction = null;
        }

        // Password Confirmation Submit
        document.getElementById('password-confirm-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const password = document.getElementById('confirm-action-password').value;
            
            if (!pendingAction) return;

            const currentAction = pendingAction; // Capture current action
            const endpoint = currentAction === 'disable' ? '/settings/2fa/disable' : '/settings/2fa/recovery-codes';
            
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ password: password })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    closePasswordModal();
                    
                    if (currentAction === 'disable') {
                        const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
                        userData.two_factor_enabled = false;
                        localStorage.setItem('user_data', JSON.stringify(userData));
                        update2FAUI(false);
                        showNotification('{{ __('Two-factor authentication disabled.') }}', 'success');
                    } else {
                        // Show new recovery codes
                        if (data.recovery_codes) {
                            const container = document.getElementById('recovery-codes-container');
                            container.innerHTML = data.recovery_codes.map(code => `<div>${code}</div>`).join('');
                            showNotification('{{ __('Recovery codes regenerated.') }}', 'success');
                        }
                    }
                } else {
                    showNotification(data.error || 'Action failed', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            }
        });

        function copyToClipboard(elementId) {
            const text = document.getElementById(elementId).textContent;
            navigator.clipboard.writeText(text).then(() => {
                showNotification('{{ __('Copied to clipboard!') }}', 'success');
            });
        }
    </script>

    <style>
        .tab-button.active {
            background: linear-gradient(to right, rgb(139 92 246), rgb(217 70 239));
            color: white;
            box-shadow: 0 10px 15px -3px rgb(139 92 246 / 0.3);
        }
        
        .tab-button:not(.active) {
            color: rgb(148 163 184);
        }
        
        .tab-button:not(.active):hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }
    </style>

</body>
</html>
