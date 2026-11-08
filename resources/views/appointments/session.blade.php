<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-900">Video consultation</h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <a href="{{ $backUrl }}"
                class="inline-block text-sm font-medium text-emerald-800 underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
                Back to appointments
            </a>

            <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="session-title">
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
                            <h2 id="session-title" class="font-semibold text-gray-900">
                                {{ $appointment->scheduled_at->format('l, g:i A') }}
                            </h2>
                            <p class="text-sm text-gray-700">
                                {{ $appointment->patient->name }} and {{ $appointment->dietitian->name }}
                                · {{ $appointment->duration_minutes }} min
                            </p>
                        </div>
                    </div>

                    <span
                        class="self-start rounded-full px-3 py-1 text-sm font-medium sm:self-auto {{ $joinable ? 'bg-emerald-50 text-emerald-900' : 'bg-gray-100 text-gray-800' }}">
                        {{ $joinable ? 'Open now' : ucfirst($appointment->status) }}
                    </span>
                </div>
            </section>

            @if ($joinable)
                <section class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-6 space-y-4"
                    aria-labelledby="call-title">
                    <h2 id="call-title" class="sr-only">Video call</h2>

                    <p class="text-sm text-gray-700">
                        Allow camera and microphone access when your browser asks.
                        Use headphones if you hear an echo.
                    </p>

                    <div id="jitsi-container" class="w-full overflow-hidden rounded-xl bg-gray-900" style="height: 600px;">
                    </div>
                </section>

                <script src="https://8x8.vc/{{ $session['app_id'] }}/external_api.js"></script>
                <script>
                    window.addEventListener('load', () => {
                        const session = @json($session);
                        const backUrl = @json($backUrl);

                        const api = new JitsiMeetExternalAPI('8x8.vc', {
                            roomName: session.room,
                            jwt: session.jwt,
                            parentNode: document.getElementById('jitsi-container'),
                            width: '100%',
                            height: '100%',
                            userInfo: { displayName: session.display_name },
                        });

                        api.addEventListener('readyToClose', () => {
                            window.location.href = backUrl;
                        });
                    });
                </script>
            @else
                <section class="bg-white border border-gray-200 rounded-2xl p-8 text-center" role="status">
                    @if ($appointment->status !== 'scheduled')
                        <p class="text-lg font-semibold text-gray-900">This session is no longer available</p>
                        <p class="mt-1 text-gray-700">Status: {{ $appointment->status }}.</p>
                    @elseif (now()->lt($appointment->joinOpensAt()))
                        <p class="text-lg font-semibold text-gray-900">Not open yet</p>
                        <p class="mt-1 text-gray-700">
                            This session opens at {{ $appointment->joinOpensAt()->format('g:i A') }}
                            on {{ $appointment->joinOpensAt()->format('M j, Y') }},
                            {{ \App\Models\Appointment::JOIN_EARLY_MINUTES }} minutes before the scheduled start.
                        </p>
                    @else
                        <p class="text-lg font-semibold text-gray-900">This session has ended</p>
                        <p class="mt-1 text-gray-700">You can book another consultation from the appointments page.</p>
                    @endif

                    <a href="{{ $backUrl }}"
                        class="mt-6 inline-flex items-center rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2">
                        Back to appointments
                    </a>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>