<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Appointments</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 p-4 rounded" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Book a tele-nutrition consultation</h3>

                @if ($dietitians->isEmpty())
                    <p>No dietitians are available yet.</p>
                @else
                    <form method="POST" action="{{ route('patient.appointments.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="dietitian_id" value="Dietitian" />
                            <select id="dietitian_id" name="dietitian_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
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
                                class="mt-1 block w-full" :min="now()->addMinutes(15 - (now()->minute % 15))->format('Y-m-d\TH:i')" :value="old('scheduled_at')" required />
                            <p class="text-sm text-gray-700 mt-1">
                                Sessions are 30 minutes. Available 8:00 AM to 4:30 PM, on the quarter hour.
                            </p>
                            <x-input-error :messages="$errors->get('scheduled_at')" class="mt-2" />
                        </div>

                        <x-primary-button>Book appointment</x-primary-button>
                    </form>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Scheduled appointments</h3>

                @forelse ($scheduled as $appointment)
                    <div class="flex items-center justify-between border-t py-3 text-sm">
                        <div>
                            <p class="font-medium">{{ $appointment->scheduled_at->format('D, M j, Y · g:i A') }}</p>
                            <p>with {{ $appointment->dietitian->name }} · {{ $appointment->duration_minutes }} min</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <a href="{{ route('appointments.session', $appointment) }}" class="underline text-blue-700"
                                aria-label="Join video session on {{ $appointment->scheduled_at->format('M j, g:i A') }}">Join
                                session</a>
                            <form method="POST" action="{{ route('patient.appointments.cancel', $appointment) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="underline text-red-700"
                                    aria-label="Cancel appointment on {{ $appointment->scheduled_at->format('M j, g:i A') }}">
                                    Cancel
                                </button>
                            </form>
                        </div>
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
                            with {{ $appointment->dietitian->name }}
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