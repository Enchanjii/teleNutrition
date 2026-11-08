@php
    $user = auth()->user();
    $isDietitian = $user->role === 'dietitian';

    $links = $isDietitian
        ? [
            ['Patients', route('dietitian.dashboard'), request()->routeIs('dietitian.dashboard', 'dietitian.patients.*')],
            ['Appointments', route('dietitian.appointments.index'), request()->routeIs('dietitian.appointments.*', 'appointments.session')],
        ]
        : [
            ['Dashboard', route('patient.dashboard'), request()->routeIs('patient.dashboard')],
            ['Meals', route('patient.meals.index'), request()->routeIs('patient.meals.*')],
            ['Appointments', route('patient.appointments.index'), request()->routeIs('patient.appointments.*', 'appointments.session')],
            ['Health profile', route('patient.profile.edit'), request()->routeIs('patient.profile.*')],
        ];

    $home = $isDietitian ? route('dietitian.dashboard') : route('patient.dashboard');
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-gray-200" aria-label="Primary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ $home }}" aria-label="{{ config('app.name') }} home"
                        class="flex items-center gap-2 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
                        <x-application-logo class="block h-9 w-9 text-emerald-700" />
                        <span class="hidden sm:block font-semibold text-gray-900">{{ config('app.name') }}</span>
                    </a>
                </div>

                <div class="hidden space-x-6 sm:ms-10 sm:flex">
                    @foreach ($links as [$label, $url, $active])
                        <a href="{{ $url }}" @if ($active) aria-current="page" @endif
                            class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 {{ $active ? 'border-emerald-600 text-gray-900' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
                            <span
                                class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold"
                                aria-hidden="true">
                                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                            </span>
                            <span>{{ $user->name }}</span>
                            <svg class="h-4 w-4 fill-current text-gray-500" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Account settings</x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Log out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" type="button" aria-label="Toggle navigation menu"
                    aria-controls="mobile-menu" :aria-expanded="open.toString()"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-menu" :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-200">
        <div class="pt-2 pb-3 space-y-1">
            @foreach ($links as [$label, $url, $active])
                <a href="{{ $url }}" @if ($active) aria-current="page" @endif
                    class="block px-4 py-2 text-base font-medium border-s-4 {{ $active ? 'border-emerald-600 bg-emerald-50 text-emerald-900' : 'border-transparent text-gray-700 hover:bg-gray-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-900">{{ $user->name }}</div>
                <div class="font-medium text-sm text-gray-600">{{ $user->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}"
                    class="block px-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-50">Account settings</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="block w-full px-4 py-2 text-start text-base font-medium text-gray-700 hover:bg-gray-50">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>