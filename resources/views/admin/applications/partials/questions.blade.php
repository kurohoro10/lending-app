<!-- Questions & Answers Section -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Questions & Answers</h3>
            <button onclick="document.getElementById('ask-question-form').classList.toggle('hidden')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Ask Question
            </button>
        </div>

        <!-- Ask Question Form (Hidden by default) -->
        <div id="ask-question-form" class="hidden mb-6 p-4 bg-indigo-50 rounded-lg border-2 border-indigo-200">
            <form method="POST" action="{{ route('admin.questions.store', $application) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Your Question</label>
                    <textarea name="question" rows="3" required
                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="Type your question to the client..."></textarea>
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_mandatory" value="1"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Mark as mandatory</span>
                    </label>
                    <div class="flex space-x-2">
                        <button type="button" onclick="document.getElementById('ask-question-form').classList.add('hidden')"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                            Send Question
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Questions List -->
        @if($application->questions->count() > 0)
            <div class="space-y-4">
                @foreach($application->questions->sortByDesc('created_at') as $question)
                <div class="border rounded-lg p-4 {{ $question->status === 'pending' ? 'border-yellow-300 bg-yellow-50' : 'border-green-300 bg-green-50' }}">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="px-2 py-1 text-xs rounded-full {{ $question->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($question->status) }}
                                </span>
                                @if($question->is_mandatory)
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Mandatory</span>
                                @endif
                                <span class="text-xs text-gray-500">Asked by {{ $question->askedBy->name }}</span>
                            </div>
                            <div class="text-sm font-semibold text-gray-900 mb-1">Q: {{ $question->question }}</div>
                            <div class="text-xs text-gray-500">{{ $question->asked_at->format('d M Y H:i') }}</div>
                        </div>
                        <form method="POST" action="{{ route('admin.questions.destroy', $question) }}"
                              onsubmit="return confirm('Are you sure you want to delete this question?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                    @if($question->status === 'answered')
                        <div class="mt-3 p-3 bg-white rounded border border-green-200">
                            <div class="text-sm font-medium text-gray-900 mb-1">A: {{ $question->answer }}</div>
                            <div class="text-xs text-gray-500">Answered on {{ $question->answered_at->format('d M Y H:i') }} - IP Adress: {{ $question->answer_ip ?? 'N/A'}}</div>
                        </div>
                    @else
                        <div class="mt-3 p-3 bg-white rounded border border-yellow-200">
                            <p class="text-sm text-gray-600 italic">Waiting for client's response...</p>
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500">No questions yet. Click "Ask Question" to start.</p>
            </div>
        @endif
    </div>
</div>
