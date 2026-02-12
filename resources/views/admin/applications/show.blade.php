<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Review Application - {{ $application->application_number }}
            </h2>
            <a href="{{ route('admin.applications.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-wrap gap-4">
                        <!-- Change Status -->
                        <form method="POST" action="{{ route('admin.applications.updateStatus', $application) }}" class="flex items-end gap-2">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Change Status</label>
                                <select name="status" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="submitted" {{ $application->status == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="under_review" {{ $application->status == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                    <option value="additional_info_required" {{ $application->status == 'additional_info_required' ? 'selected' : '' }}>Additional Info Required</option>
                                    <option value="approved" {{ $application->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="declined" {{ $application->status == 'declined' ? 'selected' : '' }}>Declined</option>
                                </select>
                            </div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Update Status
                            </button>
                        </form>

                        <!-- Assign To -->
                        <form method="POST" action="{{ route('admin.applications.assign', $application) }}" class="flex items-end gap-2">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Assign To</label>
                                <select name="assigned_to" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select Assessor</option>
                                    @foreach(\App\Models\User::role(['admin', 'assessor'])->get() as $assessor)
                                        <option value="{{ $assessor->id }}" {{ $application->assigned_to == $assessor->id ? 'selected' : '' }}>{{ $assessor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Assign
                            </button>
                        </form>

                        <!-- Export PDF -->
                        <div class="flex items-end">
                            <a href="{{ route('admin.applications.exportPdf', $application) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                Export PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Overview -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Application Overview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Status:</span>
                            @php
                                $statusColors = ['draft' => 'gray', 'submitted' => 'blue', 'under_review' => 'yellow', 'approved' => 'green', 'declined' => 'red'];
                                $color = $statusColors[$application->status] ?? 'gray';
                            @endphp
                            <p class="mt-1">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                                    {{ ucwords(str_replace('_', ' ', $application->status)) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Loan Amount:</span>
                            <p class="mt-1 text-xl font-semibold text-gray-900">${{ number_format($application->loan_amount, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Term:</span>
                            <p class="mt-1 text-xl font-semibold text-gray-900">{{ $application->term_months }} months</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Submitted:</span>
                            <p class="mt-1 text-sm text-gray-900">{{ $application->submitted_at ? $application->submitted_at->format('d M Y H:i') : 'Not submitted' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Details -->
            @if($application->personalDetails)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Full Name:</span>
                            <p class="mt-1 text-sm text-gray-900">{{ $application->personalDetails->full_name }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Email:</span>
                            <p class="mt-1 text-sm text-gray-900">{{ $application->personalDetails->email }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Mobile:</span>
                            <p class="mt-1 text-sm text-gray-900">{{ $application->personalDetails->mobile_phone }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Marital Status:</span>
                            <p class="mt-1 text-sm text-gray-900">{{ ucwords($application->personalDetails->marital_status) }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Dependants:</span>
                            <p class="mt-1 text-sm text-gray-900">{{ $application->personalDetails->number_of_dependants }}</p>
                        </div>
                        @if($application->personalDetails->spouse_name)
                        <div>
                            <span class="text-sm font-medium text-gray-500">Spouse:</span>
                            <p class="mt-1 text-sm text-gray-900">{{ $application->personalDetails->spouse_name }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Employment & Income -->
            @if($application->employmentDetails->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Employment & Income</h3>
                    @foreach($application->employmentDetails as $employment)
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Type:</span>
                                <p class="mt-1 text-sm text-gray-900">{{ strtoupper($employment->employment_type) }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Employer:</span>
                                <p class="mt-1 text-sm text-gray-900">{{ $employment->employer_business_name }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Position:</span>
                                <p class="mt-1 text-sm text-gray-900">{{ $employment->position }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Annual Income:</span>
                                <p class="mt-1 text-sm font-semibold text-indigo-600">${{ number_format($employment->getAnnualIncome(), 2) }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Monthly Income:</span>
                                <p class="mt-1 text-sm font-semibold text-indigo-600">${{ number_format($employment->getMonthlyIncome(), 2) }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Living Expenses with Verification -->
            @if($application->livingExpenses->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Living Expenses</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Expense</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Client Declared</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Verified</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Frequency</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($application->livingExpenses as $expense)
                                <tr>
                                    <td class="px-4 py-2 text-sm">
                                        <div class="font-medium text-gray-900">{{ $expense->expense_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $expense->expense_category }}</div>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-right">${{ number_format($expense->client_declared_amount, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-right">
                                        {{ $expense->verified_amount ? '$'.number_format($expense->verified_amount, 2) : '-' }}
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ ucfirst($expense->frequency) }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($expense->is_verified)
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Verified</span>
                                        @else
                                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        @if(!$expense->is_verified)
                                        <button onclick="showVerifyModal({{ $expense->id }})" class="text-indigo-600 hover:text-indigo-900 text-sm">Verify</button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="bg-gray-50 font-semibold">
                                    <td colspan="1" class="px-4 py-2 text-sm text-right">Total Monthly:</td>
                                    <td class="px-4 py-2 text-sm text-right">${{ number_format($application->getTotalLivingExpensesMonthly(), 2) }}</td>
                                    <td colspan="4"></td>
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
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Documents</h3>
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
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 text-xs rounded-full {{ $document->status == 'approved' ? 'bg-green-100 text-green-800' : ($document->status == 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ ucfirst($document->status) }}
                                </span>
                                <a href="{{ route('documents.download', $document) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Download</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Add Comment Section -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add Comment</h3>
                    <form method="POST" action="{{ route('admin.comments.store', $application) }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Comment Type</label>
                                <div class="mt-2 space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="type" value="internal" checked class="form-radio">
                                        <span class="ml-2">Internal (Staff only)</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="type" value="client_visible" class="form-radio">
                                        <span class="ml-2">Client Visible</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <textarea name="comment" rows="3" required placeholder="Type your comment here..." class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_pinned" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label class="ml-2 block text-sm text-gray-900">Pin this comment</label>
                            </div>
                            <div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    Add Comment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Comments History -->
            @if($application->comments->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Comments History</h3>
                    <div class="space-y-4">
                        @foreach($application->comments->sortByDesc('created_at') as $comment)
                        <div class="border-l-4 {{ $comment->is_pinned ? 'border-yellow-400' : 'border-gray-200' }} pl-4 py-2">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium text-gray-900">{{ $comment->user->name }}</span>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $comment->type == 'internal' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ ucfirst(str_replace('_', ' ', $comment->type)) }}
                                        </span>
                                        @if($comment->is_pinned)
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pinned</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">{{ $comment->comment }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $comment->created_at->format('d M Y H:i') }} • IP: {{ $comment->ip_address }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Electronic Signatures -->
            @php
                $finalSignature = $application->declarations()
                    ->where('declaration_type', 'final_submission')
                    ->latest()
                    ->first();
            @endphp

            @if($finalSignature)
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Electronic Signature</h3>

                <div class="border-2 border-gray-300 rounded-lg p-6 bg-gray-50">
                    @if($finalSignature->signature_type === 'typed')
                        <p class="text-3xl mb-4" style="font-family: 'Brush Script MT', cursive; color: #1F2937;">
                            {{ $finalSignature->signature_data }}
                        </p>
                    @else
                        <img src="{{ $finalSignature->signature_data }}" alt="Signature" class="max-w-md">
                    @endif

                    <div class="mt-4 pt-4 border-t border-gray-300 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-600">Signed by:</p>
                            <p class="font-semibold">{{ $finalSignature->signatory_name }}</p>
                        </div>
                        @if($finalSignature->signatory_position)
                        <div>
                            <p class="text-gray-600">Position:</p>
                            <p class="font-semibold">{{ $finalSignature->signatory_position }}</p>
                        </div>
                        @endif
                        <div>
                            <p class="text-gray-600">Date & Time:</p>
                            <p class="font-semibold">{{ $finalSignature->signature_timestamp->format('d/m/Y h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">IP Address:</p>
                            <p class="font-semibold">{{ $finalSignature->agreement_ip }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Activity Log -->
            @if($application->activityLogs->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Activity Log</h3>
                    <div class="space-y-2">
                        @foreach($application->activityLogs->sortByDesc('created_at')->take(10) as $log)
                        <div class="flex items-start space-x-3 text-sm">
                            <span class="text-gray-500">{{ $log->created_at->format('d M Y H:i') }}</span>
                            <span class="font-medium text-gray-900">{{ $log->user->name ?? 'System' }}</span>
                            <span class="text-gray-600">{{ $log->description }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
