<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Patient Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <p>Welcome, {{ auth()->user()->name }}.</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('patient.profile.edit') }}"
                        class="inline-block px-4 py-2 bg-gray-800 text-white rounded-md">Health profile</a>
                    <a href="{{ route('patient.meals.index') }}"
                        class="inline-block px-4 py-2 bg-gray-800 text-white rounded-md">Log a meal</a>
                    <a href="{{ route('patient.appointments.index') }}"
                        class="inline-block px-4 py-2 bg-gray-800 text-white rounded-md">Appointments</a>
                </div>
            </div>

            @if (!$targets)
                <div class="bg-yellow-50 text-yellow-900 p-4 rounded" role="status">
                    Complete your
                    <a href="{{ route('patient.profile.edit') }}" class="underline font-medium">health profile</a>
                    to see your daily nutrition targets.
                </div>
            @else
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium">Today's intake vs daily target</h3>
                    <p class="text-sm text-gray-700 mt-1">
                        Estimated energy need (TDEE): {{ number_format($targets['tdee']) }} kcal
                        · Basal metabolic rate (BMR): {{ number_format($targets['bmr']) }} kcal
                    </p>

                    <div class="relative h-80 mt-4">
                        <canvas id="intakeChart" role="img"
                            aria-label="Bar chart of today's nutrient intake as a percentage of the daily target"></canvas>
                    </div>

                    <table class="mt-6 w-full text-left text-sm">
                        <caption class="sr-only">Today's nutrient intake compared with daily targets</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="py-2">Nutrient</th>
                                <th scope="col" class="py-2">Intake</th>
                                <th scope="col" class="py-2">Target</th>
                                <th scope="col" class="py-2">% of target</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr class="border-t">
                                    <th scope="row" class="py-2 font-normal">{{ $row['label'] }}</th>
                                    <td class="py-2">{{ $row['intake'] }} {{ $row['unit'] }}</td>
                                    <td class="py-2">{{ $row['target'] }} {{ $row['unit'] }}</td>
                                    <td class="py-2">{{ $row['percent'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const rows = @json($rows);

                        new Chart(document.getElementById('intakeChart'), {
                            type: 'bar',
                            data: {
                                labels: rows.map(r => r.label),
                                datasets: [{
                                    label: '% of daily target',
                                    data: rows.map(r => r.percent),
                                    backgroundColor: '#2563eb',
                                }],
                            },
                            options: {
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { beginAtZero: true, title: { display: true, text: '% of target' } },
                                },
                            },
                        });
                    });
                </script>
            @endif
        </div>
    </div>
</x-app-layout>