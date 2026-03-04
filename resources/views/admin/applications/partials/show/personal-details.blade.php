{{-- resources/views/admin/applications/partials/show/personal-details.blade.php --}}
@php $pd = $application->personalDetails; @endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500">Full Name:</span>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $pd->user->first_name }}
                    {{ $pd->user->middle_name ?? '' }}
                    {{ $pd->user->last_name }}
                    {{ $pd->user->name_extension ?? '' }}
                </p>
            </div>

            <div>
                <span class="text-sm font-medium text-gray-500">Email:</span>
                <p class="mt-1 text-sm text-gray-900">
                    <a href="mailto:{{ $pd->user->email }}"
                       class="text-indigo-600 hover:underline focus:outline-none focus:ring-1 focus:ring-indigo-500 rounded">
                        {{ $pd->user->email }}
                    </a>
                </p>
            </div>

            <div>
                <span class="text-sm font-medium text-gray-500">Mobile:</span>
                <p class="mt-1 text-sm text-gray-900">
                    <a href="tel:{{ $pd->mobile_phone }}"
                       class="text-indigo-600 hover:underline focus:outline-none focus:ring-1 focus:ring-indigo-500 rounded">
                        {{ $pd->mobile_phone }}
                    </a>
                </p>
            </div>

            <div>
                <span class="text-sm font-medium text-gray-500">Date of Birth:</span>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $pd->date_of_birth?->format('d M Y') ?? '—' }}
                    @if($pd->age)
                        <span class="text-gray-400">({{ $pd->age }} yrs)</span>
                    @endif
                </p>
            </div>

            <div>
                <span class="text-sm font-medium text-gray-500">Marital Status:</span>
                <p class="mt-1 text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $pd->marital_status)) }}</p>
            </div>

            <div>
                <span class="text-sm font-medium text-gray-500">Dependants:</span>
                <p class="mt-1 text-sm text-gray-900">{{ $pd->number_of_dependants }}</p>
            </div>

            @if($pd->contact_role)
                <div>
                    <span class="text-sm font-medium text-gray-500">Contact Role:</span>
                    <p class="mt-1 text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $pd->contact_role)) }}</p>
                </div>
            @endif

            @if(in_array($pd->marital_status, ['married', 'defacto']) && $pd->spouse_name)
                <div>
                    <span class="text-sm font-medium text-gray-500">Spouse / Partner Name:</span>
                    <p class="mt-1 text-sm text-gray-900">{{ $pd->spouse_name }}</p>
                </div>
            @endif

            @if(in_array($pd->marital_status, ['married', 'defacto']) && $pd->spouse_income !== null)
                <div>
                    <span class="text-sm font-medium text-gray-500">Spouse / Partner Income:</span>
                    <p class="mt-1 text-sm text-gray-900">${{ number_format($pd->spouse_income, 2) }} p.a.</p>
                </div>
            @endif

        </div>
    </div>
</div>
