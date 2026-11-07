<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Meal Log</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 p-4 rounded" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Log a meal</h3>

                <form method="POST" action="{{ route('patient.meals.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="food_id" value="Food" />
                        <select id="food_id" name="food_id" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Select a food</option>
                            @foreach ($foods as $food)
                                <option value="{{ $food->id }}" @selected(old('food_id') == $food->id)>
                                    {{ $food->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('food_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="quantity" value="Servings (1 serving = 100 g)" />
                        <x-text-input id="quantity" name="quantity" type="number" step="0.1" min="0.1"
                            class="mt-1 block w-full" :value="old('quantity', 1)" required />
                        <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="meal_type" value="Meal type" />
                        <select id="meal_type" name="meal_type" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach (['breakfast', 'lunch', 'dinner', 'snack'] as $type)
                                <option value="{{ $type }}" @selected(old('meal_type') === $type)>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('meal_type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="logged_at" value="Date and time" />
                        <x-text-input id="logged_at" name="logged_at" type="datetime-local" class="mt-1 block w-full"
                            :value="old('logged_at', now()->format('Y-m-d\TH:i'))" required />
                        <x-input-error :messages="$errors->get('logged_at')" class="mt-2" />
                    </div>

                    <x-primary-button>Save meal</x-primary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Today's meals</h3>

                @forelse ($logs as $log)
                    <div class="flex justify-between border-b py-2">
                        <span>{{ ucfirst($log->meal_type) }}: {{ $log->food->name }}</span>
                        <span class="text-gray-600">
                            {{ rtrim(rtrim(number_format($log->quantity, 2), '0'), '.') }} serving(s)
                            · {{ $log->logged_at->format('g:i A') }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-600">No meals logged today.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>