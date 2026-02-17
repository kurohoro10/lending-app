<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500">Full Name:</span>
                <p class="mt-1 text-sm text-gray-900">{{ $application->personalDetails->full_name }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Email:</span>
                <p class="mt-1 text-sm text-gray-900">{{ $application->personalDetails->email }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Mobile:</span>
                <p class="mt-1 text-sm text-gray-900">{{ $application->personalDetails->mobile_phone }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Marital Status:</span>
                <p class="mt-1 text-sm text-gray-900">{{ ucwords($application->personalDetails->marital_status) }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Dependants:</span>
                <p class="mt-1 text-sm text-gray-900">{{ $application->personalDetails->number_of_dependants }}</p>
            </div>
            @if($application->personalDetails->spouse_name)
            <div>
                <span class="text-sm font-medium text-gray-500">Spouse:</span>
                <p class="mt-1 text-sm text-gray-900">{{ $application->personalDetails->spouse_name }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
