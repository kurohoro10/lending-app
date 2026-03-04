{{-- resources/views/applications/partials/show/personal-details.blade.php --}}
@php $pd = $application->personalDetails; @endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
            </svg>
            Personal Details
        </h3>
    </div>

    <div class="p-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Full Name</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">{{ $pd->full_name }}</dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">
                    <a href="mailto:{{ $pd->email }}"
                       class="text-indigo-600 hover:text-indigo-800 hover:underline
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded">
                        {{ $pd->email }}
                    </a>
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Mobile</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">
                    <a href="tel:{{ $pd->mobile_phone }}"
                       class="text-indigo-600 hover:text-indigo-800 hover:underline
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded">
                        {{ $pd->mobile_phone }}
                    </a>
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Date of Birth</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">
                    {{ $pd->date_of_birth?->format('d M Y') ?? '—' }}
                    @if($pd->age)
                        <span class="text-gray-400 text-xs ml-1">({{ $pd->age }} yrs)</span>
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Gender</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">
                    {{ $pd->gender ? ucwords(str_replace('_', ' ', $pd->gender)) : '—' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Citizenship</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">
                    {{ $pd->citizenship_status ? ucwords(str_replace('_', ' ', $pd->citizenship_status)) : '—' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Marital Status</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">
                    {{ ucwords(str_replace('_', ' ', $pd->marital_status)) }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Dependants</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">{{ $pd->number_of_dependants }}</dd>
            </div>

            @if($pd->contact_role)
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact Role</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">
                        {{ ucwords(str_replace('_', ' ', $pd->contact_role)) }}
                    </dd>
                </div>
            @endif

            @if(in_array($pd->marital_status, ['married', 'defacto']) && $pd->spouse_name)
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Spouse / Partner Name</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $pd->spouse_name }}</dd>
                </div>
            @endif

            @if(in_array($pd->marital_status, ['married', 'defacto']) && $pd->spouse_income !== null)
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Spouse / Partner Income</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">${{ number_format($pd->spouse_income, 2) }} p.a.</dd>
                </div>
            @endif

        </dl>
    </div>
</div>
