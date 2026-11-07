<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Patient Chart: {{ $patient->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <a href="{{ route('dietitian.dashboard') }}" class="underline text-sm text-blue-700">Back to patients</a>

            @if (session('status'))
                <div class="bg-green-100 text-green-800 p-4 rounded" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('dietitian.patients.show', $patient) }}" class="flex items-end gap-3">
                    <div>
                        <x-input-label for="date" value="Review date" />
                        <x-text-input id="date" name="date" type="date" class="mt-1 block"
                                      :value="$date->format('Y-m-d')" :max="today()->format('Y-m-d')" />
                        <x-input-error :messages="$errors->get('date')" class="mt-2" />
                    </div>
                    <x-primary-button>View</x-primary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-2">Patient profile</h3>

                @if (! $profile)
                    <p>This patient has not completed a health profile yet.</p>
                @else
                    <p class="text-sm">
                        Age {{ $profile->birth_date->age }}
                        · {{ ucfirst($profile->sex) }}
                        · {{ (float) $profile->weight_kg }} kg
                        · {{ (float) $profile->height_cm }} cm
                        · Activity: {{ str_replace('_', ' ', $profile->activity_level) }}
                    </p>
                    <p class="text-sm mt-1">
                        BMR {{ number_format($targets['bmr']) }} kcal
                        · TDEE {{ number_format($targets['tdee']) }} kcal
                    </p>
                @endif
            </div>

            @if ($profile)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-2">Intake on {{ $date->format('F j, Y') }}</h3>

                    <table class="w-full text-left text-sm">
                        <caption class="sr-only">Nutrient intake compared with daily targets</caption>
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

                    <h4 class="font-medium mt-6 mb-2">Decision-support flags</h4>
                    <div class="space-y-1 text-sm">
                        @forelse ($flags as $flag)
                            <p>
                                <strong>{{ ucfirst($flag['severity']) }}:</strong>
                                {{ $flag['term'] }}. {{ $flag['message'] }}
                            </p>
                        @empty
                            <p>No flags.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-2">Meals logged on {{ $date->format('F j, Y') }}</h3>

                @forelse ($logs as $log)
                    <div class="flex justify-between border-b py-2 text-sm">
                        <span>{{ ucfirst($log->meal_type) }}: {{ $log->food->name }}</span>
                        <span>
                            {{ rtrim(rtrim(number_format($log->quantity, 2), '0'), '.') }} serving(s)
                            · {{ $log->logged_at->format('g:i A') }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm">No meals logged on this date.</p>
                @endforelse
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">New chart note (ADIME)</h3>

                <form method="POST" action="{{ route('dietitian.patients.notes.store', $patient) }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="note_date" value="Note date" />
                        <x-text-input id="note_date" name="note_date" type="date" class="mt-1 block"
                                      :value="old('note_date', $date->format('Y-m-d'))"
                                      :max="today()->format('Y-m-d')" required />
                        <x-input-error :messages="$errors->get('note_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="assessment" value="Assessment" />
                        <textarea id="assessment" name="assessment" rows="3" required
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('assessment') }}</textarea>
                        <x-input-error :messages="$errors->get('assessment')" class="mt-2" />
                    </div>

                    <fieldset class="space-y-4">
                        <legend class="font-medium">Diagnosis (PES statement)</legend>

                        <div>
                            <x-input-label for="diagnosis_term" value="Nutrition diagnosis (problem)" />
                            <select id="diagnosis_term" name="diagnosis_term" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select</option>
                                @foreach (\App\Models\ClinicalNote::DIAGNOSES as $term)
                                    <option value="{{ $term }}"
                                        @selected(old('diagnosis_term', $suggestedDiagnosis) === $term)>
                                        {{ $term }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('diagnosis_term')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="etiology" value="Related to (etiology)" />
                            <textarea id="etiology" name="etiology" rows="2" required
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('etiology') }}</textarea>
                            <x-input-error :messages="$errors->get('etiology')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="signs_symptoms" value="As evidenced by (signs and symptoms)" />
                            <textarea id="signs_symptoms" name="signs_symptoms" rows="2" required
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('signs_symptoms') }}</textarea>
                            <x-input-error :messages="$errors->get('signs_symptoms')" class="mt-2" />
                        </div>
                    </fieldset>

                    <div>
                        <x-input-label for="intervention" value="Intervention" />
                        <textarea id="intervention" name="intervention" rows="3" required
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('intervention') }}</textarea>
                        <x-input-error :messages="$errors->get('intervention')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="monitoring_evaluation" value="Monitoring and evaluation" />
                        <textarea id="monitoring_evaluation" name="monitoring_evaluation" rows="3" required
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('monitoring_evaluation') }}</textarea>
                        <x-input-error :messages="$errors->get('monitoring_evaluation')" class="mt-2" />
                    </div>

                    <x-primary-button>Save note</x-primary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Chart note history</h3>

                @forelse ($notes as $note)
                    <article class="border-t py-4 space-y-2 text-sm">
                        <h4 class="font-medium">
                            {{ $note->note_date->format('F j, Y') }} · by {{ $note->dietitian->name }}
                        </h4>
                        <p class="whitespace-pre-line"><strong>Assessment:</strong> {{ $note->assessment }}</p>
                        <p class="whitespace-pre-line">
                            <strong>Diagnosis:</strong>
                            {{ $note->diagnosis_term }} related to {{ $note->etiology }}
                            as evidenced by {{ $note->signs_symptoms }}
                        </p>
                        <p class="whitespace-pre-line"><strong>Intervention:</strong> {{ $note->intervention }}</p>
                        <p class="whitespace-pre-line"><strong>Monitoring and evaluation:</strong> {{ $note->monitoring_evaluation }}</p>
                    </article>
                @empty
                    <p class="text-sm">No chart notes yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>