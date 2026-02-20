{{-- resources/views/admin/settings/partials/group.blade.php --}}
@php
    $groupFields = collect($fields)->filter(fn($f) => $f['group'] === $groupKey);
    $headingId   = "settings-{$groupKey}-heading";
@endphp

<section aria-labelledby="{{ $headingId }}"
         class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">

    {{-- Card header --}}
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center" aria-hidden="true">
            @if($icon === 'phone')
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            @elseif($icon === 'mail')
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            @else
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            @endif
        </div>
        <div>
            <h3 id="{{ $headingId }}" class="text-sm font-semibold text-gray-900">{{ $groupLabel }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ $groupHint }}</p>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST"
          action="{{ route('admin.settings.update', $groupKey) }}"
          aria-labelledby="{{ $headingId }}"
          novalidate>
        @csrf
        @method('PATCH')

        <div class="px-6 py-5 space-y-5">
            @foreach($groupFields as $key => $field)
                @php $fieldId = "field-{$key}"; $hintId = "hint-{$key}"; @endphp

                <div>
                    <label for="{{ $fieldId }}"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $field['label'] }}
                        @if($field['is_secret'])
                            <span class="ml-1 text-xs font-normal text-gray-400" aria-label="sensitive value">(secret)</span>
                        @endif
                    </label>

                    @if($field['type'] === 'select')
                        <select id="{{ $fieldId }}"
                                name="{{ $key }}"
                                class="block w-full sm:max-w-xs rounded-md border-gray-300 shadow-sm text-sm
                                       focus:ring-indigo-500 focus:border-indigo-500"
                                aria-describedby="{{ $hintId }}">
                            @foreach($field['options'] as $val => $label)
                                <option value="{{ $val }}"
                                    {{ ($settings[$key] ?? '') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                    @else
                        <div class="relative sm:max-w-md">
                            <input type="{{ $field['type'] === 'password' ? 'password' : $field['type'] }}"
                                   id="{{ $fieldId }}"
                                   name="{{ $key }}"
                                   value="{{ $field['is_secret'] ? '' : ($settings[$key] ?? '') }}"
                                   placeholder="{{ $field['is_secret'] && !empty($settings[$key]) ? '••••••••  (saved)' : '' }}"
                                   autocomplete="{{ $field['type'] === 'password' ? 'new-password' : 'off' }}"
                                   spellcheck="false"
                                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                          focus:ring-indigo-500 focus:border-indigo-500
                                          @error($key) border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                   aria-describedby="{{ $hintId }}{{ $errors->has($key) ? " error-{$key}" : '' }}"
                                   @if($field['type'] === 'password') aria-label="{{ $field['label'] }} — leave blank to keep existing value" @endif>

                            {{-- Toggle visibility for password fields --}}
                            @if($field['type'] === 'password')
                                <button type="button"
                                        class="settings-toggle-secret absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-r-md"
                                        data-target="{{ $fieldId }}"
                                        aria-label="Toggle {{ $field['label'] }} visibility"
                                        aria-pressed="false">
                                    <svg class="icon-eye w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg class="icon-eye-off hidden w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @endif

                    @error($key)
                        <p id="error-{{ $key }}" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                    @enderror

                    <p id="{{ $hintId }}" class="mt-1 text-xs text-gray-400">{{ $field['hint'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Footer / save --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-400">
                Changes take effect immediately — no deploy required.
            </p>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                           font-semibold text-xs text-white uppercase tracking-widest
                           hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                           transition">
                Save {{ $groupLabel }}
            </button>
        </div>

    </form>
</section>
