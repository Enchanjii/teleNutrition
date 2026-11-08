@php
    $total = $rows->count();
    $flagged = $rows->filter(fn ($r) => $r['has_profile'] && collect($r['flags'])->contains(fn ($f) => $f['severity'] !== 'info'))->count();
    $noLogs = $rows->filter(fn ($r) => $r['has_profile'] && $r['intake']['calories'] <= 0)->count();
    $noProfile = $rows->where('has_profile', false)->count();

    $stats = [
        ['Patients', $total, 'text-gray-900'],
        ['Need attention', $flagged, $flagged > 0 ? 'text-amber-800' : 'text-gray-900'],
        ['No meals logged', $noLogs, 'text-gray-900'],
        ['Profile incomplete', $noProfile, 'text-gray-900'],
    ];

    $badge = [
        'high' => 'bg-red-50 text-red-900 border-red-200',
        'moderate' => 'bg-amber-50 text-amber-900 border-amber-200',
        'info' => 'bg-gray-100 text-gray-800 border-gray-200',
    ];

    $dateParam = $date->format('Y-m-d');
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-900">Patients</h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <section aria-label="Summary for {{ $date->format('F j, Y') }}">
                <dl class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    @foreach ($stats as [$label, $value, $color])
                        <div class="bg-white border border-gray-200 rounded-2xl p-5">
                            <dt class="text-sm font-medium text-gray-700">{{ $label }}</dt>
                            <dd class="mt-2 text-3xl font-semibold {{ $color }}">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="review-title">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 id="review-title" class="text-lg font-semibold">Review a day</h2>
                        <p class="mt-1 text-sm text-gray-700">
                            Flags are suggestions for your review, not diagnoses.
                            Low-intake flags are most meaningful for completed days.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-end gap-3">
                        <form method="GET" action="{{ route('dietitian.dashboard') }}" class="flex items-end gap-3">
                            <div>
                                <x-input-label for="date" value="Review date" />
                                <x-text-input id="date" name="date" type="date" class="mt-1 block"
                                              :value="$dateParam" :max="today()->format('Y-m-d')" />
                                <x-input-error :messages="$errors->get('date')" class="mt-2" />
                            </div>
                            <x-primary-button>View</x-primary-button>
                        </form>

                        <a href="{{ route('dietitian.export', ['date' => $dateParam]) }}"
                           class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2">
                            Export de-identified CSV
                        </a>
                    </div>
                </div>
            </section>

            <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="patients-title">
                <h2 id="patients-title" class="text-lg font-semibold">Patients on {{ $date->format('F j, Y') }}</h2>

                @if ($rows->isEmpty())
                    <div class="mt-5 rounded-xl border border-dashed border-gray-300 p-8 text-center">
                        <p class="font-medium text-gray-900">No patients registered yet</p>
                        <p class="mt-1 text-sm text-gray-700">Patients appear here after they create an account.</p>
                    </div>
                @else
                    <div class="mt-5 overflow-x-auto">
                        <table class="w-full min-w-[720px] text-left text-sm">
                            <caption class="sr-only">
                                Patients with calorie intake and decision-support flags for {{ $date->format('F j, Y') }}
                            </caption>
                            <thead>
                                <tr class="text-gray-700">
                                    <th scope="col" class="py-2 pe-4 font-medium">Patient</th>
                                    <th scope="col" class="py-2 pe-4 font-medium">Calories</th>
                                    <th scope="col" class="py-2 pe-4 font-medium">Flags</th>
                                    <th scope="col" class="py-2 font-medium"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $row)
                                    @php
                                        $chartUrl = route('dietitian.patients.show', ['patient' => $row['patient'], 'date' => $dateParam]);
                                        $pct = 0;
                                        if ($row['has_profile'] && $row['targets']['calories'] > 0) {
                                            $pct = round($row['intake']['calories'] / $row['targets']['calories'] * 100);
                                        }
                                    @endphp

                                    <tr class="border-t border-gray-100 align-top">
                                        <th scope="row" class="py-4 pe-4 font-normal">
                                            <div class="flex items-center gap-3">
                                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-semibold text-emerald-900" aria-hidden="true">
                                                    {{ strtoupper(mb_substr($row['patient']->name, 0, 1)) }}
                                                </span>
                                                <a href="{{ $chartUrl }}"
                                                   class="font-medium text-gray-900 underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
                                                    {{ $row['patient']->name }}
                                                </a>
                                            </div>
                                        </th>

                                        @if (! $row['has_profile'])
                                            <td class="py-4 pe-4 text-gray-700" colspan="2">Health profile not completed.</td>
                                        @else
                                            <td class="py-4 pe-4">
                                                <p class="text-gray-900">
                                                    {{ number_format($row['intake']['calories']) }}
                                                    / {{ number_format($row['targets']['calories']) }} kcal
                                                </p>
                                                <div class="mt-2 h-2 w-40 rounded-full bg-gray-100" aria-hidden="true">
                                                    <div class="h-2 rounded-full {{ $pct > 110 ? 'bg-amber-600' : 'bg-emerald-600' }}"
                                                         style="width: {{ min($pct, 100) }}%"></div>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-700">{{ $pct }}% of target</p>
                                            </td>

                                            <td class="py-4 pe-4">
                                                @if (count($row['flags']) === 0)
                                                    <p class="text-gray-700">No flags</p>
                                                @else
                                                    <ul class="space-y-2">
                                                        @foreach ($row['flags'] as $flag)
                                                            <li>
                                                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $badge[$flag['severity']] ?? $badge['info'] }}">
                                                                    {{ ucfirst($flag['severity']) }} · {{ $flag['term'] }}
                                                                </span>
                                                                <span class="mt-1 block text-xs text-gray-700">{{ $flag['message'] }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                        @endif

                                        <td class="py-4 text-right">
                                            <a href="{{ $chartUrl }}"
                                               aria-label="Open chart for {{ $row['patient']->name }}"
                                               class="inline-flex items-center rounded-lg bg-emerald-700 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2">
                                                Open chart
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>