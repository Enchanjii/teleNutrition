@php
    $sexOptions = ['male' => 'Male', 'female' => 'Female'];
    $activityOptions = [
        'sedentary' => ['Sedentary', 'Little or no exercise'],
        'light' => ['Light', 'Exercise 1-3 days a week'],
        'moderate' => ['Moderate', 'Exercise 3-5 days a week'],
        'active' => ['Active', 'Exercise 6-7 days a week'],
        'very_active' => ['Very active', 'Hard exercise or a physical job'],
    ];
    $currentSex = old('sex', $profile?->sex);
    $currentActivity = old('activity_level', $profile?->activity_level);
    $card = 'block rounded-xl border border-gray-300 p-4 peer-checked:border-emerald-700 peer-checked:bg-emerald-50 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-700 peer-focus-visible:ring-offset-2';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-900">Health profile</h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <p class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-900">
                We use these details only to estimate your daily energy and nutrient needs.
                Your dietitian can also see them.
            </p>

            <form method="POST" action="{{ route('patient.profile.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <section class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5"
                    aria-labelledby="about-title">
                    <h2 id="about-title" class="text-lg font-semibold">About you</h2>

                    <div>
                        <x-input-label for="birth_date" value="Birth date" />
                        <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full sm:w-64"
                            :value="old('birth_date', $profile?->birth_date?->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                    </div>

                    <fieldset>
                        <legend class="font-medium text-sm text-gray-700">Sex</legend>
                        <div class="mt-2 grid grid-cols-2 gap-3 sm:max-w-sm">
                            @foreach ($sexOptions as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="sex" value="{{ $value }}" class="peer sr-only"
                                        @checked($currentSex === $value) required>
                                    <span class="{{ $card }} text-center font-medium text-gray-900">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('sex')" class="mt-2" />
                    </fieldset>
                </section>

                <section class="bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="body-title">
                    <h2 id="body-title" class="text-lg font-semibold">Body measurements</h2>

                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
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
                    </div>
                </section>

                <section class="bg-white border border-gray-200 rounded-2xl p-6">
                    <fieldset>
                        <legend class="text-lg font-semibold">Activity level</legend>
                        <p class="mt-1 text-sm text-gray-700">Pick the one that fits a typical week.</p>

                        <div class="mt-4 grid gap-3">
                            @foreach ($activityOptions as $value => [$title, $description])
                                <label class="cursor-pointer">
                                    <input type="radio" name="activity_level" value="{{ $value }}" class="peer sr-only"
                                        @checked($currentActivity === $value) required>
                                    <span class="{{ $card }}">
                                        <span class="block font-medium text-gray-900">{{ $title }}</span>
                                        <span class="block text-sm text-gray-700">{{ $description }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('activity_level')" class="mt-2" />
                    </fieldset>
                </section>

                <div class="flex items-center gap-4">
                    <x-primary-button>Save profile</x-primary-button>
                    <a href="{{ route('patient.dashboard') }}"
                        class="text-sm font-medium text-emerald-800 underline">Back to dashboard</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>