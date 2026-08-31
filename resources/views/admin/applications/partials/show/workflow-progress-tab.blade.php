{{-- In Progress Tab Content --}}
@php use App\Models\Application; @endphp

<div class="space-y-6">
    <div>
        <h3 class="text-sm font-semibold text-gray-900 mb-1">Workflow Progress</h3>
        <p class="text-xs text-gray-500 mb-4">Click a status below to move the application to that stage.</p>

        <div class="space-y-2">
            @foreach(Application::ADMIN_TABS['in_progress'] as $status)
                @php
                    $statusLabel        = Application::statusLabel($status);
                    $currentWorkflowIdx = array_search($application->status, Application::STATUS_WORKFLOW);
                    $statusWorkflowIdx  = array_search($status, Application::STATUS_WORKFLOW);

                    $isCompleted = $statusWorkflowIdx !== false && $currentWorkflowIdx !== false && $statusWorkflowIdx < $currentWorkflowIdx;
                    $isCurrent   = $application->status === $status;
                    $isWithdrawn = false;
                    $isUpcoming  = $statusWorkflowIdx !== false && $currentWorkflowIdx !== false && $statusWorkflowIdx > $currentWorkflowIdx;

                    // Clickable: upcoming forward steps, or Withdrawn (unless app is already locked)
                    $isClickable = !$isCurrent && !$application->isLocked() && ($isUpcoming || $isWithdrawn);
                @endphp

                @if($isClickable)
                    <form method="POST"
                          action="{{ route('admin.applications.updateStatus', $application) }}"
                          data-loading-form
                          class="group"
                          @if($isWithdrawn)
                              onsubmit="return confirm('Are you sure you want to withdraw this application? This cannot be undone.');"
                          @endif>
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $status }}">

                        <button type="submit"
                                class="w-full flex items-center text-left rounded-lg border px-3 py-2.5 cursor-pointer
                                       transition focus:outline-none focus:ring-2 focus:ring-indigo-400
                                       {{ $isWithdrawn
                                           ? 'border-slate-200 hover:border-slate-400 hover:bg-slate-50'
                                           : 'border-gray-200 hover:border-indigo-400 hover:bg-indigo-50' }}">

                            <div class="flex-shrink-0 w-8 h-8 rounded-full border-2 flex items-center justify-center mr-3
                                        {{ $isWithdrawn ? 'border-slate-300 group-hover:border-slate-500' : 'border-gray-300 group-hover:border-indigo-500' }}">
                                @if($isWithdrawn)
                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                @else
                                    <div class="w-2 h-2 rounded-full bg-gray-300 group-hover:bg-indigo-500"></div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium {{ $isWithdrawn ? 'text-gray-600 group-hover:text-slate-800' : 'text-gray-600 group-hover:text-indigo-700' }}">
                                    {{ $statusLabel }}
                                </p>
                                <p class="text-xs {{ $isWithdrawn ? 'text-slate-400' : 'text-gray-400 group-hover:text-indigo-500' }}">
                                    {{ $isWithdrawn ? 'Cancel application' : 'Click to advance' }}
                                </p>
                            </div>

                            <svg class="w-4 h-4 ml-2 flex-shrink-0 {{ $isWithdrawn ? 'text-slate-300 group-hover:text-slate-500' : 'text-gray-300 group-hover:text-indigo-500' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </form>

                @else
                    <div class="flex items-center px-3 py-2.5 rounded-lg
                                {{ $isCurrent ? 'bg-indigo-50 border border-indigo-200' : 'border border-transparent' }}">

                        @if($isCompleted)
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        @elseif($isCurrent && $isWithdrawn)
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-500 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        @elseif($isCurrent)
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center animate-pulse mr-3">
                                <div class="w-3 h-3 rounded-full bg-white"></div>
                            </div>
                        @else
                            <div class="flex-shrink-0 w-8 h-8 rounded-full border-2 border-gray-200 flex items-center justify-center mr-3">
                                <div class="w-2 h-2 rounded-full bg-gray-200"></div>
                            </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium
                               {{ $isCurrent && !$isWithdrawn ? 'text-indigo-700'
                                   : ($isCurrent && $isWithdrawn ? 'text-slate-800'
                                   : ($isCompleted ? 'text-green-700' : 'text-gray-400')) }}">
                                {{ $statusLabel }}
                            </p>
                            @if($isCurrent && !$isWithdrawn)
                                <p class="text-xs text-indigo-600 font-semibold">Current Status</p>
                            @elseif($isCurrent && $isWithdrawn)
                                <p class="text-xs text-slate-500 font-semibold">Withdrawn</p>
                            @elseif($isCompleted)
                                <p class="text-xs text-green-600">Completed</p>
                            @endif
                        </div>

                        @if($isCurrent && !$isWithdrawn)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 ml-2">
                                In Progress
                            </span>
                        @elseif($isCurrent && $isWithdrawn)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 ml-2">
                                Withdrawn
                            </span>
                        @elseif($isCompleted)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">
                                Done
                            </span>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-200">
        <div class="text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $application->getWorkflowStep() ?? '—' }}</p>
            <p class="text-xs text-gray-500 mt-1">of 3 steps</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $application->assignedTo?->name ? 'Yes' : 'No' }}</p>
            <p class="text-xs text-gray-500 mt-1">Assigned</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $application->isReturnable() ? 'Yes' : 'No' }}</p>
            <p class="text-xs text-gray-500 mt-1">Returnable</p>
        </div>
    </div>
</div>