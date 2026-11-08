@php
    $badge = [
        'high' => 'bg-red-50 text-red-900 border-red-200',
        'moderate' => 'bg-amber-50 text-amber-900 border-amber-200',
        'info' => 'bg-gray-100 text-gray-800 border-gray-200',
    ];

    $area = 'mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-emerald-600 focus:ring-emerald-600';
    $dateParam = $date->format('Y-m-d');
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-900">Patient chart: {{ $patient->name }}</h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <a href="{{ route('dietitian.dashboard', ['date' => $dateParam]) }}"
               class="inline-block text-sm font-medium text-emerald-800 underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
                Back to patients
            </a>

            <x-flash />

            <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="profile-title">
                <div class="flex flex-wrap items-start justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xl font-semibold text-emerald-900" aria-hidden="true">
                            {{ strtoupper(mb_substr($patient->name, 0, 1)) }}
                        </span>
                        <div>
                            <h2 id="profile-title" class="text-lg font-semibold text-gray-900">{{ $patient->name }}</h2>

                            @if ($profile)
                                <p class="text-sm text-gray-700">
                                    Age {{ $profile->birth_date->age }}
                                    · {{ ucfirst($profile->sex) }}
                                    · {{ (float) $profile->weight_kg }} kg
                                    · {{ (float) $profile->height_cm }} cm
                                </p>
                                <p class="text-sm text-gray-700">
                                    Activity: {{ str_replace('_', ' ', $profile->activity_level) }}
                                    · BMR {{ number_format($targets['bmr']) }} kcal
                                    · TDEE {{ number_format($targets['tdee']) }} kcal
                                </p>
                            @else
                                <p class="text-sm text-gray-700">This patient has not completed a health profile yet.</p>
                            @endif
                        </div>
                    </div>

                    <form method="GET" action="{{ route('dietitian.patients.show', $patient) }}" class="flex items-end gap-3">
                        <div>
                            <x-input-label for="date" value="Review date" />
                            <x-text-input id="date" name="date" type="date" class="mt-1 block"
                                          :value="$dateParam" :max="today()->format('Y-m-d')" />
                            <x-input-error :messages="$errors->get('date')" class="mt-2" />
                        </div>
                        <x-primary-button>View</x-primary-button>
                    </form>
                </div>
            </section>

            @if ($profile)
                <div class="grid gap-6 lg:grid-cols-3">
                    <section class="lg:col-span-2 bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="intake-title">
                        <h2 id="intake-title" class="text-lg font-semibold">Intake on {{ $date->format('F j, Y') }}</h2>

                        <div class="mt-6 grid gap-6 sm:grid-cols-2">
                            @foreach ($rows as $row)
                                <x-nutrient-bar :row="$row" />
                            @endforeach
                        </div>
                    </section>

                    <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="flags-title">
                        <h2 id="flags-title" class="text-lg font-semibold">Decision support</h2>
                        <p class="mt-1 text-sm text-gray-700">Suggestions for your review, not diagnoses.</p>

                        @if (count($flags) === 0)
                            <p class="mt-5 rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-900">
                                No flags for this day.
                            </p>
                        @else
                            <ul class="mt-5 space-y-4">
                                @foreach ($flags as $flag)
                                    <li>
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $badge[$flag['severity']] ?? $badge['info'] }}">
                                            {{ ucfirst($flag['severity']) }} · {{ $flag['term'] }}
                                        </span>
                                        <p class="mt-1 text-sm text-gray-700">{{ $flag['message'] }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                </div>
            @endif

            <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="meals-title">
                <h2 id="meals-title" class="text-lg font-semibold">Meals logged on {{ $date->format('F j, Y') }}</h2>

                @forelse ($logs as $log)
                    <div class="flex items-start justify-between gap-3 border-t border-gray-100 py-3 text-sm first:mt-4">
                        <div>
                            <p class="text-gray-900">{{ $log->food->name }}</p>
                            <p class="text-gray-700">{{ ucfirst($log->meal_type) }} · {{ $log->logged_at->format('g:i A') }}</p>
                        </div>
                        <p class="shrink-0 text-gray-700">
                            {{ rtrim(rtrim(number_format($log->quantity, 2), '0'), '.') }} serving(s)
                        </p>
                    </div>
                @empty
                    <p class="mt-4 rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-700">
                        No meals logged on this date.
                    </p>
                @endforelse
            </section>

            <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="note-title">
                <h2 id="note-title" class="text-lg font-semibold">New chart note (ADIME)</h2>
                <p class="mt-1 text-sm text-gray-700">
                    Assessment, Diagnosis, Intervention, Monitoring and evaluation.
                </p>

                <form method="POST" action="{{ route('dietitian.patients.notes.store', $patient) }}" class="mt-6 space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="note_date" value="Note date" />
                        <x-text-input id="note_date" name="note_date" type="date" class="mt-1 block w-full sm:w-64"
                                      :value="old('note_date', $dateParam)" :max="today()->format('Y-m-d')" required />
                        <x-input-error :messages="$errors->get('note_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="assessment" value="Assessment" />
                        <textarea id="assessment" name="assessment" rows="3" required class="{{ $area }}">{{ old('assessment') }}</textarea>
                        <x-input-error :messages="$errors->get('assessment')" class="mt-2" />
                    </div>

                    <fieldset class="space-y-5 rounded-xl border border-gray-200 p-5">
                        <legend class="px-2 text-sm font-semibold text-gray-900">Diagnosis (PES statement)</legend>

                        <div>
                            <x-input-label for="diagnosis_term" value="Nutrition diagnosis (problem)" />
                            <select id="diagnosis_term" name="diagnosis_term" required class="{{ $area }}">
                                <option value="">Select</option>
                                @foreach (\App\Models\ClinicalNote::DIAGNOSES as $term)
                                    <option value="{{ $term }}" @selected(old('diagnosis_term', $suggestedDiagnosis) === $term)>
                                        {{ $term }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($suggestedDiagnosis)
                                <p class="mt-1 text-sm text-gray-700">Preselected from today's decision-support flags. Change it if needed.</p>
                            @endif
                            <x-input-error :messages="$errors->get('diagnosis_term')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="etiology" value="Related to (etiology)" />
                            <textarea id="etiology" name="etiology" rows="2" required class="{{ $area }}">{{ old('etiology') }}</textarea>
                            <x-input-error :messages="$errors->get('etiology')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="signs_symptoms" value="As evidenced by (signs and symptoms)" />
                            <textarea id="signs_symptoms" name="signs_symptoms" rows="2" required class="{{ $area }}">{{ old('signs_symptoms') }}</textarea>
                            <x-input-error :messages="$errors->get('signs_symptoms')" class="mt-2" />
                        </div>
                    </fieldset>

                    <div>
                        <x-input-label for="intervention" value="Intervention" />
                        <textarea id="intervention" name="intervention" rows="3" required class="{{ $area }}">{{ old('intervention') }}</textarea>
                        <x-input-error :messages="$errors->get('intervention')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="monitoring_evaluation" value="Monitoring and evaluation" />
                        <textarea id="monitoring_evaluation" name="monitoring_evaluation" rows="3" required class="{{ $area }}">{{ old('monitoring_evaluation') }}</textarea>
                        <x-input-error :messages="$errors->get('monitoring_evaluation')" class="mt-2" />
                    </div>

                    <x-primary-button>Save note</x-primary-button>
                </form>
            </section>

            <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="history-title">
                <h2 id="history-title" class="text-lg font-semibold">Chart note history</h2>

                @forelse ($notes as $note)
                    <article class="mt-5 rounded-xl border border-gray-200 p-5 text-sm space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="font-semibold text-gray-900">{{ $note->note_date->format('F j, Y') }}</h3>
                            <p class="text-gray-700">by {{ $note->dietitian->name }}</p>
                        </div>

                        <p class="whitespace-pre-line"><strong>Assessment:</strong> {{ $note->assessment }}</p>

                        <p class="whitespace-pre-line rounded-lg bg-emerald-50 p-3 text-emerald-950">
                            <strong>Diagnosis:</strong>
                            {{ $note->diagnosis_term }} related to {{ $note->etiology }}
                            as evidenced by {{ $note->signs_symptoms }}
                        </p>

                        <p class="whitespace-pre-line"><strong>Intervention:</strong> {{ $note->intervention }}</p>
                        <p class="whitespace-pre-line"><strong>Monitoring and evaluation:</strong> {{ $note->monitoring_evaluation }}</p>
                    </article>
                @empty
                    <p class="mt-4 rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-700">
                        No chart notes yet.
                    </p>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>