<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Commercial Loan CRM') }}</title>
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

        .animate-slide-up {
            animation: slideInUp 0.6s ease-out forwards;
        }

        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        /* Custom Range Slider Styles with Progress Fill */
        .range-wrapper {
            position: relative;
            width: 100%;
        }

        .range-background {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 100%;
            height: 8px;
            background: #E5E7EB;
            border-radius: 4px;
            pointer-events: none;
        }

        .range-fill {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            height: 8px;
            background: linear-gradient(to right, #6366F1 0%, #8B5CF6 100%);
            border-radius: 4px;
            pointer-events: none;
            transition: width 0.1s ease;
        }

        input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            background: transparent;
            cursor: pointer;
            width: 100%;
            position: relative;
            z-index: 2;
        }

        input[type="range"]::-webkit-slider-track {
            background: transparent;
            height: 8px;
        }

        input[type="range"]::-moz-range-track {
            background: transparent;
            height: 8px;
        }

        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            height: 24px;
            width: 24px;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.5);
            border: 4px solid white;
            cursor: grab;
            transition: all 0.2s ease;
        }

        input[type="range"]::-moz-range-thumb {
            border: 4px solid white;
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            height: 24px;
            width: 24px;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.5);
            cursor: grab;
            transition: all 0.2s ease;
        }

        input[type="range"]::-webkit-slider-thumb:hover {
            transform: scale(1.15);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.6);
        }

        input[type="range"]::-moz-range-thumb:hover {
            transform: scale(1.15);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.6);
        }

        input[type="range"]::-webkit-slider-thumb:active {
            cursor: grabbing;
            transform: scale(1.05);
        }

        input[type="range"]::-moz-range-thumb:active {
            cursor: grabbing;
            transform: scale(1.05);
        }

        input[type="range"]:focus {
            outline: none;
        }

        input[type="range"]:focus::-webkit-slider-thumb {
            box-shadow: 0 0 0 6px rgba(99, 102, 241, 0.2), 0 4px 12px rgba(99, 102, 241, 0.5);
        }

        input[type="range"]:focus::-moz-range-thumb {
            box-shadow: 0 0 0 6px rgba(99, 102, 241, 0.2), 0 4px 12px rgba(99, 102, 241, 0.5);
        }

        .progress-step {
            position: relative;
        }

        .progress-step::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #E5E7EB;
            z-index: -1;
        }

        .progress-step:last-child::after {
            display: none;
        }
    </style>
