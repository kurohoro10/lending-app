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
