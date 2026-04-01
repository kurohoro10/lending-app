<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800 leading-tight">
                    {{ __('User Accounts') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Manage account access and unlock locked users
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-700 rounded-full text-xs font-semibold border border-green-200">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                    {{ $users->where('locked_at', null)->count() }} Active
                </span>
                @if($users->where('locked_at', '!=', null)->count() > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-700 rounded-full text-xs font-semibold border border-red-200">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        {{ $users->where('locked_at', '!=', null)->count() }} Locked
                    </span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm font-medium text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm font-medium text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            {{-- User list card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

                {{-- Table header --}}
                <div class="grid grid-cols-12 gap-4 px-6 py-3 bg-gray-50 border-b border-gray-200">
                    <div class="col-span-5 text-xs font-semibold text-gray-500 uppercase tracking-wider">User</div>
                    <div class="col-span-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</div>
                    <div class="col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</div>
                    <div class="col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Action</div>
                </div>

                {{-- Rows --}}
                @forelse($users as $user)
                    <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center
                        border-b border-gray-100 last:border-b-0
                        {{ $user->locked_at ? 'bg-red-50/40' : 'hover:bg-gray-50' }}
                        transition-colors duration-150">

                        {{-- Name + Email --}}
                        <div class="col-span-5 flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold text-white
                                {{ $user->locked_at
                                    ? 'bg-gradient-to-br from-red-400 to-red-600'
                                    : 'bg-gradient-to-br from-indigo-400 to-purple-500' }}">
                                {{ strtoupper(substr($user->first_name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">
                                    {{ $user->name }}
                                </p>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ $user->email }}
                                </p>
                            </div>
                        </div>

                        {{-- Role --}}
                        <div class="col-span-3">
                            @foreach($user->getRoleNames() as $role)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $role === 'admin'    ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $role === 'assessor' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $role === 'client'   ? 'bg-gray-100 text-gray-600' : '' }}
                                ">
                                    {{ ucfirst($role) }}
                                </span>
                            @endforeach
                        </div>

                        {{-- Status --}}
                        <div class="col-span-2">
                            @if($user->locked_at)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Locked
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                    Active
                                </span>
                            @endif
                        </div>

                        {{-- Action --}}
                        <div class="col-span-2 flex justify-end">
                            @if($user->locked_at)
                                <form method="POST"
                                      action="{{ route('admin.users.unlock', $user) }}"
                                      onsubmit="return confirm('Unlock account for {{ addslashes($user->name) }}?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5
                                                   bg-indigo-600 hover:bg-indigo-700
                                                   text-white text-xs font-semibold
                                                   rounded-lg shadow-sm transition-colors duration-150
                                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                        </svg>
                                        Unlock
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-300 italic">—</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-16 text-center">
                        <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">No user accounts found.</p>
                    </div>
                @endforelse
            </div>

            @if($users->where('locked_at', '!=', null)->count() > 0)
                <p class="text-xs text-gray-400 text-center">
                    Accounts are locked after {{ config('auth.lockout_attempts', 3) }} consecutive failed login attempts.
                </p>
            @endif

        </div>
    </div>
</x-app-layout>