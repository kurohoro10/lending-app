{{-- resources/views/admin/applications/partials/show/status.blade.php --}}

<form method="POST"
        action="{{ route('admin.applications.updateStatus', $application) }}"
        class="flex items-end gap-2"
        data-loading-form>
    @csrf
    @method('PATCH')

    <div>
        <label for="status-select" class="block text-sm font-medium text-gray-700">
            Change Status
        </label>
        <select id="status-select"
                name="status"
                required
                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm
                        focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <option value="submitted"                {{ $application->status === 'submitted'                ? 'selected' : '' }}>Submitted</option>
            <option value="under_review"             {{ $application->status === 'under_review'             ? 'selected' : '' }}>Under Review</option>
            <option value="additional_info_required" {{ $application->status === 'additional_info_required' ? 'selected' : '' }}>Additional Info Required</option>
            <option value="approved"                 {{ $application->status === 'approved'                 ? 'selected' : '' }}>Approved</option>
            <option value="declined"                 {{ $application->status === 'declined'                 ? 'selected' : '' }}>Declined</option>
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
        <span class="btn-label">Update Status</span>
    </button>
</form>
