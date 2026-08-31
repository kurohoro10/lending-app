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
                <span class="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">ZYA Capital</span>
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
                            <a href="#section-1" class="toc-link"><span class="toc-num">1</span> About This Website</a>
                            <a href="#section-2" class="toc-link"><span class="toc-num">2</span> Product Information</a>
                            <a href="#section-3" class="toc-link"><span class="toc-num">3</span> Eligibility & Accuracy</a>
                            <a href="#section-4" class="toc-link"><span class="toc-num">4</span> Third Party Providers</a>
                            <a href="#section-5" class="toc-link"><span class="toc-num">5</span> Third Party Links</a>
                            <a href="#section-6" class="toc-link"><span class="toc-num">6</span> Warranties & Availability</a>
                            <a href="#section-7" class="toc-link"><span class="toc-num">7</span> Limitation of Liability</a>
                            <a href="#section-8" class="toc-link"><span class="toc-num">8</span> Indemnity</a>
                            <a href="#section-9" class="toc-link"><span class="toc-num">9</span> Cookies & Analytics</a>
                            <a href="#section-10" class="toc-link"><span class="toc-num">10</span> Intellectual Property</a>
                            <a href="#section-11" class="toc-link"><span class="toc-num">11</span> Termination</a>
                            <a href="#section-12" class="toc-link"><span class="toc-num">12</span> Jurisdiction</a>
                            <a href="#section-13" class="toc-link"><span class="toc-num">13</span> Changes to Terms</a>
                            <a href="#section-14" class="toc-link"><span class="toc-num">14</span> Contact</a>
                        </nav>

                        <div class="mt-6 pt-5 border-t border-gray-100">
                            <div class="highlight-box green">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-xs text-green-700 font-medium leading-relaxed">Governed by the laws of <strong>New South Wales, Australia</strong>.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Policy Content -->
                <div class="lg:col-span-2 animate-slide-up" style="animation-delay: 0.1s;">
                    <div class="bg-white rounded-3xl shadow-xl p-8 md:p-10 policy-body">

                        <!-- Intro note -->
                        <div class="highlight-box mb-6">
                            <p class="text-sm text-indigo-800 font-medium" style="margin-top:0;">
                                This website is operated by <strong>ZYA Capital Pty Ltd (ABN: 55 695 692 052)</strong> under the domain
                                <a href="https://www.zyacapital.com.au" class="text-indigo-600 underline">https://www.zyacapital.com.au</a>.
                                By accessing or using this Website you agree to be bound by these Terms of Use.
                            </p>
                        </div>

                        <!-- Section 1 -->
                        <div id="section-1" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">1</div>
                                <h2 class="text-xl font-bold text-gray-900">About This Website</h2>
                            </div>
                            <p>
                                Your use of the information, graphics, documents, and materials available on this Website ("Website Material") is governed by these Terms of Use and should be read together with our Privacy Policy, Credit Reporting Policy, and any other policies published on this Website.
                            </p>
                            <p>
                                If you do not agree with these Terms of Use, you should discontinue using this Website immediately.
                            </p>
                        </div>

                        <!-- Section 2 -->
                        <div id="section-2" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">2</div>
                                <h2 class="text-xl font-bold text-gray-900">Product Information and Services</h2>
                            </div>
                            <p>The Website Material may contain general information about commercial lending, business finance, and funding solutions offered by ZYA Capital.</p>
                            <p>Unless expressly stated otherwise:</p>
                            <ul class="policy-list mt-3">
                                <li>The information provided on this Website does not constitute financial, legal, taxation, or investment advice.</li>
                                <li>The information does not constitute an offer or inducement to enter into a legally binding agreement.</li>
                                <li>The information does not form part of the terms and conditions of any financing agreement.</li>
                                <li>All funding and lending arrangements are subject to ZYA Capital's approval criteria, due diligence processes, documentation requirements, and contractual terms.</li>
                            </ul>
                            <p>ZYA Capital reserves the right to approve, decline, or modify any loan or finance application at its sole discretion.</p>
                            <p>Users should seek independent professional advice before acting on any information contained on this Website.</p>
                        </div>

                        <!-- Section 3 -->
                        <div id="section-3" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">3</div>
                                <h2 class="text-xl font-bold text-gray-900">Eligibility and Accuracy of Information</h2>
                            </div>
                            <p>By using this Website or submitting information through this Website, you represent and warrant that:</p>
                            <ul class="policy-list mt-3">
                                <li>All information provided is true, accurate, current, and complete.</li>
                                <li>You do not misrepresent your identity, financial profile, or authority.</li>
                                <li>You will update any information provided if it becomes inaccurate or outdated.</li>
                            </ul>
                            <p>ZYA Capital reserves the right to refuse services or terminate access to the Website where inaccurate, misleading, or incomplete information is provided.</p>
                        </div>

                        <!-- Section 4 -->
                        <div id="section-4" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">4</div>
                                <h2 class="text-xl font-bold text-gray-900">Third Party Service Providers</h2>
                            </div>
                            <p>ZYA Capital may use third party service providers to assist with services including but not limited to:</p>
                            <ul class="policy-list mt-3">
                                <li>Identity verification services</li>
                                <li>Bank statement verification services</li>
                                <li>Credit and financial assessment tools</li>
                                <li>Data processing services</li>
                                <li>Loan application management platforms</li>
                            </ul>
                            <p>By using this Website or submitting an application, you authorise ZYA Capital and its service providers to access, retrieve, and process information from third party sources designated by you, including financial institutions, for the purpose of assessing or facilitating services requested by you.</p>
                            <div class="highlight-box mt-4">
                                <h3 class="text-sm font-bold text-indigo-800 mb-2">You acknowledge that:</h3>
                                <ul class="policy-list">
                                    <li>ZYA Capital and its service providers may act as your authorised agent for retrieving such information.</li>
                                    <li>ZYA Capital does not control third party systems or websites used for such access.</li>
                                    <li>To the extent permitted by law, ZYA Capital disclaims responsibility for any breach of third party terms and conditions arising from your disclosure of such information.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Section 5 -->
                        <div id="section-5" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">5</div>
                                <h2 class="text-xl font-bold text-gray-900">Links to Third Party Websites</h2>
                            </div>
                            <p>This Website may contain links to websites operated by third parties ("Third Party Websites"). ZYA Capital:</p>
                            <ul class="policy-list mt-3">
                                <li>Does not endorse or approve Third Party Websites.</li>
                                <li>Does not control the content or operation of Third Party Websites.</li>
                                <li>Makes no representation regarding the accuracy, completeness, or suitability of information contained on Third Party Websites.</li>
                            </ul>
                            <p>To the extent permitted by law, ZYA Capital disclaims all liability for any loss or damage arising from your use of Third Party Websites.</p>
                            <p>ZYA Capital may receive referral fees or commissions from third party providers where services are introduced through links on this Website.</p>
                        </div>

                        <!-- Section 6 -->
                        <div id="section-6" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">6</div>
                                <h2 class="text-xl font-bold text-gray-900">Warranties and Website Availability</h2>
                            </div>
                            <p>While ZYA Capital makes reasonable efforts to ensure the accuracy of information on this Website, we do not guarantee that Website Material is accurate, complete, or current. All Website Material is provided "as is" and "as available."</p>
                            <p>To the extent permitted by law, ZYA Capital:</p>
                            <ul class="policy-list mt-3">
                                <li>Makes no warranty regarding uninterrupted access to the Website.</li>
                                <li>Does not guarantee that the Website will be free from viruses or malicious code.</li>
                                <li>Reserves the right to modify, remove, or update Website content without notice.</li>
                            </ul>
                            <p>Users should obtain independent professional advice before relying on Website Material.</p>
                        </div>

                        <!-- Section 7 -->
                        <div id="section-7" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">7</div>
                                <h2 class="text-xl font-bold text-gray-900">Limitation of Liability</h2>
                            </div>
                            <p>To the maximum extent permitted by law, ZYA Capital is not liable for any loss, damage, liability, claim, or expense arising out of or in connection with:</p>
                            <ul class="policy-list mt-3">
                                <li>Use of this Website.</li>
                                <li>Reliance on Website Material.</li>
                                <li>Use of Third Party Websites.</li>
                                <li>Inability to access the Website.</li>
                            </ul>
                            <p>This includes, without limitation: indirect or consequential loss, loss of business opportunity, loss of profits, and loss of data.</p>
                            <div class="highlight-box mt-4">
                                <h3 class="text-sm font-bold text-indigo-800 mb-2">Where liability cannot be excluded under Australian law, ZYA Capital's liability is limited, at its option, to:</h3>
                                <ul class="policy-list">
                                    <li>The re-supply of the services; or</li>
                                    <li>The cost of having the services supplied again.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Section 8 -->
                        <div id="section-8" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">8</div>
                                <h2 class="text-xl font-bold text-gray-900">Indemnity</h2>
                            </div>
                            <p>You agree to indemnify and hold harmless ZYA Capital, its directors, officers, employees, contractors, and affiliates from any claims, losses, damages, liabilities, or expenses (including legal costs) arising out of:</p>
                            <ul class="policy-list mt-3">
                                <li>Your breach of these Terms of Use.</li>
                                <li>Misuse of the Website.</li>
                                <li>Violation of any applicable law.</li>
                            </ul>
                        </div>

                        <!-- Section 9 -->
                        <div id="section-9" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">9</div>
                                <h2 class="text-xl font-bold text-gray-900">Cookies and Website Analytics</h2>
                            </div>
                            <p>This Website uses cookies and analytics technologies to improve functionality and user experience. Cookies may collect information such as:</p>
                            <ul class="policy-list mt-3">
                                <li>IP address</li>
                                <li>Browser type</li>
                                <li>Pages visited</li>
                                <li>Referring website</li>
                                <li>Device information</li>
                            </ul>
                            <p>ZYA Capital may use analytics tools such as Google Analytics or similar services to analyse Website traffic and usage patterns. You may disable cookies through your browser settings; however doing so may affect Website functionality.</p>
                        </div>

                        <!-- Section 10 -->
                        <div id="section-10" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">10</div>
                                <h2 class="text-xl font-bold text-gray-900">Intellectual Property</h2>
                            </div>
                            <p>All intellectual property rights associated with this Website and the Website Material are owned or licensed by ZYA Capital Pty Ltd. This includes but is not limited to:</p>
                            <ul class="policy-list mt-3">
                                <li>Trademarks, logos, and branding elements</li>
                                <li>Graphics and written content</li>
                                <li>Website design and software</li>
                            </ul>
                            <p>Except as permitted under the Copyright Act 1968 (Cth), no material on this Website may be reproduced, distributed, transmitted, adapted, or otherwise used without prior written consent from ZYA Capital.</p>
                        </div>

                        <!-- Section 11 -->
                        <div id="section-11" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">11</div>
                                <h2 class="text-xl font-bold text-gray-900">Termination</h2>
                            </div>
                            <p>
                                ZYA Capital reserves the right to terminate or restrict access to this Website at any time without notice. All disclaimers, limitations of liability, and indemnities contained in these Terms of Use will survive termination.
                            </p>
                        </div>

                        <!-- Section 12 -->
                        <div id="section-12" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">12</div>
                                <h2 class="text-xl font-bold text-gray-900">Jurisdiction</h2>
                            </div>
                            <p>
                                These Terms of Use are governed by the laws of New South Wales, Australia. Any disputes arising from the use of this Website will be subject to the exclusive jurisdiction of the courts of New South Wales.
                            </p>
                        </div>

                        <!-- Section 13 -->
                        <div id="section-13" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">13</div>
                                <h2 class="text-xl font-bold text-gray-900">Changes to These Terms</h2>
                            </div>
                            <p>
                                ZYA Capital may amend these Terms of Use at any time by publishing updated terms on this Website. Your continued use of the Website following any changes constitutes acceptance of the updated Terms of Use.
                            </p>
                        </div>

                        <!-- Section 14 -->
                        <div id="section-14" class="policy-section">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="section-number">14</div>
                                <h2 class="text-xl font-bold text-gray-900">Contact</h2>
                            </div>
                            <p>For enquiries regarding these Terms of Use, please contact:</p>

                            <div class="highlight-box mt-4 flex flex-col sm:flex-row gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Email</p>
                                        <a href="mailto:hello@zyacapital.com.au" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition">
                                            hello@zyacapital.com.au
                                        </a>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Website</p>
                                        <a href="https://www.zyacapital.com.au" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition">
                                            www.zyacapital.com.au
                                        </a>
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
                    <p class="mb-4 md:mb-0 text-sm">© {{ date('Y') }} ZYA Capital Pty Ltd (ABN: 55 695 692 052). All rights reserved.</p>
                    <div class="flex gap-6 text-xs text-gray-400">
                        <a href="{{ route('privacy-policy') }}" class="hover:text-indigo-600 transition font-medium text-indigo-500">Privacy Policy</a>
                        <a href="{{ route('terms-and-conditions') }}" class="hover:text-indigo-600 transition">Terms &amp; Conditions</a>
                        <a href="{{ url('/') }}" class="hover:text-indigo-600 transition">← Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>