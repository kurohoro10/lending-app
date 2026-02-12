<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create New Password - {{ config('app.name', 'LoanFlow') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Poppins', sans-serif; }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
            }
            50% {
                box-shadow: 0 0 40px rgba(99, 102, 241, 0.6);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .animate-slide-up {
            animation: slideInUp 0.6s ease-out forwards;
        }

        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        /* Decorative background elements */
        .bg-decoration {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.6;
            pointer-events: none;
        }

        /* Custom focus styles for accessibility */
        input:focus, button:focus, a:focus {
            outline: 2px solid #6366F1;
            outline-offset: 2px;
        }

        /* Screen reader only class for accessibility */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }

        /* Password strength indicator */
        .password-strength {
            height: 4px;
            border-radius: 2px;
            background: #E5E7EB;
            overflow: hidden;
            margin-top: 8px;
        }

        .password-strength-bar {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { width: 33%; background: #EF4444; }
        .strength-medium { width: 66%; background: #F59E0B; }
        .strength-strong { width: 100%; background: #10B981; }
    </style>
</head>
<body class="antialiased bg-gradient-to-br from-gray-50 to-indigo-50 min-h-screen">
    <!-- Decorative Background Elements -->
    <div class="bg-decoration" style="top: 10%; left: 10%; width: 400px; height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
    <div class="bg-decoration float-animation" style="top: 60%; right: 10%; width: 300px; height: 300px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
    <div class="bg-decoration float-animation" style="bottom: 10%; left: 30%; width: 250px; height: 250px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); animation-delay: 2s;"></div>

    <!-- Floating Navigation -->
    <nav class="fixed top-6 left-1/2 transform -translate-x-1/2 z-50 px-6 py-3 bg-white/90 backdrop-blur-lg rounded-full shadow-xl border border-gray-200 max-w-4xl w-full mx-4" role="navigation" aria-label="Main navigation">
        <div class="flex justify-between items-center">
            <a href="{{ route('welcome') }}" class="flex items-center focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-full" aria-label="LoanFlow Home">
                <div class="h-10 w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mr-3">
                    <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                    </svg>
                </div>
                <span class="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">LoanFlow</span>
            </a>

            <div class="flex items-center space-x-3">
                <a href="{{ route('welcome') }}" class="px-5 py-2 text-gray-700 hover:text-indigo-600 font-medium transition focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg">
                    Home
                </a>
            </div>
        </div>
    </nav>

    <main class="pt-32 pb-20 px-6 lg:px-8 relative z-10">
        <div class="max-w-5xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Column - Information -->
                <div class="animate-slide-up hidden lg:block">
                    <div class="inline-flex items-center px-4 py-2 bg-green-100 rounded-full mb-6">
                        <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-semibold text-green-900">Secure Reset</span>
                    </div>

                    <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                        Create a New
                        <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Password</span>
                    </h1>

                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        Choose a strong, unique password to secure your LoanFlow account. Make sure it's something you'll remember!
                    </p>

                    <!-- Password Tips -->
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-8 border border-indigo-100">
                        <h3 class="font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Password Requirements:
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-gray-700">At least 8 characters long</span>
                            </div>

                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-gray-700">Mix of uppercase and lowercase letters</span>
                            </div>

                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-gray-700">Include numbers and symbols</span>
                            </div>

                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-gray-700">Avoid common words or patterns</span>
                            </div>
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="mt-6 bg-white rounded-xl p-4 shadow-md border border-gray-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1 text-sm">Security Tip</h4>
                                <p class="text-xs text-gray-600">Never share your password with anyone. LoanFlow staff will never ask for your password.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Reset Password Form -->
                <div class="animate-slide-up" style="animation-delay: 0.2s;">
                    <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-10 card-hover">
                        <div class="mb-8 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-100 to-emerald-100 rounded-full mb-4">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 mb-2">Set New Password</h2>
                            <p class="text-gray-600">Enter your new password below</p>
                        </div>

                        <!-- Validation Errors -->
                        @if ($errors->any())
                            <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg p-4" role="alert" aria-live="polite">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <h3 class="text-sm font-semibold text-red-800 mb-2">Please fix the following errors:</h3>
                                        <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                            @csrf

                            <!-- Hidden Token -->
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <!-- Email Field (Read-only) -->
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email Address
                                    <span class="text-red-500" aria-label="required">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value="{{ old('email', $request->email) }}"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        aria-required="true"
                                        class="block w-full pl-12 pr-4 py-3.5 border border-gray-300 rounded-xl text-gray-900 bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                        readonly
                                    >
                                </div>
                            </div>

                            <!-- New Password Field -->
                            <div>
                                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                                    New Password
                                    <span class="text-red-500" aria-label="required">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                    <input
                                        id="password"
                                        type="password"
                                        name="password"
                                        required
                                        autocomplete="new-password"
                                        aria-required="true"
                                        aria-describedby="password-hint password-strength"
                                        class="block w-full pl-12 pr-4 py-3.5 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                        placeholder="Enter new password"
                                        onkeyup="checkPasswordStrength(this.value)"
                                    >
                                </div>
                                <div id="password-strength" class="password-strength" aria-live="polite">
                                    <div id="password-strength-bar" class="password-strength-bar"></div>
                                </div>
                                <p id="password-hint" class="mt-2 text-xs text-gray-500">
                                    <span id="strength-text">Strength: </span>
                                    <span id="strength-label" class="font-semibold">Enter password</span>
                                </p>
                            </div>

                            <!-- Confirm Password Field -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Confirm New Password
                                    <span class="text-red-500" aria-label="required">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <input
                                        id="password_confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        required
                                        autocomplete="new-password"
                                        aria-required="true"
                                        class="block w-full pl-12 pr-4 py-3.5 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                        placeholder="Re-enter new password"
                                    >
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button
                                type="submit"
                                class="w-full py-4 px-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 pulse-glow"
                            >
                                <span class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Reset Password
                                </span>
                            </button>
                        </form>

                        <!-- Divider -->
                        <div class="relative my-8">
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div class="w-full border-t border-gray-200"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-4 bg-white text-gray-500">Changed your mind?</span>
                            </div>
                        </div>

                        <!-- Back to Login -->
                        <div class="text-center">
                            <a href="{{ route('login') }}" class="inline-flex items-center px-8 py-3.5 bg-white text-gray-900 rounded-xl font-semibold hover:shadow-lg transition border-2 border-gray-200 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 mt-16 pb-8 px-6">
        <div class="max-w-5xl mx-auto">
            <div class="pt-8 border-t border-gray-200 text-center">
                <div class="flex flex-col md:flex-row justify-between items-center text-gray-600 text-sm">
                    <p class="mb-4 md:mb-0">&copy; {{ date('Y') }} LoanFlow. All rights reserved.</p>
                    <div class="flex items-center space-x-6">
                        <a href="#" class="hover:text-indigo-600 transition focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 py-1">Privacy Policy</a>
                        <a href="#" class="hover:text-indigo-600 transition focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 py-1">Terms of Service</a>
                        <a href="#" class="hover:text-indigo-600 transition focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 py-1">Help Center</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Password Strength JavaScript -->
    <script>
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthLabel = document.getElementById('strength-label');

            if (password.length === 0) {
                strengthBar.className = 'password-strength-bar';
                strengthLabel.textContent = 'Enter password';
                strengthLabel.className = 'font-semibold text-gray-500';
                return;
            }

            let strength = 0;

            // Length check
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;

            // Character variety checks
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            // Update UI based on strength
            if (strength <= 2) {
                strengthBar.className = 'password-strength-bar strength-weak';
                strengthLabel.textContent = 'Weak';
                strengthLabel.className = 'font-semibold text-red-600';
            } else if (strength <= 4) {
                strengthBar.className = 'password-strength-bar strength-medium';
                strengthLabel.textContent = 'Medium';
                strengthLabel.className = 'font-semibold text-yellow-600';
            } else {
                strengthBar.className = 'password-strength-bar strength-strong';
                strengthLabel.textContent = 'Strong';
                strengthLabel.className = 'font-semibold text-green-600';
            }
        }
    </script>
</body>
</html>
