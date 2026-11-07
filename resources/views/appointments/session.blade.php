<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Video Consultation</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <a href="{{ $backUrl }}" class="underline text-sm text-blue-700">Back to appointments</a>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm">
                <p class="font-medium">{{ $appointment->scheduled_at->format('D, M j, Y · g:i A') }}</p>
                <p>
                    Patient: {{ $appointment->patient->name }}
                    · Dietitian: {{ $appointment->dietitian->name }}
                    · {{ $appointment->duration_minutes }} min
                </p>
            </div>

            @if ($joinable)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-3">
                    <p class="text-sm text-gray-700">
                        Allow camera and microphone access when your browser asks.
                    </p>
                    <div id="jitsi-container" class="w-full bg-gray-900 rounded" style="height: 600px;"></div>
                </div>

                <script src="https://8x8.vc/{{ $session['app_id'] }}/external_api.js"></script>
                <script>
                    window.addEventListener('load', () => {
                        const session = @json($session);

                        new JitsiMeetExternalAPI('8x8.vc', {
                            roomName: session.room,
                            jwt: session.jwt,
                            parentNode: document.getElementById('jitsi-container'),
                            width: '100%',
                            height: '100%',
                            userInfo: { displayName: session.display_name },
                        });
                    });
                </script>
            @else
                <div class="bg-white shadow-sm sm:rounded-lg p-6" role="status">
                    @if ($appointment->status !== 'scheduled')
                        <p>This session is no longer available ({{ $appointment->status }}).</p>
                    @elseif (now()->lt($appointment->joinOpensAt()))
                        <p>
                            This session opens at {{ $appointment->joinOpensAt()->format('g:i A') }}
                            on {{ $appointment->joinOpensAt()->format('M j, Y') }},
                            {{ \App\Models\Appointment::JOIN_EARLY_MINUTES }} minutes before the scheduled start.
                        </p>
                    @else
                        <p>This session has ended.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>