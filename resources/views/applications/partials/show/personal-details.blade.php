<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500">Full Name:</span>
                <span class="ml-2 text-sm text-gray-900">{{ $application->personalDetails->full_name }}</span>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Email:</span>
                <span class="ml-2 text-sm text-gray-900">{{ $application->personalDetails->email }}</span>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Mobile:</span>
                <span class="ml-2 text-sm text-gray-900">{{ $application->personalDetails->mobile_phone }}</span>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Marital Status:</span>
                <span class="ml-2 text-sm text-gray-900">{{ ucwords($application->personalDetails->marital_status) }}</span>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Dependants:</span>
                <span class="ml-2 text-sm text-gray-900">{{ $application->personalDetails->number_of_dependants }}</span>
            </div>
            @if($application->personalDetails->spouse_name)
            <div>
                <span class="text-sm font-medium text-gray-500">Spouse Name:</span>
                <span class="ml-2 text-sm text-gray-900">{{ $application->personalDetails->spouse_name }}</span>
            </div>
            @endif
        </div>
    </div>
</div>
