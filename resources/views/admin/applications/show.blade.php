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
            @include('admin.applications.partials.show.quick-actions')

            <!-- Application Overview -->
            @include('admin.applications.partials.show.application-overview')

            <!-- Personal Details -->
            @if($application->personalDetails)
                @include('admin.applications.partials.show.personal-details')
            @endif

            <!-- Employment & Income -->
            @if($application->employmentDetails->count() > 0)
                @include('admin.applications.partials.show.employment-details')
            @endif

            <!-- Living Expenses with Verification -->
            @if($application->livingExpenses->count() > 0)
                @include('admin.applications.partials.show.living-expenses')
            @endif

            <!-- Documents -->
            @if($application->documents->count() > 0)
                @include('admin.applications.partials.show.documents')
            @endif

            <!-- Questions & Answers Section -->
            @include('admin.applications.partials.show.questions')

            <!-- Add Comment Section -->
            @include('admin.applications.partials.show.comment')

            <!-- Comments History -->
            @if($application->comments->count() > 0)
                @include('admin.applications.partials.show.comment-history')
            @endif

            <!-- Electronic Signatures -->
            @php
                $finalSignature = $application->declarations()
                    ->where('declaration_type', 'final_submission')
                    ->latest()
                    ->first();
            @endphp

            @if($finalSignature)
                @include('admin.applications.partials.show.final-signature')
            @endif

            <!-- Activity Log -->
            @if($application->activityLogs->count() > 0)
                @include('admin.applications.partials.show.activity-log')
            @endif

        </div>
    </div>
</x-app-layout>
