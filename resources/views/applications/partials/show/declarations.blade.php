{{-- resources/views/applications/partials/show/declarations.blade.php --}}
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Declarations
        </h3>
    </div>

    <div class="p-6">
        <ul class="space-y-3" aria-label="Completed declarations">
            @foreach($application->declarations as $declaration)
                <li class="flex items-start gap-3 p-4 bg-green-50 rounded-xl border border-green-100">
                    <svg class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ ucwords(str_replace('_', ' ', $declaration->declaration_type)) }}
                        </p>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Agreed on {{ $declaration->agreed_at->format('d M Y \a\t H:i') }}
                        </p>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
