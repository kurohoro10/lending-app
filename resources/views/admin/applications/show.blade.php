{{-- resources/views/admin/applications/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Review Application — {{ $application->application_number }}
                </h2>
                @if($application->personalDetails)
                    @php $pd = $application->personalDetails; @endphp
                    <h3 class="mt-1 text-xl text-gray-500">
                        {{ trim(collect([
                            $pd->user->first_name,
                            $pd->user->middle_name ?? null,
                            $pd->user->last_name,
                            $pd->user->name_extension ?? null,
                        ])->filter()->implode(' ')) }}
                    </h3>
                @endif
            </div>
            <a href="{{ route('admin.applications.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent
                      rounded-md font-semibold text-xs text-white uppercase tracking-widest
                      hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Main two-column layout: content left, sticky quick-actions right --}}
            <div class="lg:grid lg:grid-cols-[minmax(0,1fr)_320px] lg:gap-6 lg:items-start">

                {{-- Left column: all application content --}}
                <div class="space-y-6 min-w-0">

                    {{-- Workflow Tabs --}}
                    @include('admin.applications.partials.show.workflow-tabs')

                    {{-- Application Overview --}}
                    @include('admin.applications.partials.show.application-overview')

                    {{-- Personal Details --}}
                    @if($application->personalDetails)
                        @include('admin.applications.partials.show.personal-details')
                    @endif

                    {{-- Borrower Information --}}
                    @if($application->borrowerInformation)
                        @include('admin.applications.partials.show.borrower-information')
                    @endif

                    {{-- Directors / Trustees --}}
                    @if($application->borrowerInformation &&
                        in_array($application->borrowerInformation->borrower_type, ['company', 'trust']) &&
                        $application->borrowerDirectors->count() > 0)
                        @include('admin.applications.partials.show.borrower-directors')
                    @endif

                    {{-- Director Assets & Liabilities --}}
                    @if($application->directorAssets->count() > 0 || $application->directorLiabilities->count() > 0)
                        @include('admin.applications.partials.show.director-assets-liabilities')
                    @endif

                    {{-- Company Assets & Liabilities --}}
                    @if($application->borrowerInformation?->borrower_type === 'company' &&
                        ($application->companyAssets->count() > 0 || $application->companyLiabilities->count() > 0))
                        @include('admin.applications.partials.show.company-assets-liabilities')
                    @endif

                    {{-- Accountant Details --}}
                    @if($application->borrowerInformation?->borrower_type === 'company' &&
                        $application->accountantDetail)
                        @include('admin.applications.partials.show.accountant-details')
                    @endif

                    {{-- Employment & Income --}}
                    @if($application->employmentDetails->count() > 0)
                        @include('admin.applications.partials.show.employment-details')
                    @endif

                    {{-- Living Expenses --}}
                    @if($application->livingExpenses->count() > 0)
                        @include('admin.applications.partials.show.living-expenses')
                    @endif

                    {{-- Documents --}}
                    @if($application->documents->count() > 0)
                        @include('admin.applications.partials.show.documents')
                    @endif

                    {{-- Questions --}}
                    @include('admin.applications.partials.show.questions')

                    {{-- Add Comment --}}
                    @include('admin.applications.partials.show.comment')

                    {{-- Comments History --}}
                    @if($application->comments->count() > 0)
                        @include('admin.applications.partials.show.comment-history')
                    @endif

                    {{-- Electronic Signatures --}}
                    @php
                        $finalSignature = $application->declarations()
                            ->where('declaration_type', 'final_submission')
                            ->latest()
                            ->first();
                    @endphp
                    @if($finalSignature)
                        @include('admin.applications.partials.show.final-signature')
                    @endif

                    {{-- Activity Log --}}
                    @if($application->activity_logs_count > 0)
                        @include('admin.applications.partials.show.activity-log')
                    @endif

                </div>

                {{-- Right column: sticky Quick Actions panel --}}
                <div class="mt-6 lg:mt-0 lg:sticky lg:top-6">
                    @include('admin.applications.partials.show.quick-actions')
                </div>

            </div>

            {{-- Expense Calculator Modal --}}
            @include('admin.applications.partials.show.expense-calculator-modal', ['application' => $application])

            {{-- Approval / Decline Letter Modal --}}
            @include('admin.applications.partials.show.approval-decline-modal', ['application' => $application])

        </div>
    </div>
</x-app-layout>