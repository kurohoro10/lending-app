<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - {{ config('app.name', 'LoanFlow') }}</title>
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
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="px-5 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-full font-medium hover:shadow-lg transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Sign In
                    </a>
                @endif
            </div>
        </div>
    </nav>

    <main class="pt-32 pb-20 px-6 lg:px-8 relative z-10">
        <div class="max-w-6xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Column - Branding & Information -->
                <div class="animate-slide-up hidden lg:block">
                    <div class="inline-flex items-center px-4 py-2 bg-green-100 rounded-full mb-6">
                        <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-semibold text-green-900">Quick & Easy Registration</span>
                    </div>

                    <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                        Start Your
                        <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Funding Journey</span>
                    </h1>

                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        Create your account in minutes and get access to flexible business loans with competitive rates and fast approval.
                    </p>

                    <!-- Benefits List -->
                    <div class="space-y-4 pt-6 border-t border-gray-200">
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Fast Approval Process</h3>
                                <p class="text-sm text-gray-600">Get a decision within 24-48 hours</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Flexible Loan Amounts</h3>
                                <p class="text-sm text-gray-600">From $10,000 to $1,000,000</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Secure & Confidential</h3>
                                <p class="text-sm text-gray-600">Bank-level encryption protects your data</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-pink-600 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Dedicated Support Team</h3>
                                <p class="text-sm text-gray-600">Expert guidance throughout the process</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Proof -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="flex -space-x-2">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 border-2 border-white"></div>
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 border-2 border-white"></div>
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-pink-400 to-pink-600 border-2 border-white"></div>
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 border-2 border-white flex items-center justify-center text-white text-xs font-bold">+5K</div>
                            </div>
                            <div class="flex text-yellow-400">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="ml-1 text-gray-900 font-semibold">4.9/5</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600">Join 10,000+ businesses that trust LoanFlow</p>
                    </div>
                </div>

                <!-- Right Column - Registration Form -->
                <div class="animate-slide-up" style="animation-delay: 0.2s;">
                    <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-10 card-hover">
                        <div class="mb-8">
                            <h2 class="text-3xl font-bold text-gray-900 mb-2">Create Account</h2>
                            <p class="text-gray-600">Get started with your free account today</p>
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

                        <form method="POST" action="{{ route('register') }}" class="space-y-5">
                            @csrf

                            <!-- Name Field -->
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Full Name
                                    <span class="text-red-500" aria-label="required">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <input
                                        id="name"
                                        type="text"
                                        name="name"
                                        value="{{ old('name') }}"
                                        required
                                        autofocus
                                        autocomplete="name"
                                        aria-required="true"
                                        class="block w-full pl-12 pr-4 py-3.5 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                        placeholder="John Doe"
                                    >
                                </div>
                            </div>

                            <!-- Email Field -->
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
                                        value="{{ old('email') }}"
                                        required
                                        autocomplete="username"
                                        aria-required="true"
                                        aria-describedby="email-hint"
                                        class="block w-full pl-12 pr-4 py-3.5 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                        placeholder="you@example.com"
                                    >
                                </div>
                                <p id="email-hint" class="mt-1 text-xs text-gray-500">We'll use this for account notifications</p>
                            </div>

                            <!-- Password Field -->
                            <div>
                                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Password
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
                                        aria-describedby="password-hint"
                                        class="block w-full pl-12 pr-4 py-3.5 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                        placeholder="Minimum 8 characters"
                                    >
                                </div>
                                <p id="password-hint" class="mt-1 text-xs text-gray-500">Use at least 8 characters with letters and numbers</p>
                            </div>

                            <!-- Password Confirmation Field -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Confirm Password
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
                                        placeholder="Re-enter your password"
                                    >
                                </div>
                            </div>

                            <!-- Terms and Privacy Policy -->
                            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                                    <label for="terms" class="flex items-start cursor-pointer group">
                                        <input
                                            id="terms"
                                            type="checkbox"
                                            name="terms"
                                            required
                                            aria-required="true"
                                            class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 transition cursor-pointer flex-shrink-0"
                                        >
                                        <span class="ml-3 text-sm text-gray-700 group-hover:text-gray-900">
                                            I agree to the
                                            <a href="{{ route('terms.show') }}" target="_blank" class="text-indigo-600 hover:text-indigo-700 font-medium underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded">Terms of Service</a>
                                            and
                                            <a href="{{ route('policy.show') }}" target="_blank" class="text-indigo-600 hover:text-indigo-700 font-medium underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded">Privacy Policy</a>
                                            <span class="text-red-500" aria-label="required">*</span>
                                        </span>
                                    </label>
                                </div>
                            @endif

                            <!-- Submit Button -->
                            <button
                                type="submit"
                                class="w-full py-4 px-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 pulse-glow"
                            >
                                Create Account
                                <span class="sr-only">and start your application</span>
                            </button>

                            <!-- Login Link -->
                            <div class="relative my-6">
                                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                    <div class="w-full border-t border-gray-200"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="px-4 bg-white text-gray-500">Already have an account?</span>
                                </div>
                            </div>

                            <div class="text-center">
                                <a href="{{ route('login') }}" class="inline-flex items-center px-8 py-3.5 bg-white text-gray-900 rounded-xl font-semibold hover:shadow-lg transition border-2 border-gray-200 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    Sign In Instead
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Additional Help -->
                    <div class="mt-6 text-center text-sm text-gray-600">
                        <p>Questions? <a href="mailto:support@loanflow.com" class="text-indigo-600 hover:text-indigo-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-1">Contact our team</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 mt-16 pb-8 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="pt-8 border-t border-gray-200 text-center">
                <div class="flex flex-col md:flex-row justify-between items-center text-gray-600 text-sm">
                    <p class="mb-4 md:mb-0">&copy; {{ date('Y') }} LoanFlow. All rights reserved.</p>
                    <div class="flex items-center space-x-6">
                        <a href="{{ route('privacy-policy') }}" class="hover:text-indigo-600 transition focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 py-1">Privacy Policy</a>
                        <a href="{{ route('terms-and-conditions') }}" class="hover:text-indigo-600 transition focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 py-1">Terms of Service</a>
                        <a href="#" class="hover:text-indigo-600 transition focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 py-1">Help Center</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
