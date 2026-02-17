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
            @include('admin.applications.partials.quick-actions')

            <!-- Application Overview -->
            @include('admin.applications.partials.application-overview')

            <!-- Personal Details -->
            @if($application->personalDetails)
                @include('admin.applications.partials.personal-details')
            @endif

            <!-- Employment & Income -->
            @if($application->employmentDetails->count() > 0)
                @include('admin.applications.partials.employment-details')
            @endif

            <!-- Living Expenses with Verification -->
            @if($application->livingExpenses->count() > 0)
                @include('admin.applications.partials.living-expenses')
            @endif

            <!-- Documents -->
            @if($application->documents->count() > 0)
                @include('admin.applications.partials.documents')
            @endif

            <!-- Questions & Answers Section -->
            @include('admin.applications.partials.questions')

            <!-- Add Comment Section -->
            @include('admin.applications.partials.comment')

            <!-- Comments History -->
            @if($application->comments->count() > 0)
                @include('admin.applications.partials.comment-history')
            @endif

            <!-- Electronic Signatures -->
            @php
                $finalSignature = $application->declarations()
                    ->where('declaration_type', 'final_submission')
                    ->latest()
                    ->first();
            @endphp

            @if($finalSignature)
                @include('admin.applications.partials.final-signature')
            @endif

            <!-- Activity Log -->
            @if($application->activityLogs->count() > 0)
                @include('admin.applications.partials.activity-log')
            @endif

        </div>
    </div>
</x-app-layout>
