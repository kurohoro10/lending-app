{{-- resources/views/admin/applications/partials/show/comment.blade.php --}}
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4" id="add-comment-heading">Add Comment</h3>

        {{-- action stays on the form for no-JS fallback.                         --}}
        {{-- JS reads data-action instead and calls fetch() to avoid a page reload --}}
        <form id="comment-form"
              method="POST"
              action="{{ route('admin.comments.store', $application) }}"
              data-action="{{ route('admin.comments.store', $application) }}"
              novalidate
              aria-labelledby="add-comment-heading">
            @csrf
            <div class="space-y-4">

                {{-- Comment type --}}
                <fieldset>
                    <legend class="block text-sm font-medium text-gray-700 mb-2">Comment Type</legend>
                    <div class="mt-1 space-x-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="type" value="internal" checked
                                   class="form-radio text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">
                                Internal <span class="text-gray-400">(Staff only)</span>
                            </span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="type" value="client_visible"
                                   class="form-radio text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Client Visible</span>
                        </label>
                    </div>
                </fieldset>

                {{-- Textarea --}}
                <div>
                    <label for="comment-text" class="block text-sm font-medium text-gray-700 mb-1">
                        Comment <span aria-hidden="true">*</span><span class="sr-only">(required)</span>
                    </label>
                    <textarea id="comment-text"
                              name="comment"
                              rows="3"
                              required
                              placeholder="Type your comment here..."
                              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                              aria-required="true"
                              aria-describedby="comment-field-error"></textarea>
                    <p id="comment-field-error"
                       class="hidden mt-1 text-xs text-red-600"
                       role="alert"
                       aria-live="polite"></p>
                </div>

                {{-- Pin --}}
                <div class="flex items-center">
                    <input type="checkbox" id="is-pinned" name="is_pinned" value="1"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is-pinned" class="ml-2 block text-sm text-gray-900 cursor-pointer">
                        Pin this comment
                    </label>
                </div>

                {{-- Submit --}}
                <div class="flex items-center gap-3">
                    <button type="submit"
                            id="comment-submit-btn"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition">
                        <svg id="comment-spinner"
                             class="hidden animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Add Comment
                    </button>
                    <p id="comment-submit-status"
                       class="text-sm"
                       aria-live="polite"
                       aria-atomic="true"></p>
                </div>

            </div>
        </form>
    </div>
</div>
