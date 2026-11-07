<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Health Profile</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 p-4 rounded" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('patient.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="birth_date" value="Birth date" />
                        <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full"
                            :value="old('birth_date', $profile?->birth_date?->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="sex" value="Sex" />
                        <select id="sex" name="sex" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Select</option>
                            @foreach (['male' => 'Male', 'female' => 'Female'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('sex', $profile?->sex) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('sex')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="weight_kg" value="Weight (kg)" />
                        <x-text-input id="weight_kg" name="weight_kg" type="number" step="0.1" min="20" max="300"
                            class="mt-1 block w-full" :value="old('weight_kg', $profile?->weight_kg)" required />
                        <x-input-error :messages="$errors->get('weight_kg')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="height_cm" value="Height (cm)" />
                        <x-text-input id="height_cm" name="height_cm" type="number" step="0.1" min="80" max="250"
                            class="mt-1 block w-full" :value="old('height_cm', $profile?->height_cm)" required />
                        <x-input-error :messages="$errors->get('height_cm')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="activity_level" value="Activity level" />
                        <select id="activity_level" name="activity_level" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Select</option>
                            @foreach ([
                                    'sedentary' => 'Sedentary (little or no exercise)',
                                    'light' => 'Light (exercise 1-3 days/week)',
                                    'moderate' => 'Moderate (exercise 3-5 days/week)',
                                    'active' => 'Active (exercise 6-7 days/week)',
                                    'very_active' => 'Very active (hard exercise or physical job)',
                                ] as $value => $label)
                                <option value="{{ $value }}" @selected(old('activity_level', $profile?->activity_level) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('activity_level')" class="mt-2" />
                    </div>

                    <x-primary-button>Save profile</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>