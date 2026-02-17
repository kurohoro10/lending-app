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
            @include('applications.partials.show.status-overview')

            <!-- Loan Details -->
            @include('applications.partials.show.loan-details')

            <!-- Personal Details -->
            @if($application->personalDetails)
                @include('applications.partials.show.personal-details')
            @endif

            <!-- Residential Addresses -->
            @if($application->residentialAddresses->count() > 0)
                @include('applications.partials.show.residential-address')
            @endif

            <!-- Employment Details -->
            @if($application->employmentDetails->count() > 0)
                @include('applications.partials.show.employment-details')
            @endif

            <!-- Living Expenses -->
            @if($application->livingExpenses->count() > 0)
                @include('applications.partials.show.living-expenses')
            @endif

            <!-- Documents -->
            @if($application->documents->count() > 0)
                @include('applications.partials.show.documents')
            @endif

            <!-- Pending Questions -->
            @if($application->questions->where('status', 'pending')->count() > 0)
                @include('applications.partials.show.pending-questions')
            @endif

            <!-- Questions Section -->
            @if($application->questions->count() > 0)
                @include('applications.partials.show.questions')
            @endif

            <!-- Declarations -->
            @if($application->declarations->count() > 0)
                @include('applications.partials.show.declarations')
            @endif

            <!-- Comments History -->
            @if($application->comments->count() > 0)
                @include('applications.partials.show.comments')
            @endif

        </div>
    </div>
</x-app-layout>
