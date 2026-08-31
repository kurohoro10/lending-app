<x-action-section>
    <x-slot name="title">
        {{ __('Two Factor Authentication') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Add additional security to your account using two factor authentication.') }}
    </x-slot>

    <x-slot name="content">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            @if ($this->enabled)
                @if ($showingConfirmation)
                    {{ __('Finish enabling two factor authentication.') }}
                @else
                    {{ __('You have enabled two factor authentication using an authenticator app.') }}
                @endif
            @elseif ($this->emailEnabled)
                {{ __('You have enabled two factor authentication using email codes.') }}
            @else
                {{ __('You have not enabled two factor authentication.') }}
            @endif
        </h3>

        <div class="mt-3 max-w-xl text-sm text-gray-600 dark:text-gray-400">
            <p>
                {{ __('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application, or have one emailed to you.') }}
            </p>
        </div>

        <!-- Method Selector -->
        <div class="mt-4 max-w-xl">
            <p class="font-semibold text-sm text-gray-900 dark:text-gray-100 mb-2">{{ __('Two-Factor Authentication Method') }}</p>

            <label class="flex items-center mb-2">
                <input type="radio" wire:model.live="method" value="app" class="text-indigo-600 focus:ring-indigo-500">
                <span class="ms-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Authenticator App') }}</span>
            </label>

            <label class="flex items-center">
                <input type="radio" wire:model.live="method" value="email" class="text-indigo-600 focus:ring-indigo-500">
                <span class="ms-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Email OTP') }}</span>
            </label>
        </div>

        @if ($method === 'app')
            @if ($this->enabled)
                @if ($showingQrCode)
                    <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-semibold">
                            @if ($showingConfirmation)
                                {{ __('To finish enabling two factor authentication, scan the following QR code using your phone\'s authenticator application or enter the setup key and provide the generated OTP code.') }}
                            @else
                                {{ __('Two factor authentication is now enabled. Scan the following QR code using your phone\'s authenticator application or enter the setup key.') }}
                            @endif
                        </p>
                    </div>

                    <div class="mt-4 p-2 inline-block bg-white">
                        {!! $this->user->twoFactorQrCodeSvg() !!}
                    </div>

                    <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-semibold">
                            {{ __('Setup Key') }}: {{ decrypt($this->user->two_factor_secret) }}
                        </p>
                    </div>

                    @if ($showingConfirmation)
                        <div class="mt-4">
                            <x-label for="code" value="{{ __('Code') }}" />

                            <x-input id="code" type="text" name="code" class="block mt-1 w-1/2" inputmode="numeric" autofocus autocomplete="one-time-code"
                                wire:model="code"
                                wire:keydown.enter="confirmTwoFactorAuthentication" />

                            <x-input-error for="code" class="mt-2" />
                        </div>
                    @endif
                @endif

                @if ($showingRecoveryCodes)
                    <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-semibold">
                            {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.') }}
                        </p>
                    </div>

                    <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                        @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                            <div>{{ $code }}</div>
                        @endforeach
                    </div>
                @endif
            @endif

            <div class="mt-5">
                @if (! $this->enabled)
                    <x-confirms-password wire:then="enableTwoFactorAuthentication">
                        <x-button type="button" wire:loading.attr="disabled">
                            {{ __('Enable') }}
                        </x-button>
                    </x-confirms-password>
                @else
                    @if ($showingRecoveryCodes)
                        <x-confirms-password wire:then="regenerateRecoveryCodes">
                            <x-secondary-button class="me-3">
                                {{ __('Regenerate Recovery Codes') }}
                            </x-secondary-button>
                        </x-confirms-password>
                    @elseif ($showingConfirmation)
                        <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                            <x-button type="button" class="me-3" wire:loading.attr="disabled">
                                {{ __('Confirm') }}
                            </x-button>
                        </x-confirms-password>
                    @else
                        <x-confirms-password wire:then="showRecoveryCodes">
                            <x-secondary-button class="me-3">
                                {{ __('Show Recovery Codes') }}
                            </x-secondary-button>
                        </x-confirms-password>
                    @endif

                    @if ($showingConfirmation)
                        <x-confirms-password wire:then="disableTwoFactorAuthentication">
                            <x-secondary-button wire:loading.attr="disabled">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                        </x-confirms-password>
                    @else
                        <x-confirms-password wire:then="disableTwoFactorAuthentication">
                            <x-danger-button wire:loading.attr="disabled">
                                {{ __('Disable') }}
                            </x-danger-button>
                        </x-confirms-password>
                    @endif
                @endif
            </div>
        @else
            {{-- Email OTP --}}
            @if ($this->emailEnabled)
                <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                    <p>{{ __('Codes will be emailed to :email when you log in.', ['email' => $this->user->email]) }}</p>
                </div>

                <div class="mt-5">
                    <x-confirms-password wire:then="disableEmailTwoFactorAuthentication">
                        <x-danger-button wire:loading.attr="disabled">
                            {{ __('Disable') }}
                        </x-danger-button>
                    </x-confirms-password>
                </div>
            @else
                @if ($showingEmailCode)
                    <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p>{{ __('We\'ve emailed a 6-digit code to :email. Enter it below to finish enabling email two factor authentication.', ['email' => $this->user->email]) }}</p>
                    </div>

                    <div class="mt-4">
                        <x-label for="emailCode" value="{{ __('Code') }}" />

                        <x-input id="emailCode" type="text" name="emailCode" class="block mt-1 w-1/2" inputmode="numeric" autofocus autocomplete="one-time-code"
                            wire:model="emailCode"
                            wire:keydown.enter="confirmEmailTwoFactorAuthentication" />

                        <x-input-error for="emailCode" class="mt-2" />
                    </div>

                    <div class="mt-5">
                        <x-confirms-password wire:then="confirmEmailTwoFactorAuthentication">
                            <x-button type="button" class="me-3" wire:loading.attr="disabled">
                                {{ __('Confirm') }}
                            </x-button>
                        </x-confirms-password>

                        <x-confirms-password wire:then="sendEmailTwoFactorCode">
                            <x-secondary-button wire:loading.attr="disabled">
                                {{ __('Resend Code') }}
                            </x-secondary-button>
                        </x-confirms-password>
                    </div>
                @else
                    <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p>{{ __('We\'ll email a 6-digit code to :email each time you log in.', ['email' => $this->user->email]) }}</p>
                    </div>

                    <div class="mt-5">
                        <x-confirms-password wire:then="sendEmailTwoFactorCode">
                            <x-button type="button" wire:loading.attr="disabled">
                                {{ __('Enable') }}
                            </x-button>
                        </x-confirms-password>
                    </div>
                @endif
            @endif
        @endif
    </x-slot>
</x-action-section>
