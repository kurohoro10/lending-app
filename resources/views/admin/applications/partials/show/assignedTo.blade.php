{{-- resources/views/admin/applications/partials/show/assignedTo.blade.php --}}
@if(auth()->user()->hasRole('admin'))
    @if(in_array($application->status, ['approved', 'declined']))
        {{-- Show read-only for approved/declined --}}
        <div class="flex items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Assigned To
                </label>
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 border border-gray-300 rounded-md">
                    <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-semibold text-gray-700">
                        {{ $application->assignedTo->name ?? 'Unassigned' }}
                    </span>
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true" title="Assignment locked">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Cannot reassign {{ $application->status }} applications
                </p>
            </div>
        </div>
    @else
        {{-- Admins can assign for other statuses --}}
        <form method="POST"
            action="{{ route('admin.applications.assign', $application) }}"
            class="flex items-end gap-2"
            data-loading-form>
            @csrf
            @method('PATCH')
            <div>
                <label for="assigned-select" class="block text-sm font-medium text-gray-700">
                    Assign To
                    @if($application->status === 'submitted')
                        <span class="text-xs text-indigo-600 font-normal">(will change status to "Under Review")</span>
                    @endif
                </label>
                <select id="assigned-select"
                        name="assigned_to"
                        required
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Select Assessor</option>
                    @foreach(\App\Models\User::role(['admin', 'assessor'])->get() as $assessor)
                        <option value="{{ $assessor->id }}"
                                {{ $application->assigned_to == $assessor->id ? 'selected' : '' }}>
                            {{ $assessor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="loading-btn inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 border border-transparent
                        rounded-md font-semibold text-xs text-white uppercase tracking-widest
                        hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                        disabled:opacity-60 disabled:cursor-not-allowed transition">
                <svg class="btn-spinner hidden animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span class="btn-label">Assign</span>
            </button>
        </form>
    @endif
@else
    {{-- Assessors see read-only assignment info --}}
    <div class="flex items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Assigned To
            </label>
            @if($application->assigned_to === auth()->id())
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 border border-green-200 rounded-md">
                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-semibold text-green-900">
                        You ({{ auth()->user()->name }})
                    </span>
                </div>
            @else
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-200 rounded-md">
                    <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-semibold text-indigo-900">
                        {{ $application->assignedTo->name ?? 'Unassigned' }}
                    </span>
                </div>
            @endif
        </div>
    </div>
@endif
