<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Appointments</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <a href="{{ route('dietitian.dashboard') }}" class="underline text-sm text-blue-700">Back to patients</a>

            @if (session('status'))
                <div class="bg-green-100 text-green-800 p-4 rounded" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Scheduled appointments</h3>

                @forelse ($scheduled as $appointment)
                    <div class="flex items-center justify-between border-t py-3 text-sm">
                        <div>
                            <p class="font-medium">{{ $appointment->scheduled_at->format('D, M j, Y · g:i A') }}</p>
                            <p>
                                <a href="{{ route('dietitian.patients.show', $appointment->patient) }}"
                                    class="underline text-blue-700">{{ $appointment->patient->name }}</a>
                                · {{ $appointment->duration_minutes }} min
                            </p>
                        </div>
                        <form method="POST" action="{{ route('dietitian.appointments.update', $appointment) }}"
                            class="flex gap-4">
                            @csrf
                            @method('PATCH')
                            <a href="{{ route('appointments.session', $appointment) }}" class="underline text-blue-700"
                                aria-label="Join video session with {{ $appointment->patient->name }}">Join session</a>
                            <button type="submit" name="status" value="completed" class="underline text-green-800"
                                aria-label="Mark appointment with {{ $appointment->patient->name }} as completed">
                                Mark completed
                            </button>
                            <button type="submit" name="status" value="cancelled" class="underline text-red-700"
                                aria-label="Cancel appointment with {{ $appointment->patient->name }}">
                                Cancel
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm">No scheduled appointments.</p>
                @endforelse
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">History</h3>

                @forelse ($history as $appointment)
                    <div class="flex justify-between border-t py-3 text-sm">
                        <span>
                            {{ $appointment->scheduled_at->format('M j, Y · g:i A') }}
                            with {{ $appointment->patient->name }}
                        </span>
                        <span>{{ ucfirst($appointment->status) }}</span>
                    </div>
                @empty
                    <p class="text-sm">No past appointments.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>