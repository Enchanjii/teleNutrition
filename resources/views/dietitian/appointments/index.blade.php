@php
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
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="upcoming-title">
                <h2 id="upcoming-title" class="text-lg font-semibold">Scheduled</h2>

                @if ($scheduled->isEmpty())
                    <div class="mt-5 rounded-xl border border-dashed border-gray-300 p-6 text-center">
                        <p class="font-medium text-gray-900">No scheduled appointments</p>
                        <p class="mt-1 text-sm text-gray-700">New bookings from patients appear here.</p>
                    </div>
                @else
                    <ul class="mt-5 space-y-3">
                        @foreach ($scheduled as $appointment)
                            @php $passed = now()->gt($appointment->joinClosesAt()); @endphp

                            <li class="rounded-xl border border-gray-200 p-4">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
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
                                                <a href="{{ route('dietitian.patients.show', $appointment->patient) }}"
                                                    class="underline text-emerald-800">{{ $appointment->patient->name }}</a>
                                                · {{ $appointment->duration_minutes }} min
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-4">
                                        @if ($appointment->isJoinable())
                                            <a href="{{ route('appointments.session', $appointment) }}"
                                                aria-label="Join video session with {{ $appointment->patient->name }}"
                                                class="inline-flex items-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2">
                                                Join now
                                            </a>
                                        @elseif (!$passed)
                                            <span class="text-sm text-gray-700">Opens
                                                {{ $appointment->joinOpensAt()->format('M j, g:i A') }}</span>
                                        @endif

                                        <form method="POST" action="{{ route('dietitian.appointments.update', $appointment) }}"
                                            class="flex items-center gap-4">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" name="status" value="completed"
                                                aria-label="Mark appointment with {{ $appointment->patient->name }} as completed"
                                                class="text-sm font-medium text-emerald-800 underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
                                                Mark completed
                                            </button>

                                            <button type="submit" name="status" value="cancelled"
                                                onclick="return confirm('Cancel this appointment?')"
                                                aria-label="Cancel appointment with {{ $appointment->patient->name }}"
                                                class="text-sm font-medium text-red-700 underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-red-700">
                                                Cancel
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                @if ($passed)
                                    <p class="mt-3 rounded-lg bg-amber-50 p-3 text-sm text-amber-900">
                                        The session time has passed. Mark it completed or cancel it.
                                    </p>
                                @endif
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
                                    <span class="text-gray-700">with {{ $appointment->patient->name }}</span>
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
</x-app-layout>