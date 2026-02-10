<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Commercial Loan CRM') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-gray-100 selection:bg-indigo-500 selection:text-white">
        @if (Route::has('login'))
            <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                @auth
                    <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500">Log in</a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500">Register</a>
                    @endif
                @endauth
            </div>
        @endif

        <div class="max-w-7xl mx-auto p-6 lg:p-8">
            <div class="flex justify-center">
                <svg class="h-16 w-auto text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                </svg>
            </div>

            <div class="mt-16">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                    <div class="scale-100 p-6 bg-white from-gray-700/50 via-transparent rounded-lg shadow-xl motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-indigo-500">
                        <div>
                            <div class="h-16 w-16 bg-indigo-50 flex items-center justify-center rounded-full">
                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>

                            <h2 class="mt-6 text-xl font-semibold text-gray-900">Commercial Loan Application</h2>

                            <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                                Apply for a commercial loan quickly and securely. Our streamlined application process guides you through each step, from personal details to financial information.
                            </p>
                        </div>

                        <div class="mt-6">
                            @auth
                                <a href="{{ route('applications.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    View Applications
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    Get Started
                                </a>
                            @endauth
                        </div>
                    </div>

                    <div class="scale-100 p-6 bg-white from-gray-700/50 via-transparent rounded-lg shadow-xl motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-indigo-500">
                        <div>
                            <div class="h-16 w-16 bg-indigo-50 flex items-center justify-center rounded-full">
                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>

                            <h2 class="mt-6 text-xl font-semibold text-gray-900">Secure & Compliant</h2>

                            <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                                Your information is protected with bank-level security. All submissions are tracked with IP addresses and timestamps for complete audit compliance.
                            </p>
                        </div>

                        <div class="mt-6">
                            <span class="text-sm text-gray-600">✓ Email & Phone Uniqueness</span><br>
                            <span class="text-sm text-gray-600">✓ IP Address Tracking</span><br>
                            <span class="text-sm text-gray-600">✓ Complete Audit Trail</span>
                        </div>
                    </div>

                    <div class="scale-100 p-6 bg-white from-gray-700/50 via-transparent rounded-lg shadow-xl motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-indigo-500">
                        <div>
                            <div class="h-16 w-16 bg-indigo-50 flex items-center justify-center rounded-full">
                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                            </div>

                            <h2 class="mt-6 text-xl font-semibold text-gray-900">Real-Time Communication</h2>

                            <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                                Stay informed throughout the process with email and SMS notifications. Respond to assessor questions directly through the secure portal.
                            </p>
                        </div>
                    </div>

                    <div class="scale-100 p-6 bg-white from-gray-700/50 via-transparent rounded-lg shadow-xl motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-indigo-500">
                        <div>
                            <div class="h-16 w-16 bg-indigo-50 flex items-center justify-center rounded-full">
                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>

                            <h2 class="mt-6 text-xl font-semibold text-gray-900">Document Management</h2>

                            <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                                Upload all required documents securely. Track document status and receive notifications when additional documents are needed.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-center mt-16 px-0 sm:items-center sm:justify-between">
                <div class="text-center text-sm text-gray-500 sm:text-left">
                    <div class="flex items-center gap-4">
                        <p>Commercial Loan CRM v1.0</p>
                    </div>
                </div>

                <div class="ml-4 text-center text-sm text-gray-500 sm:text-right sm:ml-0">
                    Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                </div>
            </div>
        </div>
    </div>
</body>
</html>
