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