</head>
<body class="antialiased bg-gradient-to-br from-gray-50 to-indigo-50">
    <!-- Floating Navigation -->
    <nav class="fixed top-6 left-1/2 transform -translate-x-1/2 z-50 px-6 py-3 bg-white/90 backdrop-blur-lg rounded-full shadow-xl border border-gray-200 max-w-4xl w-full mx-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <div class="h-10 w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mr-3">
                    <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                    </svg>
                </div>
                <span class="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">LoanFlow</span>
            </div>

            @if (Route::has('login'))
                <div class="flex items-center space-x-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2 text-gray-700 hover:text-indigo-600 font-medium transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2 text-gray-700 hover:text-indigo-600 font-medium transition">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-5 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-full font-medium hover:shadow-lg transition">
                                Apply Now
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </nav>

    <div class="pt-32 pb-20 px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Hero Section with Calculator -->
            <div class="grid lg:grid-cols-2 gap-12 items-center mb-20">
                <!-- Left Column - Hero Text -->
                <div class="animate-slide-up">
                    <div class="inline-flex items-center px-4 py-2 bg-indigo-100 rounded-full mb-6">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                        <span class="text-sm font-semibold text-indigo-900">Fast • Secure • Transparent</span>
                    </div>

                    <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                        Get Your Business
                        <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent"> Funded Fast</span>
                    </h1>

                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        Access capital in as little as 24 hours. Simple application, transparent terms, and personalized service every step of the way.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        @auth
                            <a href="{{ route('applications.index') }}" class="px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold hover:shadow-xl transition transform hover:scale-105">
                                View Applications →
                            </a>
                        @else
                            <a href="{{ route('applications.create') }}" class="px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold hover:shadow-xl transition transform hover:scale-105">
                                Start Application →
                            </a>
                            <a href="#calculator" class="px-8 py-4 bg-white text-gray-900 rounded-xl font-semibold hover:shadow-lg transition border-2 border-gray-200">
                                Calculate Loan
                            </a>
                        @endauth
                    </div>

                    <!-- Trust Badges -->
                    <div class="flex flex-wrap gap-6 pt-6 border-t border-gray-200">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">No Hidden Fees</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Bank-Level Security</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">24/7 Support</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Interactive Calculator -->
                <div id="calculator" class="bg-white rounded-3xl shadow-2xl p-8 card-hover animate-slide-up" style="animation-delay: 0.2s;">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-gray-900">Loan Calculator</h3>
                        <div class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold">
                            Instant Quote
                        </div>
                    </div>

                    <div class="space-y-8">
                        <!-- Loan Amount -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <label class="text-sm font-semibold text-gray-700">Loan Amount</label>
                                <span class="text-2xl font-bold text-indigo-600" id="loanAmount">$100,000</span>
                            </div>
                            <div class="py-2 range-wrapper">
                                <div class="range-background"></div>
                                <div class="range-fill" id="loanFill"></div>
                                <input type="range" min="10000" max="1000000" value="100000" step="10000" id="loanSlider" class="w-full">
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-2">
                                <span>$10K</span>
                                <span>$1M</span>
                            </div>
                        </div>

                        <!-- Loan Term -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <label class="text-sm font-semibold text-gray-700">Loan Term</label>
                                <span class="text-xl font-bold text-indigo-600" id="loanTerm">36 months</span>
                            </div>
                            <div class="py-2 range-wrapper">
                                <div class="range-background"></div>
                                <div class="range-fill" id="termFill"></div>
                                <input type="range" min="12" max="84" value="36" step="12" id="termSlider" class="w-full">
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-2">
                                <span>12 months</span>
                                <span>84 months</span>
                            </div>
                        </div>

                        <!-- Interest Rate -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <label class="text-sm font-semibold text-gray-700">Interest Rate</label>
                                <span class="text-xl font-bold text-indigo-600" id="interestRate">8.5%</span>
                            </div>
                            <div class="py-2 range-wrapper">
                                <div class="range-background"></div>
                                <div class="range-fill" id="rateFill"></div>
                                <input type="range" min="5" max="15" value="8.5" step="0.5" id="rateSlider" class="w-full">
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-2">
                                <span>5%</span>
                                <span>15%</span>
                            </div>
                        </div>

                        <!-- Results -->
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-6 mt-6">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-gray-700 font-medium">Monthly Payment</span>
                                <span class="text-3xl font-bold text-indigo-600" id="monthlyPayment">$3,133</span>
                            </div>
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-gray-700 font-medium">Total Interest</span>
                                <span class="text-xl font-semibold text-gray-900" id="totalInterest">$12,788</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700 font-medium">Total Repayment</span>
                                <span class="text-xl font-semibold text-gray-900" id="totalRepayment">$112,788</span>
                            </div>
                        </div>

                        <button onclick="applyWithCalculatorValues()" class="w-full py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-105">
                            Apply for This Loan
                        </button>

                        <p class="text-xs text-gray-500 text-center">
                            * Rates shown are illustrative. Actual rates may vary based on creditworthiness.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Application Process Steps -->
            <div class="mb-20">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">How It Works</h2>
                    <p class="text-xl text-gray-600">Three simple steps to funding</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <div class="progress-step text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-full text-2xl font-bold mb-4 pulse-glow">
                            1
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Apply Online</h3>
                        <p class="text-gray-600">Fill out our simple form in 10 minutes or less</p>
                    </div>

                    <div class="progress-step text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-full text-2xl font-bold mb-4">
                            2
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Get Approved</h3>
                        <p class="text-gray-600">Receive a decision within 24-48 hours</p>
                    </div>

                    <div class="progress-step text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-full text-2xl font-bold mb-4">
                            3
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Receive Funds</h3>
                        <p class="text-gray-600">Money deposited directly to your account</p>
                    </div>
                </div>
            </div>

            <!-- Feature Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-20">
                <div class="bg-white rounded-2xl p-6 shadow-lg card-hover">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Secure Platform</h3>
                    <p class="text-sm text-gray-600">256-bit encryption protects your data</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-lg card-hover">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Fast Approval</h3>
                    <p class="text-sm text-gray-600">Get funded in as little as 24 hours</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-lg card-hover">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Real-Time Alerts</h3>
                    <p class="text-sm text-gray-600">Email & SMS notifications</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-lg card-hover">
                    <div class="w-14 h-14 bg-gradient-to-br from-pink-400 to-pink-600 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Expert Support</h3>
                    <p class="text-sm text-gray-600">Dedicated loan specialists</p>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-3xl p-12 mb-20 shadow-2xl">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center text-white">
                    <div>
                        <div class="text-5xl font-bold mb-2">$2B+</div>
                        <div class="text-indigo-100">Total Funded</div>
                    </div>
                    <div>
                        <div class="text-5xl font-bold mb-2">10K+</div>
                        <div class="text-indigo-100">Happy Clients</div>
                    </div>
                    <div>
                        <div class="text-5xl font-bold mb-2">24h</div>
                        <div class="text-indigo-100">Avg. Approval</div>
                    </div>
                    <div>
                        <div class="text-5xl font-bold mb-2">4.9★</div>
                        <div class="text-indigo-100">Customer Rating</div>
                    </div>
                </div>
            </div>

            <!-- Final CTA -->
            <div class="text-center bg-white rounded-3xl p-12 shadow-xl">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Ready to Grow Your Business?</h2>
                <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                    Join thousands of businesses that have secured funding through our platform
                </p>

                @auth
                    <a href="{{ route('applications.index') }}" class="inline-flex items-center px-10 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold text-lg hover:shadow-2xl transition transform hover:scale-105">
                        View Your Applications
                        <svg class="w-6 h-6 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('applications.create') }}" class="inline-flex items-center px-10 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold text-lg hover:shadow-2xl transition transform hover:scale-105">
                        Start Your Application Now
                        <svg class="w-6 h-6 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                @endauth

                <p class="mt-6 text-sm text-gray-500">
                    ✓ No application fee  ✓ No prepayment penalty  ✓ Flexible terms
                </p>
            </div>

            <!-- Footer -->
            <div class="mt-16 pt-8 border-t border-gray-200 text-center">
                <div class="flex flex-col md:flex-row justify-between items-center text-gray-600">
                    <p class="mb-4 md:mb-0">Commercial Loan CRM v1.0 - Powered by Innovation</p>
                    <p class="text-sm">Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Calculator JavaScript -->
    <script>
        // Loan Calculator Logic
        const loanSlider = document.getElementById('loanSlider');
        const termSlider = document.getElementById('termSlider');
        const rateSlider = document.getElementById('rateSlider');

        const loanFill = document.getElementById('loanFill');
        const termFill = document.getElementById('termFill');
        const rateFill = document.getElementById('rateFill');

        const loanAmountDisplay = document.getElementById('loanAmount');
        const loanTermDisplay = document.getElementById('loanTerm');
        const interestRateDisplay = document.getElementById('interestRate');
        const monthlyPaymentDisplay = document.getElementById('monthlyPayment');
        const totalInterestDisplay = document.getElementById('totalInterest');
        const totalRepaymentDisplay = document.getElementById('totalRepayment');

        function formatCurrency(amount) {
            return '$' + Math.round(amount).toLocaleString();
        }

        function updateSliderFill(slider, fill) {
            const min = parseFloat(slider.min);
            const max = parseFloat(slider.max);
            const value = parseFloat(slider.value);
            const percentage = ((value - min) / (max - min)) * 100;
            fill.style.width = percentage + '%';
        }

        function calculateLoan() {
            const principal = parseFloat(loanSlider.value);
            const termMonths = parseInt(termSlider.value);
            const annualRate = parseFloat(rateSlider.value);
            const monthlyRate = annualRate / 100 / 12;

            // Calculate monthly payment using loan payment formula
            const monthlyPayment = principal * (monthlyRate * Math.pow(1 + monthlyRate, termMonths)) / (Math.pow(1 + monthlyRate, termMonths) - 1);
            const totalRepayment = monthlyPayment * termMonths;
            const totalInterest = totalRepayment - principal;

            // Update displays
            loanAmountDisplay.textContent = formatCurrency(principal);
            loanTermDisplay.textContent = termMonths + ' months';
            interestRateDisplay.textContent = annualRate + '%';
            monthlyPaymentDisplay.textContent = formatCurrency(monthlyPayment);
            totalInterestDisplay.textContent = formatCurrency(totalInterest);
            totalRepaymentDisplay.textContent = formatCurrency(totalRepayment);

            // Update slider fills
            updateSliderFill(loanSlider, loanFill);
            updateSliderFill(termSlider, termFill);
            updateSliderFill(rateSlider, rateFill);
        }

        // Event listeners
        loanSlider.addEventListener('input', calculateLoan);
        termSlider.addEventListener('input', calculateLoan);
        rateSlider.addEventListener('input', calculateLoan);

        // Initial calculation
        calculateLoan();

        // Apply with calculator values
        function applyWithCalculatorValues() {
            const amount = document.getElementById('loanSlider').value;
            const term = document.getElementById('termSlider').value;
            const rate = document.getElementById('rateSlider').value;

            // Redirect to application page with values
            window.location.href = '{{ route("applications.create") }}?amount=' + amount + '&term=' + term + '&rate=' + rate;
        }
    </script>
</body>
</html>
