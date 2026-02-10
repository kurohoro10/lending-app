<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Details</h3>

        <form method="POST" action="{{ route('applications.personal-details.store', $application) }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                    <input type="text" name="full_name" id="full_name"
                           value="{{ old('full_name', $application->personalDetails->full_name ?? '') }}"
                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('full_name') border-red-500 @enderror" required>
                    @error('full_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address *</label>
                    <input type="email" name="email" id="email"
                           value="{{ old('email', $application?->personalDetails->email ?? '') }}"
                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('email') border-red-500 @enderror" required>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="mobile_phone" class="block text-sm font-medium text-gray-700">Mobile Phone *</label>
                    <input type="tel" name="mobile_phone" id="mobile_phone"
                           value="{{ old('mobile_phone', $application->personalDetails->mobile_phone ?? '') }}"
                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('mobile_phone') border-red-500 @enderror" required>
                    @error('mobile_phone')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                    <input type="date" name="date_of_birth" id="date_of_birth"
                           value="{{ old('date_of_birth', $application->personalDetails?->date_of_birth->format('Y-m-d') ?? '') }}"
                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                    <select name="gender" id="gender"
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select...</option>
                        <option value="male" {{ old('gender', $application->personalDetails->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $application->personalDetails->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', $application->personalDetails->gender ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                        <option value="prefer_not_to_say" {{ old('gender', $application->personalDetails->gender ?? '') == 'prefer_not_to_say' ? 'selected' : '' }}>Prefer not to say</option>
                    </select>
                </div>

                <div>
                    <label for="marital_status" class="block text-sm font-medium text-gray-700">Marital Status *</label>
                    <select name="marital_status" id="marital_status" required
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select...</option>
                        <option value="single" {{ old('marital_status', $application->personalDetails->marital_status ?? '') == 'single' ? 'selected' : '' }}>Single</option>
                        <option value="married" {{ old('marital_status', $application->personalDetails->marital_status ?? '') == 'married' ? 'selected' : '' }}>Married</option>
                        <option value="defacto" {{ old('marital_status', $application->personalDetails->marital_status ?? '') == 'defacto' ? 'selected' : '' }}>De Facto</option>
                        <option value="divorced" {{ old('marital_status', $application->personalDetails->marital_status ?? '') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                        <option value="widowed" {{ old('marital_status', $application->personalDetails->marital_status ?? '') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                    </select>
                    @error('marital_status')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="number_of_dependants" class="block text-sm font-medium text-gray-700">Number of Dependants *</label>
                    <input type="number" name="number_of_dependants" id="number_of_dependants" min="0"
                           value="{{ old('number_of_dependants', $application->personalDetails->number_of_dependants ?? '0') }}"
                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                </div>

                <div class="col-span-2">
                    <label for="spouse_name" class="block text-sm font-medium text-gray-700">Spouse Name (if married)</label>
                    <input type="text" name="spouse_name" id="spouse_name"
                           value="{{ old('spouse_name', $application->personalDetails->spouse_name ?? '') }}"
                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    {{ $application->personalDetails ? 'Update' : 'Save' }} Personal Details
                </button>
            </div>
        </form>

        @if($application->personalDetails)
        <div class="mt-4 p-4 bg-green-50 border-l-4 border-green-400">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">Personal details completed</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
