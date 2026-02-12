<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - {{ config('app.name', 'LoanFlow') }}</title>
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
                        Back to Login
                    </a>
                @endif
            </div>
        </div>
    </nav>

    <main class="pt-32 pb-20 px-6 lg:px-8 relative z-10">
        <div class="max-w-5xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Column - Information & Illustration -->
                <div class="animate-slide-up hidden lg:block">
                    <div class="inline-flex items-center px-4 py-2 bg-blue-100 rounded-full mb-6">
                        <svg class="w-4 h-4 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-semibold text-blue-900">Password Recovery</span>
                    </div>

                    <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                        Reset Your
                        <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Password</span>
                    </h1>

                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        No worries! It happens to the best of us. Enter your email address and we'll send you a link to reset your password.
                    </p>

                    <!-- Illustration/Steps -->
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-8 border border-indigo-100">
                        <h3 class="font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            How it works:
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-sm mr-4">
                                    1
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Enter your email</h4>
                                    <p class="text-sm text-gray-600">Provide the email address associated with your account</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-sm mr-4">
                                    2
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Check your inbox</h4>
                                    <p class="text-sm text-gray-600">We'll send a secure reset link to your email</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-sm mr-4">
                                    3
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Create new password</h4>
                                    <p class="text-sm text-gray-600">Click the link and set up your new password</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="mt-6 flex items-start bg-white rounded-xl p-4 shadow-md border border-gray-200">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-1">Secure & Private</h4>
                            <p class="text-sm text-gray-600">The reset link expires in 60 minutes for your security. We'll never share your information.</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Reset Form -->
                <div class="animate-slide-up" style="animation-delay: 0.2s;">
                    <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-10 card-hover">
                        <div class="mb-8 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full mb-4">
                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 mb-2">Forgot Password?</h2>
                            <p class="text-gray-600">We'll help you get back into your account</p>
                        </div>

                        <!-- Status Message -->
                        @session('status')
                            <div class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-lg p-4" role="alert" aria-live="polite">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <h3 class="text-sm font-semibold text-green-800 mb-1">Email Sent!</h3>
                                        <p class="text-sm text-green-700">{{ $value }}</p>
                                    </div>
                                </div>
                            </div>
                        @endsession

                        <!-- Validation Errors -->
                        @if ($errors->any())
                            <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg p-4" role="alert" aria-live="polite">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <h3 class="text-sm font-semibold text-red-800 mb-2">Unable to send reset link:</h3>
                                        <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                            @csrf

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
                                        autofocus
                                        autocomplete="username"
                                        aria-required="true"
                                        aria-describedby="email-hint"
                                        class="block w-full pl-12 pr-4 py-3.5 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                        placeholder="you@example.com"
                                    >
                                </div>
                                <p id="email-hint" class="mt-2 text-xs text-gray-500">Enter the email address you used to register</p>
                            </div>

                            <!-- Submit Button -->
                            <button
                                type="submit"
                                class="w-full py-4 px-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 pulse-glow"
                            >
                                <span class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Send Reset Link
                                </span>
                            </button>
                        </form>

                        <!-- Divider -->
                        <div class="relative my-8">
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div class="w-full border-t border-gray-200"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-4 bg-white text-gray-500">Remember your password?</span>
                            </div>
                        </div>

                        <!-- Back to Login -->
                        <div class="text-center">
                            <a href="{{ route('login') }}" class="inline-flex items-center px-8 py-3.5 bg-white text-gray-900 rounded-xl font-semibold hover:shadow-lg transition border-2 border-gray-200 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                Back to Sign In
                            </a>
                        </div>
                    </div>

                    <!-- Additional Help -->
                    <div class="mt-6 text-center">
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm">
                            <p class="text-blue-900 mb-2">
                                <strong>Didn't receive the email?</strong>
                            </p>
                            <p class="text-blue-800">
                                Check your spam folder or
                                <a href="mailto:support@loanflow.com" class="text-indigo-600 hover:text-indigo-700 font-medium underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded">contact support</a>
                            </p>
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
