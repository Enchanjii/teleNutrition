@php
    $field = 'mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-emerald-600 focus:ring-emerald-600';
    $minStart = now()->addMinutes(15 - (now()->minute % 15))->format('Y-m-d\TH:i');
    $statusStyle = [
        'completed' => 'bg-emerald-50 text-emerald-900',
        'cancelled' => 'bg-gray-100 text-gray-800',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-900">Appointments</h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <div class="grid gap-6 lg:grid-cols-5">

                <section class="lg:col-span-2 bg-white border border-gray-200 rounded-2xl p-6"
                    aria-labelledby="book-title">
                    <h2 id="book-title" class="text-lg font-semibold">Book a video consultation</h2>

                    @if ($dietitians->isEmpty())
                        <p class="mt-4 text-sm text-gray-700">No dietitians are available yet.</p>
                    @else
                        <form method="POST" action="{{ route('patient.appointments.store') }}" class="mt-5 space-y-5">
                            @csrf

                            <div>
                                <x-input-label for="dietitian_id" value="Dietitian" />
                                <select id="dietitian_id" name="dietitian_id" required class="{{ $field }}">
                                    <option value="">Select a dietitian</option>
                                    @foreach ($dietitians as $dietitian)
                                        <option value="{{ $dietitian->id }}" @selected((int) old('dietitian_id') === $dietitian->id)>
                                            {{ $dietitian->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('dietitian_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="scheduled_at" value="Date and time" />
                                <x-text-input id="scheduled_at" name="scheduled_at" type="datetime-local" step="900"
                                    class="mt-1 block w-full" :min="$minStart" :value="old('scheduled_at')" required />
                                <p class="mt-1 text-sm text-gray-700">
                                    30-minute sessions, from 8:00 AM to 4:30 PM, on the quarter hour.
                                </p>
                                <x-input-error :messages="$errors->get('scheduled_at')" class="mt-2" />
                            </div>

                            <x-primary-button class="w-full">Book appointment</x-primary-button>
                        </form>
                    @endif
                </section>

                <div class="lg:col-span-3 space-y-6">
                    <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="upcoming-title">
                        <h2 id="upcoming-title" class="text-lg font-semibold">Upcoming</h2>

                        @if ($scheduled->isEmpty())
                            <div class="mt-5 rounded-xl border border-dashed border-gray-300 p-6 text-center">
                                <p class="font-medium text-gray-900">No upcoming consultations</p>
                                <p class="mt-1 text-sm text-gray-700">Pick a dietitian and a time to get started.</p>
                            </div>
                        @else
                            <ul class="mt-5 space-y-3">
                                @foreach ($scheduled as $appointment)
                                    <li
                                        class="flex flex-col gap-4 rounded-xl border border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-emerald-50 text-emerald-900"
                                                aria-hidden="true">
                                                <span
                                                    class="text-xs font-medium uppercase">{{ $appointment->scheduled_at->format('M') }}</span>
                                                <span
                                                    class="text-lg font-semibold leading-none">{{ $appointment->scheduled_at->format('j') }}</span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">
                                                    {{ $appointment->scheduled_at->format('l, g:i A') }}</p>
                                                <p class="text-sm text-gray-700">
                                                    with {{ $appointment->dietitian->name }} ·
                                                    {{ $appointment->duration_minutes }} min
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-4">
                                            @if ($appointment->isJoinable())
                                                <a href="{{ route('appointments.session', $appointment) }}"
                                                    aria-label="Join video session on {{ $appointment->scheduled_at->format('M j, g:i A') }}"
                                                    class="inline-flex items-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2">
                                                    Join now
                                                </a>
                                            @else
                                                <span class="text-sm text-gray-700">
                                                    Opens {{ $appointment->joinOpensAt()->format('M j, g:i A') }}
                                                </span>
                                            @endif

                                            <form method="POST"
                                                action="{{ route('patient.appointments.cancel', $appointment) }}"
                                                onsubmit="return confirm('Cancel this appointment?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    aria-label="Cancel appointment on {{ $appointment->scheduled_at->format('M j, g:i A') }}"
                                                    class="text-sm font-medium text-red-700 underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-red-700">
                                                    Cancel
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>

                    <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="history-title">
                        <h2 id="history-title" class="text-lg font-semibold">History</h2>

                        @if ($history->isEmpty())
                            <p class="mt-4 text-sm text-gray-700">No past appointments yet.</p>
                        @else
                            <ul class="mt-4 divide-y divide-gray-100">
                                @foreach ($history as $appointment)
                                    <li class="flex items-center justify-between gap-3 py-3 text-sm">
                                        <span class="text-gray-900">
                                            {{ $appointment->scheduled_at->format('M j, Y · g:i A') }}
                                            <span class="text-gray-700">with {{ $appointment->dietitian->name }}</span>
                                        </span>
                                        <span
                                            class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusStyle[$appointment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>