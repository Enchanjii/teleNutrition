<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dietitian Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('dietitian.dashboard') }}" class="flex items-end gap-3">
                    <div>
                        <x-input-label for="date" value="Review date" />
                        <x-text-input id="date" name="date" type="date" class="mt-1 block"
                            :value="$date->format('Y-m-d')" :max="today()->format('Y-m-d')" />
                        <x-input-error :messages="$errors->get('date')" class="mt-2" />
                    </div>
                    <x-primary-button>View</x-primary-button>
                </form>

                <p class="text-sm text-gray-700 mt-3">
                    Flags are decision-support suggestions for the dietitian's review, not diagnoses.
                    Low-intake flags are most meaningful for completed days.
                </p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Patients on {{ $date->format('F j, Y') }}</h3>

                <table class="w-full text-left text-sm">
                    <caption class="sr-only">
                        Patients with calorie intake and decision-support flags for {{ $date->format('F j, Y') }}
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" class="py-2">Patient</th>
                            <th scope="col" class="py-2">Calories (intake / target)</th>
                            <th scope="col" class="py-2">Flags</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr class="border-t align-top">
                                <th scope="row" class="py-2 font-normal">
                                    <a href="{{ route('dietitian.patients.show', ['patient' => $row['patient'], 'date' => $date->format('Y-m-d')]) }}"
                                        class="underline text-blue-700">{{ $row['patient']->name }}</a>
                                </th>

                                @if (!$row['has_profile'])
                                    <td class="py-2" colspan="2">Health profile not completed.</td>
                                @else
                                    <td class="py-2">
                                        {{ number_format($row['intake']['calories']) }}
                                        / {{ number_format($row['targets']['calories']) }} kcal
                                    </td>
                                    <td class="py-2 space-y-1">
                                        @forelse ($row['flags'] as $flag)
                                            <p>
                                                <strong>{{ ucfirst($flag['severity']) }}:</strong>
                                                {{ $flag['term'] }}. {{ $flag['message'] }}
                                            </p>
                                        @empty
                                            <p>No flags.</p>
                                        @endforelse
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-gray-700">No patients registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>