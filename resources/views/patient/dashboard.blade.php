@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

    $calories = collect($rows)->firstWhere('label', 'Calories');
    $circumference = 326.73;
    $caloriePercent = $calories ? min($calories['percent'], 100) : 0;
    $ringColor = ($calories && $calories['percent'] > 110) ? '#b45309' : '#047857';

    $btnLight = 'inline-flex items-center px-5 py-2.5 rounded-lg bg-white text-emerald-800 text-sm font-medium hover:bg-emerald-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-700';
    $btnOutline = 'inline-flex items-center px-5 py-2.5 rounded-lg border border-white text-white text-sm font-medium hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-700';
    $btnPrimary = 'inline-flex items-center px-5 py-2.5 rounded-lg bg-emerald-700 text-white text-sm font-medium hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2';
@endphp

<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <section class="rounded-2xl p-6 sm:p-8 text-white bg-emerald-700"
                     style="background-image: linear-gradient(135deg, #047857 0%, #0f766e 100%);"
                     aria-labelledby="greeting">
                <h1 id="greeting" class="text-2xl sm:text-3xl font-semibold">{{ $greeting }}, {{ $firstName }}</h1>
                <p class="mt-2 max-w-xl text-emerald-50">
                    @if ($streakDays > 0)
                        You logged meals on {{ $streakDays }} of the last 7 days. Keep it going.
                    @else
                        Start by logging what you ate today. It only takes a minute.
                    @endif
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('patient.meals.index') }}" class="{{ $btnLight }}">Log a meal</a>
                    <a href="{{ route('patient.appointments.index') }}" class="{{ $btnOutline }}">Book a consultation</a>
                </div>
            </section>

            @if (!$targets)
                <section class="bg-white border border-gray-200 rounded-2xl p-6 sm:p-8" aria-labelledby="setup-title">
                    <h2 id="setup-title" class="text-lg font-semibold">Set up your daily targets</h2>
                    <p class="mt-1 text-gray-700 max-w-2xl">
                        Tell us a little about yourself and we will work out how much energy and how many nutrients
                        your body needs each day. Then your dashboard comes alive.
                    </p>

                    <ol class="mt-6 space-y-3 text-gray-700">
                        <li class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-700 text-sm font-semibold text-white" aria-hidden="true">1</span>
                            Complete your health profile
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-200 text-sm font-semibold text-gray-800" aria-hidden="true">2</span>
                            Log what you eat
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-200 text-sm font-semibold text-gray-800" aria-hidden="true">3</span>
                            Book a video consultation
                        </li>
                    </ol>

                    <a href="{{ route('patient.profile.edit') }}" class="mt-6 {{ $btnPrimary }}">Complete health profile</a>
                </section>
            @else
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">

                    <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="energy-title">
                        <h2 id="energy-title" class="text-sm font-medium text-gray-600">Energy today</h2>

                        <div class="mt-4 flex items-center gap-5">
                            <div class="relative h-28 w-28 shrink-0">
                                <svg viewBox="0 0 120 120" class="h-28 w-28 -rotate-90" role="img"
                                     aria-label="{{ $calories['percent'] }} percent of your daily calorie target">
                                    <circle cx="60" cy="60" r="52" fill="none" stroke="#e5e7eb" stroke-width="10" />
                                    <circle cx="60" cy="60" r="52" fill="none" stroke="{{ $ringColor }}" stroke-width="10"
                                            stroke-linecap="round"
                                            stroke-dasharray="{{ round($caloriePercent / 100 * $circumference, 1) }} {{ $circumference }}" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center text-lg font-semibold">
                                    {{ $calories['percent'] }}%
                                </div>
                            </div>

                            <div>
                                <p class="text-2xl font-semibold">{{ number_format($calories['intake']) }}</p>
                                <p class="text-sm text-gray-700">of {{ number_format($calories['target']) }} kcal</p>
                            </div>
                        </div>

                        <p class="mt-4 text-sm text-gray-700">
                            Estimated need (TDEE): {{ number_format($targets['tdee']) }} kcal
                            · BMR: {{ number_format($targets['bmr']) }} kcal
                        </p>
                    </section>

                    <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="next-title">
                        <h2 id="next-title" class="text-sm font-medium text-gray-600">Next consultation</h2>

                        @if ($nextAppointment)
                            <p class="mt-4 text-xl font-semibold">{{ $nextAppointment->scheduled_at->format('D, M j') }}</p>
                            <p class="text-gray-700">
                                {{ $nextAppointment->scheduled_at->format('g:i A') }}
                                with {{ $nextAppointment->dietitian->name }}
                            </p>

                            @if ($nextAppointment->isJoinable())
                                <a href="{{ route('appointments.session', $nextAppointment) }}" class="mt-4 {{ $btnPrimary }}">
                                    Join session now
                                </a>
                            @else
                                <p class="mt-3 text-sm text-gray-700">
                                    You can join from {{ $nextAppointment->joinOpensAt()->format('M j, g:i A') }}.
                                </p>
                                <a href="{{ route('patient.appointments.index') }}"
                                   class="mt-3 inline-block text-sm font-medium text-emerald-800 underline">
                                    Manage appointments
                                </a>
                            @endif
                        @else
                            <p class="mt-4 text-gray-700">You have no upcoming video consultation.</p>
                            <a href="{{ route('patient.appointments.index') }}" class="mt-4 {{ $btnPrimary }}">
                                Book a consultation
                            </a>
                        @endif
                    </section>

                    <section class="bg-white border border-gray-200 rounded-2xl p-6 md:col-span-2 lg:col-span-1" aria-labelledby="week-title">
                        <h2 id="week-title" class="text-sm font-medium text-gray-600">Last 7 days</h2>

                        <p class="mt-4 text-2xl font-semibold">
                            {{ $streakDays }}
                            <span class="text-base font-normal text-gray-700">of 7 days logged</span>
                        </p>

                        <ul class="mt-4 flex justify-between">
                            @foreach ($week as $day)
                                <li class="flex flex-col items-center gap-1 text-xs text-gray-700">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $day['logged'] ? 'bg-emerald-700 text-white' : 'bg-gray-100' }}" aria-hidden="true">
                                        {{ $day['logged'] ? '✓' : '' }}
                                    </span>
                                    <span aria-hidden="true">{{ $day['label'] }}</span>
                                    <span class="sr-only">{{ $day['date'] }}: {{ $day['logged'] ? 'meals logged' : 'no meals logged' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                </div>

                <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="nutrients-title">
                    <div class="flex items-baseline justify-between gap-4">
                        <h2 id="nutrients-title" class="text-lg font-semibold">Today's nutrients</h2>
                        <a href="{{ route('patient.meals.index') }}" class="text-sm font-medium text-emerald-800 underline">Log a meal</a>
                    </div>

                    <div class="mt-6 grid gap-6 sm:grid-cols-2">
                        @foreach ($rows as $row)
                            @php
                                $isLimit = $row['label'] === 'Sodium (limit)';
                                $over = $isLimit ? $row['percent'] > 100 : $row['percent'] > 110;
                                $barColor = $over ? 'bg-amber-600' : 'bg-emerald-600';
                                $note = $isLimit
                                    ? ($over ? 'Over the daily limit' : 'Within the limit')
                                    : ($over ? 'Above target' : ($row['percent'] >= 90 ? 'Target reached' : 'Keep going'));
                            @endphp

                            <div>
                                <div class="flex justify-between gap-2 text-sm">
                                    <span class="font-medium text-gray-900">{{ $row['label'] }}</span>
                                    <span class="text-gray-700">{{ $row['intake'] }} / {{ $row['target'] }} {{ $row['unit'] }}</span>
                                </div>

                                <div class="mt-2 h-2.5 w-full rounded-full bg-gray-100" role="progressbar"
                                     aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ min($row['percent'], 100) }}"
                                     aria-label="{{ $row['label'] }}: {{ $row['percent'] }}% of daily target">
                                    <div class="h-2.5 rounded-full {{ $barColor }}" style="width: {{ min($row['percent'], 100) }}%"></div>
                                </div>

                                <p class="mt-1 text-xs text-gray-700">{{ $row['percent'] }}% · {{ $note }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="chart-title">
                    <h2 id="chart-title" class="text-lg font-semibold">Calories this week</h2>
                    <p class="mt-1 text-sm text-gray-700">
                        Daily calories compared with your target of {{ number_format($targets['calories']) }} kcal.
                    </p>

                    <div class="relative h-72 mt-4" role="img"
                         aria-label="Bar chart of calories eaten each day for the last 7 days compared with the daily target">
                        <canvas id="weekChart"></canvas>
                    </div>

                    <table class="sr-only">
                        <caption>Calories eaten each day for the last 7 days</caption>
                        <thead>
                            <tr>
                                <th scope="col">Day</th>
                                <th scope="col">Calories</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($week as $day)
                                <tr>
                                    <th scope="row">{{ $day['date'] }}</th>
                                    <td>{{ number_format($day['calories']) }} kcal</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>

                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const week = @json($week);
                        const target = @json($targets['calories']);

                        new Chart(document.getElementById('weekChart'), {
                            data: {
                                labels: week.map(d => d.label),
                                datasets: [
                                    {
                                        type: 'bar',
                                        label: 'Calories eaten',
                                        data: week.map(d => d.calories),
                                        backgroundColor: week.map(d => d.calories > target * 1.1 ? '#b45309' : '#047857'),
                                        borderRadius: 6,
                                    },
                                    {
                                        type: 'line',
                                        label: 'Daily target',
                                        data: week.map(() => target),
                                        borderColor: '#6b7280',
                                        borderDash: [6, 4],
                                        borderWidth: 2,
                                        pointRadius: 0,
                                    },
                                ],
                            },
                            options: {
                                maintainAspectRatio: false,
                                plugins: { legend: { position: 'bottom' } },
                                scales: { y: { beginAtZero: true, title: { display: true, text: 'kcal' } } },
                            },
                        });
                    });
                </script>
            @endif
        </div>
    </div>
</x-app-layout>