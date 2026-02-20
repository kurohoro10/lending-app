<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Apply for Loan - {{ config('app.name', 'Commercial Loan CRM') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Poppins', sans-serif; }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(99, 102, 241, 0.4); }
            50% { box-shadow: 0 0 40px rgba(99, 102, 241, 0.6); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .animate-slide-up {
            animation: slideInUp 0.6s ease-out forwards;
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        /* Custom styled inputs */
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #E5E7EB;
            border-radius: 0.875rem;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            color: #1F2937;
            background: #FAFAFA;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: #6366F1;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .form-input.error {
            border-color: #EF4444;
            background: #FFF5F5;
        }

        .form-input::placeholder {
            color: #9CA3AF;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #4B5563;
            margin-bottom: 0.5rem;
            letter-spacing: 0.025em;
            text-transform: uppercase;
        }

        /* Section badge */
        .section-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            color: white;
            border-radius: 50%;
            font-size: 0.875rem;
            font-weight: 700;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        /* Checkbox custom */
        .custom-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #D1D5DB;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
            position: relative;
            margin-top: 2px;
        }

        .custom-checkbox:checked {
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            border-color: #6366F1;
        }

        .custom-checkbox:checked::after {
            content: '';
            position: absolute;
            left: 3px;
            top: 0px;
            width: 6px;
            height: 10px;
            border: 2px solid white;
            border-top: none;
            border-left: none;
            transform: rotate(45deg);
        }

        .custom-checkbox:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        /* Input with icon */
        .input-icon-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 0.9rem;
            font-weight: 600;
            pointer-events: none;
        }

        .input-with-icon {
            padding-left: 2rem;
        }

        /* Progress steps */
        .step-connector {
            position: absolute;
            top: 1.25rem;
            left: calc(50% + 1.5rem);
            right: calc(-50% + 1.5rem);
            height: 2px;
            background: linear-gradient(to right, #6366F1, #E5E7EB);
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(to right, #6366F1, #8B5CF6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Select custom arrow */
        .form-select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.25em 1.25em;
            padding-right: 2.5rem;
        }

        /* Submit button */
        .submit-btn {
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            color: white;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            padding: 1rem 2.5rem;
            border-radius: 0.875rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .submit-btn:hover {
            transform: scale(1.03);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }

        .submit-btn:active {
            transform: scale(0.98);
        }

        /* Cancel button */
        .cancel-btn {
            background: white;
            color: #4B5563;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.875rem 1.75rem;
            border-radius: 0.875rem;
            border: 2px solid #E5E7EB;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .cancel-btn:hover {
            border-color: #6366F1;
            color: #6366F1;
            background: #FAFAFA;
        }

        /* Info panel */
        .info-panel {
            background: linear-gradient(135deg, #EEF2FF 0%, #F5F3FF 100%);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 1rem;
            padding: 1rem 1.25rem;
        }

        /* Next steps cards */
        .next-step-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            border-radius: 0.875rem;
            background: #F9FAFB;
            border: 1px solid #F3F4F6;
            transition: all 0.2s;
        }

        .next-step-item:hover {
            background: #EEF2FF;
            border-color: rgba(99, 102, 241, 0.2);
        }

        .next-step-icon {
            width: 2rem;
            height: 2rem;
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .submit-btn:disabled {
            transform: none !important;
            box-shadow: none !important;
            filter: grayscale(0.4);
            cursor: not-allowed;
        }
    </style>
</head>
<body class="antialiased bg-gradient-to-br from-gray-50 to-indigo-50 min-h-screen">

    <!-- Floating Navigation (matches welcome page exactly) -->
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

            <div class="flex items-center space-x-3">
                @auth
                    <span class="text-sm text-gray-500 hidden sm:block">{{ auth()->user()->name }}</span>
                    <a href="{{ url('/dashboard') }}" class="px-5 py-2 text-gray-700 hover:text-indigo-600 font-medium transition">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2 text-gray-700 hover:text-indigo-600 font-medium transition">Login</a>
                    <a href="{{ url('/') }}" class="px-5 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-full font-medium hover:shadow-lg transition text-sm">
                        ← Home
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <div class="pt-32 pb-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">

            <!-- Page Hero -->
            <div class="text-center mb-12 animate-slide-up">
                <div class="inline-flex items-center px-4 py-2 bg-indigo-100 rounded-full mb-4">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                    <span class="text-sm font-semibold text-indigo-900">Secure Application Portal</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    @auth
                        Start Your
                        <span class="gradient-text"> Loan Application</span>
                    @else
                        Apply for a
                        <span class="gradient-text"> Commercial Loan</span>
                    @endauth
                </h1>
                <p class="text-lg text-gray-600 max-w-xl mx-auto">
                    @auth
                        Complete the form below to begin your commercial loan application.
                    @else
                        Create your account and begin your application. The process takes about 10–15 minutes.
                    @endauth
                </p>
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
            <div class="mb-6 flex items-start gap-3 bg-green-50 border border-green-200 rounded-2xl p-4 animate-fade-in">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="h-4 w-4 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-green-700 pt-1">{{ session('success') }}</p>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200 rounded-2xl p-4 animate-fade-in">
                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="h-4 w-4 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-red-700 pt-1">{{ session('error') }}</p>
            </div>
            @endif

            <div class="grid lg:grid-cols-3 gap-8">

                <!-- Main Form -->
                <div class="lg:col-span-2 animate-slide-up" style="animation-delay: 0.1s;">
                    <div class="bg-white rounded-3xl shadow-xl p-8 card-hover">

                        <form method="POST" action="{{ route('applications.store') }}">
                            @csrf

                            <!-- ── Section 1: Account Info (guests only) ── -->
                            @guest
                            <div class="mb-10 pb-10 border-b border-gray-100">
                                <div class="flex items-center mb-6">
                                    <div class="section-badge pulse-glow">1</div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 leading-tight">Create Your Account</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">You'll use these credentials to track your application</p>
                                    </div>
                                </div>

                                <div class="space-y-5">
                                    <div>
                                        <label for="name" class="form-label">Full Name <span class="text-indigo-500">*</span></label>
                                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                               placeholder="Jane Smith"
                                               class="form-input @error('name') error @enderror">
                                        @error('name')
                                            <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="email" class="form-label">Email Address <span class="text-indigo-500">*</span></label>
                                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                               placeholder="jane@company.com"
                                               class="form-input @error('email') error @enderror">
                                        <p class="mt-1 text-xs text-gray-400">This will be your login email address</p>
                                        @error('email')
                                            <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <label for="password" class="form-label">Password <span class="text-indigo-500">*</span></label>
                                            <input type="password" name="password" id="password" required
                                                   placeholder="Min. 8 characters"
                                                   class="form-input @error('password') error @enderror">
                                            @error('password')
                                                <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="password_confirmation" class="form-label">Confirm Password <span class="text-indigo-500">*</span></label>
                                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                                   placeholder="Repeat password"
                                                   class="form-input">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endguest

                            <!-- Logged-In User Banner -->
                            @auth
                            <div class="mb-8 flex items-center gap-3 bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-100 rounded-2xl p-4">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-indigo-900">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-indigo-600">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="ml-auto px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                    ✓ Verified
                                </div>
                            </div>
                            @endauth

                            <!-- ── Section 2: Loan Details ── -->
                            <div class="mb-10 pb-10 border-b border-gray-100">
                                <div class="flex items-center mb-6">
                                    <div class="section-badge">{{ auth()->check() ? '1' : '2' }}</div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 leading-tight">Loan Details</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Tell us how much you need and what it's for</p>
                                    </div>
                                </div>

                                <div class="space-y-5">
                                    <div>
                                        <label for="loan_amount" class="form-label">Loan Amount Requested <span class="text-indigo-500">*</span></label>
                                        <div class="input-icon-wrapper">
                                            <span class="input-icon">$</span>

                                            <!-- Loan Amount Field -->
                                            <input type="number" name="loan_amount" id="loan_amount" step="0.01" min="1000"
                                                value="{{ old('loan_amount', $calculatorValues['loan_amount']) }}"
                                                placeholder="100,000"
                                                class="form-input input-with-icon @error('loan_amount') error @enderror"
                                                required>

                                        </div>
                                        @error('loan_amount')
                                            <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="loan_purpose" class="form-label">Loan Purpose <span class="text-indigo-500">*</span></label>
                                        <select name="loan_purpose" id="loan_purpose" required
                                                class="form-input form-select @error('loan_purpose') error @enderror">
                                            <option value="">Select purpose...</option>
                                            <option value="business_expansion" {{ old('loan_purpose') == 'business_expansion' ? 'selected' : '' }}>Business Expansion</option>
                                            <option value="equipment_purchase" {{ old('loan_purpose') == 'equipment_purchase' ? 'selected' : '' }}>Equipment Purchase</option>
                                            <option value="working_capital" {{ old('loan_purpose') == 'working_capital' ? 'selected' : '' }}>Working Capital</option>
                                            <option value="property_purchase" {{ old('loan_purpose') == 'property_purchase' ? 'selected' : '' }}>Property Purchase</option>
                                            <option value="debt_consolidation" {{ old('loan_purpose') == 'debt_consolidation' ? 'selected' : '' }}>Debt Consolidation</option>
                                            <option value="other" {{ old('loan_purpose') == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('loan_purpose')
                                            <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="loan_purpose_details" class="form-label">Purpose Details <span class="text-gray-400 normal-case font-normal">(optional)</span></label>
                                        <textarea name="loan_purpose_details" id="loan_purpose_details" rows="3"
                                                  placeholder="Please provide more details about the loan purpose..."
                                                  class="form-input resize-none">{{ old('loan_purpose_details') }}</textarea>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <label for="term_months" class="form-label">Loan Term <span class="text-indigo-500">*</span></label>

                                            <!-- Term Months Field -->
                                            <input type="number" name="term_months" id="term_months" min="1" max="360"
                                                value="{{ old('term_months', $calculatorValues['term_months']) }}"
                                                class="form-input @error('term_months') error @enderror"
                                                required>

                                            <p class="mt-1 text-xs text-gray-400">Months — typically 12–360</p>
                                            @error('term_months')
                                                <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="security_type" class="form-label">Security Type <span class="text-gray-400 normal-case font-normal">(optional)</span></label>
                                            <select name="security_type" id="security_type"
                                                    class="form-input form-select">
                                                <option value="">Select security type...</option>
                                                <option value="property" {{ old('security_type') == 'property' ? 'selected' : '' }}>Property</option>
                                                <option value="equipment" {{ old('security_type') == 'equipment' ? 'selected' : '' }}>Equipment</option>
                                                <option value="vehicle" {{ old('security_type') == 'vehicle' ? 'selected' : '' }}>Vehicle</option>
                                                <option value="unsecured" {{ old('security_type') == 'unsecured' ? 'selected' : '' }}>Unsecured</option>
                                                <option value="other" {{ old('security_type') == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ── Section 3: Consent ── -->
                            <div class="mb-8">
                                <div class="flex items-center mb-6">
                                    <div class="section-badge">{{ auth()->check() ? '2' : '3' }}</div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 leading-tight">Consent & Agreements</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Please review and accept before submitting</p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <label class="flex items-start gap-3 p-4 rounded-2xl border-2 border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/30 cursor-pointer transition-all">
                                        <input id="privacy_consent" name="privacy_consent" type="checkbox" value="1" required
                                               class="custom-checkbox @error('privacy_consent') error @enderror">
                                        <div>
                                            <span class="text-sm font-semibold text-gray-800">I consent to the collection and use of my personal information</span>
                                            <p class="text-xs text-gray-500 mt-1">
                                                I have read and agree to the
                                                <a href="{{ route('privacy-policy') }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium underline decoration-dotted">Privacy Policy</a>
                                            </p>
                                        </div>
                                    </label>
                                    @error('privacy_consent')
                                        <p class="text-xs text-red-500 ml-2 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            {{ $message }}
                                        </p>
                                    @enderror

                                    <label class="flex items-start gap-3 p-4 rounded-2xl border-2 border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/30 cursor-pointer transition-all">
                                        <input id="terms_consent" name="terms_consent" type="checkbox" value="1" required
                                               class="custom-checkbox @error('terms_consent') error @enderror">
                                        <div>
                                            <span class="text-sm font-semibold text-gray-800">I agree to the Terms and Conditions</span>
                                            <p class="text-xs text-gray-500 mt-1">
                                                I have read and agree to the
                                                <a href="{{ route('terms-and-conditions') }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium underline decoration-dotted">Terms and Conditions</a>
                                            </p>
                                        </div>
                                    </label>
                                    @error('terms_consent')
                                        <p class="text-xs text-red-500 ml-2 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Compliance Notice -->
                                <div class="info-panel mt-5 flex items-start gap-3">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="h-4 w-4 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs text-indigo-700 leading-relaxed">
                                        By submitting this application, you confirm all information provided is accurate and complete.
                                        Your submission will be tracked with your IP address for security and compliance purposes.
                                    </p>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex items-center justify-between pt-2">
                                <a href="{{ auth()->check() ? route('applications.index') : url('/') }}" class="cancel-btn">
                                    ← Cancel
                                </a>
                                <button id="submitBtn" type="submit" class="submit-btn opacity-50 cursor-not-allowed" disabled>
                                    @auth
                                        Start Application
                                    @else
                                        Create Account & Apply
                                    @endauth
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6 animate-slide-up" style="animation-delay: 0.25s;">

                    <!-- Loan Calculator -->
                    <div class="bg-white rounded-3xl shadow-xl p-6 card-hover">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-base font-bold text-gray-900">Loan Calculator</h3>
                            <div class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                Instant Quote
                            </div>
                        </div>

                        <div class="space-y-6">
                            <!-- Calculator Loan Amount -->
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <label class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Loan Amount</label>
                                    <span class="text-xl font-bold text-indigo-600" id="calcLoanAmount">$100,000</span>
                                </div>
                                <div class="py-2 range-wrapper">
                                    <div class="range-background"></div>
                                    <div class="range-fill" id="calcLoanFill"></div>

                                    <!-- Loan Amount Slider -->
                                    <input type="range" min="10000" max="1000000"
                                        value="{{ $calculatorValues['loan_amount'] }}"
                                        step="10000" id="calcLoanSlider" class="w-full">

                                </div>
                                <div class="flex justify-between text-xs text-gray-400 mt-2">
                                    <span>$10K</span>
                                    <span>$1M</span>
                                </div>
                            </div>

                            <!-- Calculator Term -->
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <label class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Loan Term</label>
                                    <span class="text-lg font-bold text-indigo-600" id="calcLoanTerm">60 months</span>
                                </div>
                                <div class="py-2 range-wrapper">
                                    <div class="range-background"></div>
                                    <div class="range-fill" id="calcTermFill"></div>

                                    <!-- Term Slider -->
                                    <input type="range" min="12" max="84"
                                        value="{{ $calculatorValues['term_months'] }}"
                                        step="12" id="calcTermSlider" class="w-full">

                                </div>
                                <div class="flex justify-between text-xs text-gray-400 mt-2">
                                    <span>12 months</span>
                                    <span>84 months</span>
                                </div>
                            </div>

                            <!-- Calculator Interest Rate -->
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <label class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Interest Rate</label>
                                    <span class="text-lg font-bold text-indigo-600" id="calcInterestRate">8.5%</span>
                                </div>
                                <div class="py-2 range-wrapper">
                                    <div class="range-background"></div>
                                    <div class="range-fill" id="calcRateFill"></div>

                                    <!-- Interest Rate Slider -->
                                    <input type="range" min="5" max="15"
                                        value="{{ $calculatorValues['interest_rate'] }}"
                                        step="0.5" id="calcRateSlider" class="w-full">

                                </div>
                                <div class="flex justify-between text-xs text-gray-400 mt-2">
                                    <span>5%</span>
                                    <span>15%</span>
                                </div>
                            </div>

                            <!-- Calculator Results -->
                            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-5 mt-6">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Monthly Payment</span>
                                    <span class="text-2xl font-bold text-indigo-600" id="calcMonthlyPayment">$2,058</span>
                                </div>
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-xs text-gray-600">Total Interest</span>
                                    <span class="text-base font-semibold text-gray-900" id="calcTotalInterest">$23,480</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-600">Total Repayment</span>
                                    <span class="text-base font-semibold text-gray-900" id="calcTotalRepayment">$123,480</span>
                                </div>
                            </div>

                            <!-- Use These Values Button -->
                            <button type="button" onclick="useCalculatorValues()" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold text-sm hover:shadow-lg transition transform hover:scale-105">
                                Use These Values in Form →
                            </button>

                            <p class="text-xs text-gray-400 text-center leading-relaxed">
                                * Rates shown are illustrative. Actual rates may vary based on creditworthiness.
                            </p>
                        </div>
                    </div>

                    <!-- What Happens Next -->
                    <div class="bg-white rounded-3xl shadow-xl p-6 card-hover">
                        <h3 class="text-base font-bold text-gray-900 mb-5 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </span>
                            What happens next?
                        </h3>

                        <div class="space-y-3">
                            @guest
                            <div class="next-step-item">
                                <div class="next-step-icon">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-600">Your account will be created and you'll be logged in automatically</p>
                            </div>
                            @endguest

                            <div class="next-step-item">
                                <div class="next-step-icon">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-600">Complete personal info, employment & address details</p>
                            </div>

                            <div class="next-step-item">
                                <div class="next-step-icon">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-600">Upload required documents — ID, income proof, etc.</p>
                            </div>

                            <div class="next-step-item">
                                <div class="next-step-icon">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-600">Submit for review — our team responds in 24–48 hours</p>
                            </div>
                        </div>
                    </div>

                    <!-- Trust Signals -->
                    <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-3xl shadow-xl p-6 text-white">
                        <h3 class="text-base font-bold mb-4">Why LoanFlow?</h3>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-indigo-200 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-indigo-100">No hidden fees or charges</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-indigo-200 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-indigo-100">256-bit bank-level encryption</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-indigo-200 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-indigo-100">Decision within 24–48 hours</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-indigo-200 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-indigo-100">Dedicated loan specialist support</span>
                            </div>
                        </div>

                        <div class="mt-5 pt-5 border-t border-indigo-500 grid grid-cols-2 gap-3 text-center">
                            <div>
                                <div class="text-2xl font-bold">4.9★</div>
                                <div class="text-xs text-indigo-200">Customer Rating</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold">10K+</div>
                                <div class="text-xs text-indigo-200">Happy Clients</div>
                            </div>
                        </div>
                    </div>

                    <!-- Need Help -->
                    <div class="bg-white rounded-3xl shadow-xl p-6 card-hover text-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-pink-600 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <h4 class="font-bold text-gray-900 mb-1">Need Help?</h4>
                        <p class="text-xs text-gray-500 mb-3">Our specialists are available 24/7</p>
                        <a href="mailto:support@loanflow.com" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition">
                            support@loanflow.com →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-16 pt-8 border-t border-gray-200 text-center animate-slide-up" style="animation-delay: 0.3s;">
                <div class="flex flex-col md:flex-row justify-between items-center text-gray-500">
                    <p class="mb-4 md:mb-0 text-sm">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                    <div class="flex gap-6 text-xs text-gray-400">
                        <a href="{{ route('privacy-policy') }}" class="hover:text-indigo-600 transition">Privacy Policy</a>
                        <a href="{{ route('terms-and-conditions') }}" class="hover:text-indigo-600 transition">Terms & Conditions</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ==========================================
        // SUBMIT BUTTON LOADING STATE
        // ==========================================
        document.addEventListener('DOMContentLoaded', () => {
            const form      = document.querySelector('form');
            const submitBtn = document.getElementById('submitBtn');
            const privacy   = document.getElementById('privacy_consent');
            const terms     = document.getElementById('terms_consent');

            // Store original button content
            const originalButtonHTML = submitBtn.innerHTML;

            // Function to show loading state
            function showLoadingState() {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-75', 'cursor-wait');
                submitBtn.classList.remove('hover:scale-105', 'hover:shadow-lg');

                submitBtn.innerHTML = `
                    <svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Creating Your Application...</span>
                `;
            }

            // Function to restore button state (in case of validation error)
            function restoreButtonState() {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-75', 'cursor-wait');
                submitBtn.classList.add('hover:scale-105', 'hover:shadow-lg');
                submitBtn.innerHTML = originalButtonHTML;
                updateSubmitState(); // Re-check if button should be enabled
            }

            // Function to update submit button enabled/disabled state
            function updateSubmitState() {
                const enabled      = privacy.checked && terms.checked;
                submitBtn.disabled = !enabled;
                submitBtn.classList.toggle('opacity-50', !enabled);
                submitBtn.classList.toggle('cursor-not-allowed', !enabled);
            }

            // Handle form submission
            form.addEventListener('submit', function(e) {
                // Check if consents are checked
                if (privacy.checked && terms.checked) {
                    // Show loading state
                    showLoadingState();

                    // If there are validation errors on the backend, the page will reload
                    // and the button will automatically return to normal state

                    // Optional: Add a timeout to restore button if submission takes too long
                    // (This handles cases where backend validation fails)
                    setTimeout(() => {
                        // Check if we're still on the same page (form didn't submit successfully)
                        if (document.getElementById('submitBtn')) {
                            restoreButtonState();
                        }
                    }, 10000); // 10 seconds timeout
                }
            });

            // Restore button state if there are validation errors on page load
            @if($errors->any())
                restoreButtonState();
            @endif

            // Event listeners for consent checkboxes
            privacy.addEventListener('change', updateSubmitState);
            terms.addEventListener('change', updateSubmitState);

            // Initial state
            updateSubmitState();

            // Initial calculation with passed values
            calculateLoan();

            // If values were passed from welcome page, show notification
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('amount')) {
                setTimeout(() => {
                    const tempMessage     = document.createElement('div');
                    tempMessage.className = 'fixed top-24 right-6 bg-green-500 text-white px-6 py-3 rounded-xl shadow-2xl z-50 animate-fade-in';
                    tempMessage.innerHTML = `
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-semibold">Calculator values loaded!</span>
                        </div>
                    `;
                    document.body.appendChild(tempMessage);

                    setTimeout(() => {
                        tempMessage.style.opacity    = '0';
                        tempMessage.style.transform  = 'translateY(-10px)';
                        tempMessage.style.transition = 'all 0.3s ease';
                        setTimeout(() => tempMessage.remove(), 300);
                    }, 3000);
                }, 500);
            }
        });

        // Add CSS for spinner animation (if not already in your styles)
        const style       = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(360deg);
                }
            }

            .animate-spin {
                animation: spin 1s linear infinite;
            }

            /* Smooth transition for button state changes */
            .submit-btn {
                transition: all 0.3s ease;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
