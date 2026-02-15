<!-- Personal Details Section - Enhanced with Fetch API -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200">
    <button type="button" 
        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-left focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        id="personal-details-btn"
        aria-expanded="true"
        aria-controls="personal-details-content">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    Personal Details
                </h3>
                <p class="text-indigo-100 text-sm mt-1">Tell us about yourself</p>
            </div>
            
            <!-- Chevron Icon -->
            <svg id="personal-details-chevron" class="w-5 h-5 text-white transition-transform duration-200 transform rotate-180" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>
    </button>

    <div id="personal-details-content" 
        class="transition-all duration-300 ease-in-out p-6"
        aria-labelledby="personal-details-header">
        <div class="p-6">
            @if($application->hasCompletePersonalDetails())
            <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-semibold text-green-800">Personal details completed</p>
                        <p class="text-xs text-green-700 mt-1">You can update your information below if needed</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Success/Error Messages Container -->
            <div id="form-messages" class="mb-4"></div>

            <form id="personal-details" method="POST" action="{{ route('applications.personal-details.store', $application) }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="full_name" id="full_name"
                            value="{{ old('full_name', $application->personalDetails->full_name ?? '') }}"
                            class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl @error('full_name') border-red-500 @enderror" required>
                        <p id="full_name-error" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address *</label>
                        <input type="email"
                            name="email"
                            id="email"
                            value="{{ old('email', $application->personalDetails?->email ?? '') }}"
                            class="block w-full py-3 px-4 border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed rounded-xl shadow-sm focus:ring-0 focus:border-gray-300"
                            readonly
                            aria-readonly="true"
                            title="Email is linked to your account and cannot be changed here.">
                        <p id="email-error" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label for="mobile_phone" class="block text-sm font-semibold text-gray-700 mb-2">Mobile Phone *</label>
                        <input type="tel" name="mobile_phone" id="mobile_phone"
                            value="{{ old('mobile_phone', $application->personalDetails->mobile_phone ?? '') }}"
                            class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl @error('mobile_phone') border-red-500 @enderror" required>
                        <p id="mobile_phone-error" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label for="date_of_birth" class="block text-sm font-semibold text-gray-700 mb-2">Date of Birth *</label>
                        <input type="date" name="date_of_birth" id="date_of_birth"
                            value="{{ old('date_of_birth', optional($application->personalDetails?->date_of_birth)->format('Y-m-d')) }}"
                            class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl"
                            required
                            aria-describedby="date_of_birth-error">
                        <p id="date_of_birth-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                    </div>

                    <div>
                        <label for="gender" class="block text-sm font-semibold text-gray-700 mb-2">Gender</label>
                        <select name="gender" id="gender"
                                class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select...</option>
                            <option value="male" {{ old('gender', $application->personalDetails->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $application->personalDetails->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $application->personalDetails->gender ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                            <option value="prefer_not_to_say" {{ old('gender', $application->personalDetails->gender ?? '') == 'prefer_not_to_say' ? 'selected' : '' }}>Prefer not to say</option>
                        </select>
                        <p id="gender-error" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label for="marital_status" class="block text-sm font-semibold text-gray-700 mb-2">Marital Status *</label>
                        <select name="marital_status" id="marital_status" required
                                class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select...</option>
                            <option value="single" {{ old('marital_status', $application->personalDetails->marital_status ?? '') == 'single' ? 'selected' : '' }}>Single</option>
                            <option value="married" {{ old('marital_status', $application->personalDetails->marital_status ?? '') == 'married' ? 'selected' : '' }}>Married</option>
                            <option value="defacto" {{ old('marital_status', $application->personalDetails->marital_status ?? '') == 'defacto' ? 'selected' : '' }}>De Facto</option>
                            <option value="divorced" {{ old('marital_status', $application->personalDetails->marital_status ?? '') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                            <option value="widowed" {{ old('marital_status', $application->personalDetails->marital_status ?? '') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                        </select>
                        <p id="marital_status-error" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label for="number_of_dependants" class="block text-sm font-semibold text-gray-700 mb-2">Number of Dependants *</label>
                        <input type="number" name="number_of_dependants" id="number_of_dependants" min="0"
                            value="{{ old('number_of_dependants', $application->personalDetails->number_of_dependants ?? '0') }}"
                            class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl" required>
                        <p id="number_of_dependants-error" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label for="citizenship_status" class="block text-sm font-semibold text-gray-700 mb-2">Citizenship Status *</label>
                        <select name="citizenship_status" id="citizenship_status" class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">Select...</option>
                            <option value="australian_citizen" {{ old('citizenship_status', $application->personalDetails->citizenship_status ?? '') == 'australian_citizen' ? 'selected' : '' }}>Australian Citizen</option>
                            <option value="permanent_resident" {{ old('citizenship_status', $application->personalDetails->citizenship_status ?? '') == 'permanent_resident' ? 'selected' : '' }}>Permanent Resident</option>
                            <option value="temporary_resident" {{ old('citizenship_status', $application->personalDetails->citizenship_status ?? '') == 'temporary_resident' ? 'selected' : '' }}>Temporary Resident</option>
                            <option value="nz_citizen" {{ old('citizenship_status', $application->personalDetails->citizenship_status ?? '') == 'nz_citizen' ? 'selected' : '' }}>NZ Citizen</option>
                        </select>
                        <p id="citizenship_status-error" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    <div class="col-span-2">
                        <label for="spouse_name" class="block text-sm font-semibold text-gray-700 mb-2">Spouse Name (if married)</label>
                        <input type="text" name="spouse_name" id="spouse_name"
                            value="{{ old('spouse_name', $application->personalDetails->spouse_name ?? '') }}"
                            class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                        <p id="spouse_name-error" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" id="submit-button" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold text-sm uppercase tracking-wide hover:shadow-lg transition transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span id="submit-button-text">{{ $application->personalDetails ? 'Update' : 'Save' }} Personal Details</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite('resources/js/applications/personalDetails.js')
