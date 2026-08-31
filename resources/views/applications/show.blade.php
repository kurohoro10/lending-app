{{-- resources/views/applications/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Application {{ $application->application_number }}
            </h2>
            <div class="flex space-x-2">
                @if($application->isEditable())
                    <a href="{{ route('applications.edit', $application) }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent
                              rounded-md font-semibold text-xs text-white uppercase tracking-widest
                              hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Edit Application
                    </a>
                @endif
                @if($application->submitted_at) {{-- or use your submitted check --}}
                    <a href="{{ route('applications.download-confirmation', $application) }}"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent
                            rounded-md font-semibold text-xs text-white uppercase tracking-widest
                            hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                    download>
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        Download Submission
                    </a>
                @endif
                <a href="{{ route('applications.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent
                          rounded-md font-semibold text-xs text-white uppercase tracking-widest
                          hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Back to Applications
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Pending Questions --}}
            @if($application->questions->where('status', 'pending')->count() > 0)
                @include('applications.partials.show.pending-questions')
            @endif

            {{-- Status Overview --}}
            @include('applications.partials.show.status-overview')

            {{-- Loan Details --}}
            @include('applications.partials.show.loan-details')

            {{-- Personal Details --}}
            @if($application->personalDetails)
                @include('applications.partials.show.personal-details')
            @endif

            {{-- Borrower Information --}}
            @if($application->borrowerInformation)
                @include('applications.partials.show.borrower-information')
            @endif

            {{-- Directors / Trustees (Company or Trust only) --}}
            @if($application->borrowerInformation &&
                in_array($application->borrowerInformation->borrower_type, ['company', 'trust']) &&
                $application->borrowerDirectors->count() > 0)
                @include('applications.partials.show.borrower-directors')
            @endif

            {{-- Director Assets & Liabilities --}}
            @if($application->directorAssets->count() > 0 || $application->directorLiabilities->count() > 0)
                @include('applications.partials.show.director-assets-liabilities')
            @endif

            {{-- Company Assets & Liabilities (Company only) --}}
            @if($application->borrowerInformation?->borrower_type === 'company' &&
                ($application->companyAssets->count() > 0 || $application->companyLiabilities->count() > 0))
                @include('applications.partials.show.company-assets-liabilities')
            @endif

            {{-- Accountant Details (Company only) --}}
            @if($application->borrowerInformation?->borrower_type === 'company' &&
                $application->accountantDetail)
                @include('applications.partials.show.accountant-details')
            @endif

            {{-- Residential Addresses --}}
            @if($application->residentialAddresses->count() > 0)
                @include('applications.partials.show.residential-address')
            @endif

            {{-- Employment Details --}}
            @if($application->employmentDetails->count() > 0)
                @include('applications.partials.show.employment-details')
            @endif

            {{-- Living Expenses --}}
            @if($application->livingExpenses->count() > 0)
                @include('applications.partials.show.living-expenses')
            @endif

            {{-- Documents --}}
            @if($application->documents->count() > 0)
                @include('applications.partials.show.documents')
            @endif

            {{-- Questions --}}
            @if($application->questions->count() > 0)
                @include('applications.partials.show.questions')
            @endif

            {{-- Declarations --}}
            @if($application->declarations->count() > 0)
                @include('applications.partials.show.declarations')
            @endif

            {{-- Comments History --}}
            @if($application->comments->count() > 0)
                @include('applications.partials.show.comments')
            @endif

        </div>
    </div>
</x-app-layout>

@if(session('just_submitted'))
<script>
    window.open('/applications/{{ $application->id }}/download-confirmation', '_blank');
</script>
@endif