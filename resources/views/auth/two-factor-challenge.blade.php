<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Authentication - {{ config('app.name', 'LoanFlow') }}</title>
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

        @keyframes spin-slow {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
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

        .spin-slow {
            animation: spin-slow 20s linear infinite;
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

        /* Code input styling */
        .code-input {
            letter-spacing: 0.5em;
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
        }

        [x-cloak] { display: none !important; }
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

    <main class="pt-32 pb-20 px-6 lg:px-8 relative z-10" x-data="{ recovery: false }">
        <div class="max-w-5xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Column - Information -->
                <div class="animate-slide-up hidden lg:block">
                    <div class="inline-flex items-center px-4 py-2 bg-indigo-100 rounded-full mb-6">
                        <div class="w-2 h-2 bg-indigo-500 rounded-full mr-2 spin-slow" aria-hidden="true"></div>
                        <span class="text-sm font-semibold text-indigo-900">Extra Security Layer</span>
                    </div>

                    <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                        Two-Factor
                        <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Authentication</span>
                    </h1>

                    <p class="text-xl text-gray-600 mb-8 leading-relaxed" x-show="!recovery">
                        Enter the 6-digit code from your authenticator app to securely access your account.
                    </p>

                    <p class="text-xl text-gray-600 mb-8 leading-relaxed" x-cloak x-show="recovery">
                        Enter one of your emergency recovery codes to access your account.
                    </p>

                    <!-- Authentication App Info -->
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-8 border border-indigo-100 mb-6" x-show="!recovery">
                        <h3 class="font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            How to use your authenticator:
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-sm mr-4">
                                    1
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Open your app</h4>
                                    <p class="text-sm text-gray-600">Launch your authenticator application</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-sm mr-4">
                                    2
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Find LoanFlow</h4>
                                    <p class="text-sm text-gray-600">Look for the LoanFlow account entry</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-sm mr-4">
                                    3
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Enter the code</h4>
                                    <p class="text-sm text-gray-600">Type the 6-digit code displayed</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recovery Code Info -->
                    <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl p-8 border border-yellow-100 mb-6" x-cloak x-show="recovery">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-6 h-6 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Using a recovery code
                        </h3>
                        <p class="text-sm text-gray-700 mb-4">
                            Recovery codes are single-use backup codes that allow you to access your account when you don't have access to your authenticator app.
                        </p>
                        <div class="bg-yellow-100 rounded-lg p-3 text-xs text-yellow-800">
                            <strong>Important:</strong> Each recovery code can only be used once. Generate new codes after using them.
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="flex items-start bg-white rounded-xl p-4 shadow-md border border-gray-200">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-1">Your account is protected</h4>
                            <p class="text-sm text-gray-600">Two-factor authentication adds an extra layer of security to your account.</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column - 2FA Form -->
                <div class="animate-slide-up" style="animation-delay: 0.2s;">
                    <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-10 card-hover">
                        <div class="mb-8 text-center">
                            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full mb-4">
                                <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" x-show="!recovery">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" x-cloak x-show="recovery">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 mb-2" x-show="!recovery">Enter Authentication Code</h2>
                            <h2 class="text-3xl font-bold text-gray-900 mb-2" x-cloak x-show="recovery">Enter Recovery Code</h2>
                            <p class="text-gray-600" x-show="!recovery">From your authenticator app</p>
                            <p class="text-gray-600" x-cloak x-show="recovery">Use one of your backup codes</p>
                        </div>

                        <!-- Instructions -->
                        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4" x-show="!recovery">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div class="text-sm text-blue-900">
                                    <p class="font-semibold mb-1">Open your authenticator app</p>
                                    <p>Enter the 6-digit code shown for your LoanFlow account. The code refreshes every 30 seconds.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4" x-cloak x-show="recovery">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div class="text-sm text-yellow-900">
                                    <p class="font-semibold mb-1">One-time use only</p>
                                    <p>Each recovery code can only be used once. Make sure to generate new codes after logging in.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Validation Errors -->
                        @if ($errors->any())
                            <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg p-4" role="alert" aria-live="polite">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <h3 class="text-sm font-semibold text-red-800 mb-2">Authentication failed:</h3>
                                        <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-6">
                            @csrf

                            <!-- Authentication Code Field -->
                            <div x-show="!recovery">
                                <label for="code" class="block text-sm font-semibold text-gray-700 mb-2">
                                    6-Digit Code
                                    <span class="text-red-500" aria-label="required">*</span>
                                </label>
                                <input
                                    id="code"
                                    type="text"
                                    inputmode="numeric"
                                    name="code"
                                    x-ref="code"
                                    autofocus
                                    autocomplete="one-time-code"
                                    maxlength="6"
                                    pattern="[0-9]{6}"
                                    aria-required="true"
                                    aria-describedby="code-hint"
                                    class="code-input block w-full px-4 py-4 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                    placeholder="000000"
                                >
                                <p id="code-hint" class="mt-2 text-xs text-gray-500 text-center">Enter the code from your authenticator app</p>
                            </div>

                            <!-- Recovery Code Field -->
                            <div x-cloak x-show="recovery">
                                <label for="recovery_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Recovery Code
                                    <span class="text-red-500" aria-label="required">*</span>
                                </label>
                                <input
                                    id="recovery_code"
                                    type="text"
                                    name="recovery_code"
                                    x-ref="recovery_code"
                                    autocomplete="one-time-code"
                                    aria-required="true"
                                    aria-describedby="recovery-hint"
                                    class="block w-full px-4 py-3.5 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition font-mono"
                                    placeholder="xxxx-xxxx-xxxx"
                                >
                                <p id="recovery-hint" class="mt-2 text-xs text-gray-500">Enter one of your backup recovery codes</p>
                            </div>

                            <!-- Submit Button -->
                            <button
                                type="submit"
                                class="w-full py-4 px-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 pulse-glow"
                            >
                                <span class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    Verify & Log In
                                </span>
                            </button>

                            <!-- Toggle between code types -->
                            <div class="text-center">
                                <button
                                    type="button"
                                    x-show="!recovery"
                                    @click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() })"
                                    class="text-sm text-indigo-600 hover:text-indigo-700 font-medium underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 py-1"
                                >
                                    Use a recovery code instead
                                </button>

                                <button
                                    type="button"
                                    x-cloak
                                    x-show="recovery"
                                    @click="recovery = false; $nextTick(() => { $refs.code.focus() })"
                                    class="text-sm text-indigo-600 hover:text-indigo-700 font-medium underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 py-1"
                                >
                                    Use an authentication code instead
                                </button>
                            </div>
                        </form>

                        <!-- Additional Help -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="text-center text-sm text-gray-600">
                                <p class="mb-2">Having trouble?</p>
                                <a href="mailto:support@loanflow.com" class="text-indigo-600 hover:text-indigo-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 py-1">
                                    Contact Support
                                </a>
                            </div>
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
</body>
</html>
