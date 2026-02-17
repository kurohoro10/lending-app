<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Questions from Assessment Team</h3>
        <div class="space-y-4">
            @foreach($application->questions as $question)
            <div class="border rounded-lg p-4 {{ $question->status === 'pending' ? 'border-yellow-300 bg-yellow-50' : 'border-gray-200' }}">
                <div class="flex justify-between items-start mb-2">
                    <div class="text-sm font-medium text-gray-900">
                        {{ $question->question }}
                        @if($question->is_mandatory)
                            <span class="ml-2 text-red-500">*</span>
                        @endif
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full {{ $question->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                        {{ ucfirst($question->status) }}
                    </span>
                </div>

                @if($question->status === 'pending')
                    <form method="POST" action="{{ route('questions.answer', $question) }}" class="mt-3">
                        @csrf
                        <textarea name="answer" rows="3" required
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Type your answer here..."></textarea>
                        <div class="mt-2 flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Submit Answer
                            </button>
                        </div>
                    </form>
                @else
                    <div class="mt-2 p-3 bg-gray-50 rounded">
                        <div class="text-sm text-gray-700">{{ $question->answer }}</div>
                        <div class="mt-1 text-xs text-gray-500">Answered on {{ $question->answered_at->format('d M Y H:i') }}</div>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
