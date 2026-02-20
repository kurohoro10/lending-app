<div id="question-card-{{ $question->id }}"
     class="border rounded-lg p-4 transition-all {{ $question->status === 'pending' ? 'border-yellow-300 bg-yellow-50' : 'border-green-300 bg-green-50' }}">
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
        <button data-question-id="{{ $question->id}}"
                class="text-red-600 hover:text-red-800 ml-4 flex-shrink-0">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>

    @if($question->status === 'answered')
        <div class="mt-3 p-3 bg-white rounded border border-green-200">
            <div class="text-sm font-medium text-gray-900 mb-1">A: {{ $question->answer }}</div>
            <div class="text-xs text-gray-500">
                Answered on {{ $question->answered_at->format('d M Y H:i') }} &bull; IP: {{ $question->answer_ip ?? 'N/A' }}
            </div>
        </div>
    @else
        <div class="mt-3 p-3 bg-white rounded border border-yellow-200">
            <p class="text-sm text-gray-600 italic">Waiting for client's response...</p>
        </div>
    @endif
</div>
