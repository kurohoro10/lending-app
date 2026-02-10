<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Poppins', sans-serif; }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-slide-up {
            animation: slideInUp 0.6s ease-out forwards;
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .gradient-text {
            background: linear-gradient(to right, #6366F1, #8B5CF6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Section anchors */
        .policy-section {
            padding: 2rem 0;
            border-bottom: 1px solid #F3F4F6;
        }

        .policy-section:last-of-type {
            border-bottom: none;
        }

        .section-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            color: white;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* TOC link */
        .toc-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.875rem;
            border-radius: 0.75rem;
            text-decoration: none;
            color: #4B5563;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .toc-link:hover {
            background: #EEF2FF;
            color: #6366F1;
        }

        .toc-num {
            width: 1.5rem;
            height: 1.5rem;
            background: #F3F4F6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            font-weight: 700;
            color: #6B7280;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .toc-link:hover .toc-num {
            background: #6366F1;
            color: white;
        }

        /* Policy list */
        .policy-list {
            list-style: none;
            padding: 0;
            margin: 0.75rem 0 0 0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .policy-list li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.9rem;
            color: #4B5563;
            line-height: 1.6;
        }

        .policy-list li::before {
            content: '';
            display: block;
            width: 6px;
            height: 6px;
            background: linear-gradient(135deg, #6366F1, #8B5CF6);
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: 0.55rem;
        }

        /* Highlight box */
        .highlight-box {
            background: linear-gradient(135deg, #EEF2FF 0%, #F5F3FF 100%);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
        }

        .highlight-box.green {
            background: linear-gradient(135deg, #ECFDF5 0%, #F0FDF4 100%);
            border-color: rgba(34, 197, 94, 0.2);
        }

        .policy-body p {
            font-size: 0.9rem;
            color: #4B5563;
            line-height: 1.75;
            margin-top: 0.75rem;
        }
    </style>
</head>
<body class="antialiased bg-gradient-to-br from-gray-50 to-indigo-50 min-h-screen">

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

            <div class="flex items-center space-x-3">
                @auth
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
                    <svg class="w-4 h-4 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-semibold text-indigo-900">Your Privacy Matters</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Privacy
                    <span class="gradient-text"> Policy</span>
                </h1>
                <p class="text-lg text-gray-500">Last Updated: {{ now()->format('F d, Y') }}</p>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">

                <!-- Sidebar: Table of Contents -->
                <div class="lg:col-span-1 animate-slide-up" style="animation-delay: 0.15s;">
                    <div class="bg-white rounded-3xl shadow-xl p-6 card-hover sticky top-28">
                        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10"/>
                            </svg>
                            Table of Contents
                        </h3>
                        <nav class="space-y-1">
                            <a href="#section-1" class="toc-link"><span class="toc-num">1</span> Introduction</a>
                            <a href="#section-2" class="toc-link"><span class="toc-num">2</span> Information We Collect</a>
                            <a href="#section-3" class="toc-link"><span class="toc-num">3</span> How We Use Your Info</a>
                            <a href="#section-4" class="toc-link"><span class="toc-num">4</span> IP Address Tracking</a>
                            <a href="#section-5" class="toc-link"><span class="toc-num">5</span> Information Sharing</a>
                            <a href="#section-6" class="toc-link"><span class="toc-num">6</span> Data Security</a>
                            <a href="#section-7" class="toc-link"><span class="toc-num">7</span> Your Rights</a>
                            <a href="#section-8" class="toc-link"><span class="toc-num">8</span> Cookies & Tracking</a>
                            <a href="#section-9" class="toc-link"><span class="toc-num">9</span> Data Retention</a>
                            <a href="#section-10" class="toc-link"><span class="toc-num">10</span> Children's Privacy</a>
                            <a href="#section-11" class="toc-link"><span class="toc-num">11</span> Policy Changes</a>
                            <a href="#section-12" class="toc-link"><span class="toc-num">12</span> Contact Us</a>
                        </nav>

                        <div class="mt-6 pt-5 border-t border-gray-100">
                            <div class="highlight-box green">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-xs text-green-700 font-medium leading-relaxed">We do <strong>not</strong> sell your personal information to third parties.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Policy Content -->
                <div class="lg:col-span-2 animate-slide-up" style="animation-delay: 0.1s;">
                    <div class="bg-white rounded-3xl shadow-xl p-8 md:p-10 policy-body">

                        <!-- Section 1 -->
                        <div id="section-1" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">1</div>
                                <h2 class="text-xl font-bold text-gray-900">Introduction</h2>
                            </div>
                            <p>
                                {{ config('app.name') }} ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you apply for a commercial loan through our platform.
                            </p>
                        </div>

                        <!-- Section 2 -->
                        <div id="section-2" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">2</div>
                                <h2 class="text-xl font-bold text-gray-900">Information We Collect</h2>
                            </div>

                            <div class="space-y-5">
                                <div class="highlight-box">
                                    <h3 class="text-sm font-bold text-indigo-800 mb-2">2.1 Personal Information</h3>
                                    <ul class="policy-list">
                                        <li>Full name</li>
                                        <li>Email address (must be unique)</li>
                                        <li>Mobile phone number (must be unique)</li>
                                        <li>Date of birth</li>
                                        <li>Marital status and number of dependants</li>
                                        <li>Current and previous residential addresses (3-year history)</li>
                                    </ul>
                                </div>

                                <div class="highlight-box">
                                    <h3 class="text-sm font-bold text-indigo-800 mb-2">2.2 Financial Information</h3>
                                    <ul class="policy-list">
                                        <li>Employment details and income information</li>
                                        <li>Living expenses (as declared by you)</li>
                                        <li>Loan amount requested and purpose</li>
                                        <li>Credit check results (when authorized)</li>
                                    </ul>
                                </div>

                                <div class="highlight-box">
                                    <h3 class="text-sm font-bold text-indigo-800 mb-2">2.3 Technical Information</h3>
                                    <ul class="policy-list">
                                        <li>IP address (tracked on all submissions for security and compliance)</li>
                                        <li>Browser type and device information</li>
                                        <li>Date and time of access</li>
                                        <li>Pages viewed and actions taken</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3 -->
                        <div id="section-3" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">3</div>
                                <h2 class="text-xl font-bold text-gray-900">How We Use Your Information</h2>
                            </div>
                            <p>We use your information to:</p>
                            <ul class="policy-list mt-3">
                                <li>Process your loan application</li>
                                <li>Verify your identity and assess creditworthiness</li>
                                <li>Communicate with you about your application via email and SMS</li>
                                <li>Comply with legal and regulatory requirements</li>
                                <li>Maintain security and prevent fraud</li>
                                <li>Improve our services</li>
                            </ul>
                        </div>

                        <!-- Section 4 -->
                        <div id="section-4" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">4</div>
                                <h2 class="text-xl font-bold text-gray-900">IP Address Tracking</h2>
                            </div>
                            <p>We track your IP address on all submissions and interactions with our platform. This is done for:</p>
                            <ul class="policy-list mt-3">
                                <li>Security and fraud prevention</li>
                                <li>Compliance with financial regulations</li>
                                <li>Audit trail maintenance</li>
                                <li>Dispute resolution</li>
                            </ul>
                        </div>

                        <!-- Section 5 -->
                        <div id="section-5" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">5</div>
                                <h2 class="text-xl font-bold text-gray-900">Information Sharing</h2>
                            </div>
                            <p>We may share your information with:</p>
                            <ul class="policy-list mt-3">
                                <li>Credit reporting agencies (with your consent)</li>
                                <li>Service providers who assist in processing your application</li>
                                <li>Legal and regulatory authorities when required by law</li>
                                <li>Our internal assessment team for application review</li>
                            </ul>
                            <div class="highlight-box green mt-5">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-sm font-semibold text-green-800">We do NOT sell your personal information to third parties.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 6 -->
                        <div id="section-6" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">6</div>
                                <h2 class="text-xl font-bold text-gray-900">Data Security</h2>
                            </div>
                            <p>We implement industry-standard security measures to protect your information:</p>
                            <ul class="policy-list mt-3">
                                <li>Encrypted data storage</li>
                                <li>Secure communication channels</li>
                                <li>Role-based access controls</li>
                                <li>Regular security audits</li>
                                <li>Complete activity logging</li>
                            </ul>
                        </div>

                        <!-- Section 7 -->
                        <div id="section-7" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">7</div>
                                <h2 class="text-xl font-bold text-gray-900">Your Rights</h2>
                            </div>
                            <p>You have the right to:</p>
                            <ul class="policy-list mt-3">
                                <li>Access your personal information</li>
                                <li>Request correction of inaccurate information</li>
                                <li>Request deletion of your information (subject to legal requirements)</li>
                                <li>Withdraw consent for marketing communications</li>
                                <li>Lodge a complaint with the relevant privacy authority</li>
                            </ul>
                        </div>

                        <!-- Section 8 -->
                        <div id="section-8" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">8</div>
                                <h2 class="text-xl font-bold text-gray-900">Cookies and Tracking</h2>
                            </div>
                            <p>
                                We use cookies and similar tracking technologies to enhance your experience and maintain session security. You can control cookie settings through your browser.
                            </p>
                        </div>

                        <!-- Section 9 -->
                        <div id="section-9" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">9</div>
                                <h2 class="text-xl font-bold text-gray-900">Data Retention</h2>
                            </div>
                            <p>We retain your information for as long as necessary to:</p>
                            <ul class="policy-list mt-3">
                                <li>Fulfill the purposes outlined in this policy</li>
                                <li>Comply with legal and regulatory obligations</li>
                                <li>Resolve disputes and enforce agreements</li>
                            </ul>
                        </div>

                        <!-- Section 10 -->
                        <div id="section-10" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">10</div>
                                <h2 class="text-xl font-bold text-gray-900">Children's Privacy</h2>
                            </div>
                            <p>
                                Our services are not intended for individuals under 18 years of age. We do not knowingly collect information from children.
                            </p>
                        </div>

                        <!-- Section 11 -->
                        <div id="section-11" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">11</div>
                                <h2 class="text-xl font-bold text-gray-900">Changes to This Policy</h2>
                            </div>
                            <p>
                                We may update this Privacy Policy from time to time. We will notify you of significant changes via email or through our platform.
                            </p>
                        </div>

                        <!-- Section 12 -->
                        <div id="section-12" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">12</div>
                                <h2 class="text-xl font-bold text-gray-900">Contact Us</h2>
                            </div>
                            <p>If you have questions about this Privacy Policy or wish to exercise your rights, please contact us:</p>

                            <div class="highlight-box mt-4 flex flex-col sm:flex-row gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Email</p>
                                        <a href="mailto:privacy@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition">
                                            privacy@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}
                                        </a>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Address</p>
                                        <p class="text-sm font-semibold text-gray-700">[Your Company Address]</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-16 pt-8 border-t border-gray-200 text-center animate-slide-up" style="animation-delay: 0.3s;">
                <div class="flex flex-col md:flex-row justify-between items-center text-gray-500">
                    <p class="mb-4 md:mb-0 text-sm">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                    <div class="flex gap-6 text-xs text-gray-400">
                        <a href="{{ route('privacy-policy') }}" class="hover:text-indigo-600 transition font-medium text-indigo-500">Privacy Policy</a>
                        <a href="{{ route('terms-and-conditions') }}" class="hover:text-indigo-600 transition">Terms & Conditions</a>
                        <a href="{{ url('/') }}" class="hover:text-indigo-600 transition">← Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
