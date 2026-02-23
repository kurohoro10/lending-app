{{-- resources/views/admin/applications/partials/show/question-card.blade.php --}}
<div id="question-card-{{ $question->id }}"
     class="border rounded-lg p-4 transition-all
            {{ $question->status === 'pending'
                ? 'border-yellow-300 bg-yellow-50'
                : ($question->isUnread() ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-white') }}">

    <div class="flex justify-between items-start mb-3">
        <div class="flex-1">

            {{-- Mark as read button --}}
            @if($question->status === 'answered' && $question->isUnread())
                <button type="button"
                        data-action="mark-as-read"
                        data-question-id="{{ $question->id }}"
                        class="mb-2 text-xs text-green-600 hover:text-green-800 font-medium
                               focus:outline-none focus:ring-2 focus:ring-green-500 rounded"
                        aria-label="Mark answer as read">
                    ✓ Mark as Read
                </button>
            @endif

            {{-- Badges --}}
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="px-2 py-1 text-xs rounded-full
                    {{ $question->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                    {{ ucfirst($question->status) }}
                </span>

                @if($question->is_mandatory)
                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                        Mandatory
                    </span>
                @endif

                @if($question->isUnread())
                    <span data-new-badge
                          class="px-2 py-1 text-xs rounded-full bg-green-500 text-white font-bold animate-pulse"
                          aria-label="New unread answer">
                        NEW
                    </span>
                @endif

                <span class="text-xs text-gray-500">
                    Asked by {{ $question->askedBy->name }}
                </span>
            </div>

            <div class="text-sm font-semibold text-gray-900 mb-1">
                Q: {{ $question->question }}
            </div>
            <div class="text-xs text-gray-500">
                <time datetime="{{ $question->asked_at->toIso8601String() }}">
                    {{ $question->asked_at->format('d M Y H:i') }}
                </time>
            </div>
        </div>

        {{-- Delete button --}}
        <button type="button"
                data-action="delete-question"
                data-question-id="{{ $question->id }}"
                class="text-red-600 hover:text-red-800 ml-4 flex-shrink-0
                       focus:outline-none focus:ring-2 focus:ring-red-500 rounded"
                aria-label="Delete question asked on {{ $question->asked_at->format('d M Y') }}">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd"
                      d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                      clip-rule="evenodd"/>
            </svg>
        </button>
    </div>

    {{-- Answer / pending --}}
    @if($question->status === 'answered')
        <div class="mt-3 p-3 rounded relative
                    {{ $question->isUnread()
                        ? 'bg-white border-2 border-green-300'
                        : 'bg-gray-50 border border-gray-200' }}">

            @if($question->isUnread())
                <div class="absolute -top-2 -right-2" data-new-badge aria-hidden="true">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold
                                 bg-green-500 text-white shadow-lg animate-pulse">
                        NEW
                    </span>
                </div>
            @endif

            <div class="text-sm font-medium text-gray-900 mb-1">
                A: {{ $question->answer }}
            </div>
            <div class="text-xs text-gray-500">
                Answered on
                <time datetime="{{ $question->answered_at->toIso8601String() }}">
                    {{ $question->answered_at->format('d M Y H:i') }}
                </time>
                &bull; IP: {{ $question->answer_ip ?? 'N/A' }}

                @if($question->read_at)
                    <br>
                    <span class="text-gray-400">
                        ✓ Read by {{ $question->readBy->name ?? 'Admin' }} on
                        <time datetime="{{ $question->read_at->toIso8601String() }}">
                            {{ $question->read_at->format('d M Y H:i') }}
                        </time>
                    </span>
                @endif
            </div>
        </div>
    @else
        <div class="mt-3 p-3 bg-white rounded border border-yellow-200">
            <p class="text-sm text-gray-600 italic">Waiting for client's response…</p>
        </div>
    @endif
</div>
