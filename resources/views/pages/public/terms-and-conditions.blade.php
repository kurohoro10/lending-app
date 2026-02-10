<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms and Conditions - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Poppins', sans-serif; }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-slide-up { animation: slideInUp 0.6s ease-out forwards; }

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

        .policy-section {
            padding: 2rem 0;
            border-bottom: 1px solid #F3F4F6;
        }
        .policy-section:last-of-type { border-bottom: none; }

        .section-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            color: white;
            border-radius: 50%;
            font-size: 0.7rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .toc-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.75rem;
            text-decoration: none;
            color: #4B5563;
            font-size: 0.775rem;
            font-weight: 500;
            transition: all 0.2s;
            line-height: 1.3;
        }
        .toc-link:hover { background: #EEF2FF; color: #6366F1; }

        .toc-num {
            width: 1.4rem;
            height: 1.4rem;
            background: #F3F4F6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.6rem;
            font-weight: 700;
            color: #6B7280;
            flex-shrink: 0;
            transition: all 0.2s;
        }
        .toc-link:hover .toc-num { background: #6366F1; color: white; }

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
            font-size: 0.875rem;
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

        .highlight-box {
            background: linear-gradient(135deg, #EEF2FF 0%, #F5F3FF 100%);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
        }
        .highlight-box.amber {
            background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
            border-color: rgba(245, 158, 11, 0.3);
        }
        .highlight-box.green {
            background: linear-gradient(135deg, #ECFDF5 0%, #F0FDF4 100%);
            border-color: rgba(34, 197, 94, 0.2);
        }

        .subsection-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: #6366F1;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .policy-body p {
            font-size: 0.875rem;
            color: #4B5563;
            line-height: 1.75;
            margin-top: 0.625rem;
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
                    <a href="{{ url('/') }}" class="px-5 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-full font-medium hover:shadow-lg transition text-sm">← Home</a>
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
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-semibold text-indigo-900">Legal Agreement</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Terms &
                    <span class="gradient-text"> Conditions</span>
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
                        <nav class="space-y-0.5 max-h-96 overflow-y-auto pr-1">
                            <a href="#section-1"  class="toc-link"><span class="toc-num">1</span>  Acceptance of Terms</a>
                            <a href="#section-2"  class="toc-link"><span class="toc-num">2</span>  Eligibility</a>
                            <a href="#section-3"  class="toc-link"><span class="toc-num">3</span>  Account Registration</a>
                            <a href="#section-4"  class="toc-link"><span class="toc-num">4</span>  Application Process</a>
                            <a href="#section-5"  class="toc-link"><span class="toc-num">5</span>  Application Review</a>
                            <a href="#section-6"  class="toc-link"><span class="toc-num">6</span>  Communication</a>
                            <a href="#section-7"  class="toc-link"><span class="toc-num">7</span>  Document Upload</a>
                            <a href="#section-8"  class="toc-link"><span class="toc-num">8</span>  Living Expenses</a>
                            <a href="#section-9"  class="toc-link"><span class="toc-num">9</span>  Data Security & Privacy</a>
                            <a href="#section-10" class="toc-link"><span class="toc-num">10</span> Intellectual Property</a>
                            <a href="#section-11" class="toc-link"><span class="toc-num">11</span> Prohibited Activities</a>
                            <a href="#section-12" class="toc-link"><span class="toc-num">12</span> Electronic Signatures</a>
                            <a href="#section-13" class="toc-link"><span class="toc-num">13</span> Limitation of Liability</a>
                            <a href="#section-14" class="toc-link"><span class="toc-num">14</span> Termination</a>
                            <a href="#section-15" class="toc-link"><span class="toc-num">15</span> Changes to Terms</a>
                            <a href="#section-16" class="toc-link"><span class="toc-num">16</span> Governing Law</a>
                            <a href="#section-17" class="toc-link"><span class="toc-num">17</span> Dispute Resolution</a>
                            <a href="#section-18" class="toc-link"><span class="toc-num">18</span> Contact Information</a>
                        </nav>

                        <!-- Quick summary -->
                        <div class="mt-5 pt-5 border-t border-gray-100 space-y-3">
                            <div class="highlight-box green">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-xs text-green-700 font-medium leading-relaxed">Your electronic submission carries the same legal weight as a handwritten signature.</p>
                                </div>
                            </div>
                            <div class="highlight-box amber">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-xs text-amber-700 font-medium leading-relaxed">All submissions are IP-tracked for compliance. Accurate information is required.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-2 animate-slide-up" style="animation-delay: 0.1s;">
                    <div class="bg-white rounded-3xl shadow-xl p-8 md:p-10 policy-body">

                        <!-- Section 1 -->
                        <div id="section-1" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">1</div>
                                <h2 class="text-xl font-bold text-gray-900">Acceptance of Terms</h2>
                            </div>
                            <p>By creating an account and submitting a loan application through {{ config('app.name') }}, you agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use our services.</p>
                        </div>

                        <!-- Section 2 -->
                        <div id="section-2" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">2</div>
                                <h2 class="text-xl font-bold text-gray-900">Eligibility</h2>
                            </div>
                            <p>To apply for a commercial loan, you must:</p>
                            <ul class="policy-list">
                                <li>Be at least 18 years of age</li>
                                <li>Have legal capacity to enter into a binding contract</li>
                                <li>Provide accurate and complete information</li>
                                <li>Have a valid email address and mobile phone number</li>
                            </ul>
                        </div>

                        <!-- Section 3 -->
                        <div id="section-3" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">3</div>
                                <h2 class="text-xl font-bold text-gray-900">Account Registration</h2>
                            </div>
                            <div class="space-y-4">
                                <div class="highlight-box">
                                    <p class="subsection-title">3.1 Account Creation</p>
                                    <p class="!mt-0">When you create an account, you must provide accurate information. Each email address and mobile phone number can only be associated with one account.</p>
                                </div>
                                <div class="highlight-box">
                                    <p class="subsection-title">3.2 Account Security</p>
                                    <p class="!mt-0">You are responsible for:</p>
                                    <ul class="policy-list">
                                        <li>Maintaining the confidentiality of your password</li>
                                        <li>All activities that occur under your account</li>
                                        <li>Notifying us immediately of any unauthorized access</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4 -->
                        <div id="section-4" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">4</div>
                                <h2 class="text-xl font-bold text-gray-900">Application Process</h2>
                            </div>
                            <div class="space-y-4">
                                <div class="highlight-box">
                                    <p class="subsection-title">4.1 Information Accuracy</p>
                                    <p class="!mt-0">You warrant that all information provided in your loan application is true, accurate, and complete. Providing false or misleading information may result in:</p>
                                    <ul class="policy-list">
                                        <li>Immediate rejection of your application</li>
                                        <li>Termination of your account</li>
                                        <li>Legal action for fraud</li>
                                    </ul>
                                </div>
                                <div class="highlight-box">
                                    <p class="subsection-title">4.2 IP Address Tracking</p>
                                    <p class="!mt-0">All submissions, updates, and interactions are tracked with your IP address for security, compliance, and audit purposes. This tracking is mandatory and cannot be disabled.</p>
                                </div>
                                <div class="highlight-box">
                                    <p class="subsection-title">4.3 Credit Checks</p>
                                    <p class="!mt-0">By submitting your application, you authorize us to:</p>
                                    <ul class="policy-list">
                                        <li>Conduct credit checks with credit reporting agencies</li>
                                        <li>Verify your employment and income information</li>
                                        <li>Contact third parties to verify the information you provided</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Section 5 -->
                        <div id="section-5" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">5</div>
                                <h2 class="text-xl font-bold text-gray-900">Application Review</h2>
                            </div>
                            <div class="space-y-4">
                                <div class="highlight-box">
                                    <p class="subsection-title">5.1 No Guarantee of Approval</p>
                                    <p class="!mt-0">Submitting an application does not guarantee approval. We reserve the right to approve or decline any application at our sole discretion.</p>
                                </div>
                                <div class="highlight-box">
                                    <p class="subsection-title">5.2 Additional Information</p>
                                    <p class="!mt-0">We may request additional information or documentation at any time during the review process. Failure to provide requested information may result in application rejection.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 6 -->
                        <div id="section-6" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">6</div>
                                <h2 class="text-xl font-bold text-gray-900">Communication</h2>
                            </div>
                            <div class="space-y-4">
                                <div class="highlight-box">
                                    <p class="subsection-title">6.1 Email and SMS</p>
                                    <p class="!mt-0">By creating an account, you consent to receive:</p>
                                    <ul class="policy-list">
                                        <li>Application status updates via email and SMS</li>
                                        <li>Requests for additional information</li>
                                        <li>Important notices about your application</li>
                                        <li>Service-related communications</li>
                                    </ul>
                                </div>
                                <div class="highlight-box">
                                    <p class="subsection-title">6.2 Communication Logging</p>
                                    <p class="!mt-0">All email and SMS communications are logged and stored for compliance and quality assurance purposes.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 7 -->
                        <div id="section-7" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">7</div>
                                <h2 class="text-xl font-bold text-gray-900">Document Upload</h2>
                            </div>
                            <p>When uploading documents, you confirm that:</p>
                            <ul class="policy-list">
                                <li>You have the right to share the documents</li>
                                <li>The documents are authentic and unaltered</li>
                                <li>The information in the documents is current and accurate</li>
                            </ul>
                        </div>

                        <!-- Section 8 -->
                        <div id="section-8" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">8</div>
                                <h2 class="text-xl font-bold text-gray-900">Living Expenses Verification</h2>
                            </div>
                            <p>You understand that:</p>
                            <ul class="policy-list">
                                <li>The living expenses you declare will be reviewed by our assessors</li>
                                <li>We may verify these expenses through various means</li>
                                <li>Discrepancies may affect your application outcome</li>
                            </ul>
                        </div>

                        <!-- Section 9 -->
                        <div id="section-9" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">9</div>
                                <h2 class="text-xl font-bold text-gray-900">Data Security and Privacy</h2>
                            </div>
                            <p>Our use of your personal information is governed by our <a href="{{ route('privacy-policy') }}" class="text-indigo-600 hover:text-indigo-800 font-medium underline decoration-dotted">Privacy Policy</a>. We implement security measures including:</p>
                            <ul class="policy-list">
                                <li>Encrypted data storage and transmission</li>
                                <li>Role-based access controls</li>
                                <li>Regular security audits</li>
                                <li>Complete activity logging with IP tracking</li>
                            </ul>
                        </div>

                        <!-- Section 10 -->
                        <div id="section-10" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">10</div>
                                <h2 class="text-xl font-bold text-gray-900">Intellectual Property</h2>
                            </div>
                            <p>All content, features, and functionality of our platform are owned by {{ config('app.name') }} and are protected by copyright, trademark, and other intellectual property laws.</p>
                        </div>

                        <!-- Section 11 -->
                        <div id="section-11" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">11</div>
                                <h2 class="text-xl font-bold text-gray-900">Prohibited Activities</h2>
                            </div>
                            <p>You agree not to:</p>
                            <ul class="policy-list">
                                <li>Provide false or misleading information</li>
                                <li>Create multiple accounts with the same email or phone number</li>
                                <li>Attempt to circumvent security measures</li>
                                <li>Use the platform for any illegal purpose</li>
                                <li>Interfere with the proper functioning of the platform</li>
                            </ul>
                        </div>

                        <!-- Section 12 -->
                        <div id="section-12" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">12</div>
                                <h2 class="text-xl font-bold text-gray-900">Electronic Signatures</h2>
                            </div>
                            <p>By submitting your application, you agree that your electronic signature and IP-tracked consent have the same legal effect as a handwritten signature.</p>
                        </div>

                        <!-- Section 13 -->
                        <div id="section-13" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">13</div>
                                <h2 class="text-xl font-bold text-gray-900">Limitation of Liability</h2>
                            </div>
                            <p>To the maximum extent permitted by law, {{ config('app.name') }} shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of our services.</p>
                        </div>

                        <!-- Section 14 -->
                        <div id="section-14" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">14</div>
                                <h2 class="text-xl font-bold text-gray-900">Termination</h2>
                            </div>
                            <p>We reserve the right to suspend or terminate your account and access to our services at any time, with or without notice, for any reason, including violation of these Terms.</p>
                        </div>

                        <!-- Section 15 -->
                        <div id="section-15" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">15</div>
                                <h2 class="text-xl font-bold text-gray-900">Changes to Terms</h2>
                            </div>
                            <p>We may modify these Terms at any time. Continued use of our services after changes constitutes acceptance of the modified Terms.</p>
                        </div>

                        <!-- Section 16 -->
                        <div id="section-16" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">16</div>
                                <h2 class="text-xl font-bold text-gray-900">Governing Law</h2>
                            </div>
                            <p>These Terms shall be governed by and construed in accordance with the laws of [Your Jurisdiction], without regard to its conflict of law provisions.</p>
                        </div>

                        <!-- Section 17 -->
                        <div id="section-17" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">17</div>
                                <h2 class="text-xl font-bold text-gray-900">Dispute Resolution</h2>
                            </div>
                            <p>Any disputes arising from these Terms or your use of our services shall be resolved through [arbitration/mediation/court] in [Your Jurisdiction].</p>
                        </div>

                        <!-- Section 18 -->
                        <div id="section-18" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">18</div>
                                <h2 class="text-xl font-bold text-gray-900">Contact Information</h2>
                            </div>
                            <p>For questions about these Terms and Conditions, please contact us:</p>

                            <div class="highlight-box mt-4 flex flex-col sm:flex-row gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Email</p>
                                        <a href="mailto:legal@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition">
                                            legal@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}
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

                            <!-- Important acknowledgement notice -->
                            <div class="highlight-box amber mt-5 flex items-start gap-3">
                                <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <p class="text-xs text-amber-700 leading-relaxed">
                                    <strong>Important:</strong> By clicking "Create Account & Start Application," you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions and our <a href="{{ route('privacy-policy') }}" class="underline decoration-dotted font-semibold hover:text-amber-900">Privacy Policy</a>.
                                </p>
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
                        <a href="{{ route('privacy-policy') }}" class="hover:text-indigo-600 transition">Privacy Policy</a>
                        <a href="{{ route('terms-and-conditions') }}" class="hover:text-indigo-600 transition font-medium text-indigo-500">Terms & Conditions</a>
                        <a href="{{ url('/') }}" class="hover:text-indigo-600 transition">← Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
