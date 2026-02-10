<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Application {{ $application->application_number }}
            </h2>
            <div class="flex space-x-2">
                @if($application->isEditable())
                    <a href="{{ route('applications.edit', $application) }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Edit Application
                    </a>
                @endif
                <a href="{{ route('applications.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Back to Applications
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Status Overview -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            @php
                                $statusColors = [
                                    'draft' => 'gray',
                                    'submitted' => 'blue',
                                    'under_review' => 'yellow',
                                    'additional_info_required' => 'orange',
                                    'approved' => 'green',
                                    'declined' => 'red',
                                ];
                                $color = $statusColors[$application->status] ?? 'gray';
                            @endphp
                            <p class="mt-2">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                                    {{ ucwords(str_replace('_', ' ', $application->status)) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Loan Amount</h3>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">
                                ${{ number_format($application->loan_amount, 2) }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Term</h3>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">
                                {{ $application->term_months }} months
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Submitted</h3>
                            <p class="mt-2 text-sm text-gray-900">
                                {{ $application->submitted_at ? $application->submitted_at->format('d M Y') : 'Not yet submitted' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loan Details -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Loan Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Purpose:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $application->loan_purpose)) }}</span>
                        </div>
                        @if($application->loan_purpose_details)
                        <div class="col-span-2">
                            <span class="text-sm font-medium text-gray-500">Details:</span>
                            <p class="mt-1 text-sm text-gray-900">{{ $application->loan_purpose_details }}</p>
                        </div>
                        @endif
                        @if($application->security_type)
                        <div>
                            <span class="text-sm font-medium text-gray-500">Security:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ ucwords($application->security_type) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Personal Details -->
            @if($application->personalDetails)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Full Name:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ $application->personalDetails->full_name }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Email:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ $application->personalDetails->email }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Mobile:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ $application->personalDetails->mobile_phone }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Marital Status:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ ucwords($application->personalDetails->marital_status) }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Dependants:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ $application->personalDetails->number_of_dependants }}</span>
                        </div>
                        @if($application->personalDetails->spouse_name)
                        <div>
                            <span class="text-sm font-medium text-gray-500">Spouse Name:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ $application->personalDetails->spouse_name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Residential Addresses -->
            @if($application->residentialAddresses->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Residential History</h3>
                    <div class="space-y-4">
                        @foreach($application->residentialAddresses->sortBy('address_type') as $address)
                        <div class="border-l-4 border-indigo-400 pl-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ ucwords(str_replace('_', ' ', $address->address_type)) }}
                            </div>
                            <div class="mt-1 text-sm text-gray-600">
                                {{ $address->full_address }}
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                {{ $address->start_date->format('M Y') }} - {{ $address->end_date ? $address->end_date->format('M Y') : 'Present' }}
                                ({{ $address->months_at_address }} months)
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Employment Details -->
            @if($application->employmentDetails->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Employment & Income</h3>
                    @foreach($application->employmentDetails as $employment)
                    <div class="mb-4 last:mb-0">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Employment Type:</span>
                                <span class="ml-2 text-sm text-gray-900">{{ strtoupper($employment->employment_type) }}</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Employer:</span>
                                <span class="ml-2 text-sm text-gray-900">{{ $employment->employer_business_name }}</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Position:</span>
                                <span class="ml-2 text-sm text-gray-900">{{ $employment->position }}</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Annual Income:</span>
                                <span class="ml-2 text-sm font-semibold text-gray-900">${{ number_format($employment->getAnnualIncome(), 2) }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Living Expenses -->
            @if($application->livingExpenses->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Living Expenses</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Expense</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Frequency</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Monthly</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($application->livingExpenses as $expense)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $expense->expense_category }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $expense->expense_name }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-900">${{ number_format($expense->client_declared_amount, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-600">{{ ucfirst($expense->frequency) }}</td>
                                    <td class="px-4 py-2 text-sm text-right font-medium text-gray-900">${{ number_format($expense->getMonthlyAmount(), 2) }}</td>
                                </tr>
                                @endforeach
                                <tr class="bg-gray-50">
                                    <td colspan="4" class="px-4 py-2 text-sm font-semibold text-gray-900 text-right">Total Monthly:</td>
                                    <td class="px-4 py-2 text-sm font-bold text-gray-900 text-right">${{ number_format($application->getTotalLivingExpensesMonthly(), 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Documents -->
            @if($application->documents->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Uploaded Documents</h3>
                    <div class="space-y-2">
                        @foreach($application->documents as $document)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $document->original_filename }}</div>
                                    <div class="text-xs text-gray-500">{{ $document->document_category }} • {{ $document->file_size_human }} • {{ $document->created_at->format('d M Y') }}</div>
                                </div>
                            </div>
                            <a href="{{ route('documents.download', $document) }}"
                               class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                Download
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Pending Questions -->
            @if($application->questions->where('status', 'pending')->count() > 0)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">You have {{ $application->questions->where('status', 'pending')->count() }} pending question(s)</h3>
                        <p class="mt-2 text-sm text-yellow-700">Please scroll down to answer the questions from our assessment team.</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Questions Section -->
            @if($application->questions->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Questions from Assessment Team</h3>
                    <div class="space-y-4">
                        @foreach($application->questions as $question)
                        <div class="border rounded-lg p-4 {{ $question->status === 'pending' ? 'border-yellow-300 bg-yellow-50' : 'border-gray-200' }}">
                            <div class="flex justify-between items-start mb-2">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $question->question }}
                                    @if($question->is_mandatory)
                                        <span class="ml-2 text-red-500">*</span>
                                    @endif
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full {{ $question->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($question->status) }}
                                </span>
                            </div>

                            @if($question->status === 'pending')
                                <form method="POST" action="{{ route('questions.answer', $question) }}" class="mt-3">
                                    @csrf
                                    <textarea name="answer" rows="3" required
                                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                              placeholder="Type your answer here..."></textarea>
                                    <div class="mt-2 flex justify-end">
                                        <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                            Submit Answer
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="mt-2 p-3 bg-gray-50 rounded">
                                    <div class="text-sm text-gray-700">{{ $question->answer }}</div>
                                    <div class="mt-1 text-xs text-gray-500">Answered on {{ $question->answered_at->format('d M Y H:i') }}</div>
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Declarations -->
            @if($application->declarations->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Declarations</h3>
                    <div class="space-y-3">
                        @foreach($application->declarations as $declaration)
                        <div class="flex items-start space-x-3">
                            <svg class="h-5 w-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $declaration->declaration_type)) }}</div>
                                <div class="text-xs text-gray-500 mt-1">Agreed on {{ $declaration->agreed_at->format('d M Y H:i') }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Comments History -->
            @if($application->comments->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Comments History</h3>
                    <div class="space-y-4">
                        @foreach($application->comments->sortByDesc('created_at') as $comment)
                            @if($comment->type !== 'internal')
                                <div class="border-l-4 {{ $comment->is_pinned ? 'border-yellow-400' : 'border-gray-200' }} pl-4 py-2">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-medium text-gray-900">{{ $comment->user->name }}</span>
                                                @if($comment->is_pinned)
                                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pinned</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-sm text-gray-600">{{ $comment->comment }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $comment->created_at->format('d M Y H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
