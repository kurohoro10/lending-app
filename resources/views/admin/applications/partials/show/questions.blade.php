<!-- Questions & Answers Section -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg" id="qa-section">
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">
                Questions & Answers
                <span id="qa-count" class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700">
                    {{ $application->questions->count() }}
                </span>
            </h3>
            <button id="toggle-ask-form-btn"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Ask Question
            </button>
        </div>

        <!-- Toast Notification -->
        <div id="qa-toast" class="hidden mb-4 p-4 rounded-lg text-sm font-medium"></div>

        <!-- Ask Question Form -->
        <div id="ask-question-form" class="hidden mb-6 p-4 bg-indigo-50 rounded-lg border-2 border-indigo-200">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Your Question</label>
                <textarea id="question-input" rows="3"
                          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                          placeholder="Type your question to the client..."></textarea>
                <p id="question-error" class="hidden mt-1 text-sm text-red-600"></p>
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="is-mandatory"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Mark as mandatory</span>
                </label>
                <div class="flex space-x-2">
                    <button type="button" id="cancel-btn"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="button" id="submit-question-btn"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 flex items-center">
                        <span id="submit-question-text">Send Question</span>
                        <svg id="submit-question-spinner" class="hidden ml-2 h-4 w-4 animate-spin" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Questions List -->
        <div id="questions-list" class="space-y-4">
            @forelse($application->questions->sortByDesc('created_at') as $question)
                @include('admin.applications.partials.question.question-item', ['question' => $question])
            @empty
                <div id="no-questions-message" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No questions yet. Click "Ask Question" to start.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
