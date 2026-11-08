@php
    $mealTypes = ['breakfast' => 'Breakfast', 'lunch' => 'Lunch', 'dinner' => 'Dinner', 'snack' => 'Snack'];
    $selectedMeal = old('meal_type', $defaultMealType);
    $selectClass = 'mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-emerald-600 focus:ring-emerald-600';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-900">Meal log</h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <div class="grid gap-6 lg:grid-cols-5">

                <section class="lg:col-span-3 bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="log-title"
                         x-data="{
                            foods: @js($foods),
                            search: '',
                            foodId: @js((string) old('food_id', '')),
                            quantity: @js((string) old('quantity', '1')),
                            get filtered() {
                                const q = this.search.trim().toLowerCase();
                                if (q === '') { return this.foods; }
                                return this.foods.filter(f => f.name.toLowerCase().includes(q) || String(f.id) === this.foodId);
                            },
                            get selected() {
                                return this.foods.find(f => String(f.id) === this.foodId) ?? null;
                            },
                            get qty() {
                                const n = parseFloat(this.quantity);
                                return Number.isFinite(n) && n > 0 ? n : 0;
                            },
                            total(key) {
                                return this.selected ? Math.round(this.selected[key] * this.qty * 10) / 10 : 0;
                            },
                         }">
                    <h2 id="log-title" class="text-lg font-semibold">Log a meal</h2>

                    <form method="POST" action="{{ route('patient.meals.store') }}" class="mt-6 space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="food_search" value="Search foods" />
                            <x-text-input id="food_search" type="search" class="mt-1 block w-full"
                                          x-model="search" placeholder="Type to filter, for example rice" autocomplete="off" />
                        </div>

                        <div>
                            <x-input-label for="food_id" value="Food" />
                            <select id="food_id" name="food_id" x-model="foodId" required class="{{ $selectClass }}">
                                <option value="">Select a food</option>
                                <template x-for="food in filtered" :key="food.id">
                                    <option :value="String(food.id)" x-text="food.name"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-sm text-gray-700" x-show="filtered.length === 0">No foods match your search.</p>
                            <x-input-error :messages="$errors->get('food_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="quantity" value="Servings (1 serving = 100 g)" />
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <x-text-input id="quantity" name="quantity" type="number" step="0.1" min="0.1" max="50"
                                              class="w-28" x-model="quantity" required />
                                @foreach (['0.5', '1', '2', '3'] as $preset)
                                    <button type="button" x-on:click="quantity = '{{ $preset }}'"
                                            aria-label="Set servings to {{ $preset }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-800 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
                                        {{ $preset }}×
                                    </button>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                        </div>

                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4" aria-live="polite">
                            <p class="text-sm font-medium text-emerald-900">This portion adds</p>
                            <p class="mt-1 text-sm text-gray-700" x-show="!selected">Pick a food to preview its nutrition.</p>
                            <dl class="mt-2 grid grid-cols-4 gap-3 text-center" x-show="selected">
                                <div>
                                    <dt class="text-xs text-gray-700">Calories</dt>
                                    <dd class="text-lg font-semibold text-gray-900"><span x-text="Math.round(total('calories'))"></span></dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-700">Protein</dt>
                                    <dd class="text-lg font-semibold text-gray-900"><span x-text="total('protein')"></span> g</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-700">Carbs</dt>
                                    <dd class="text-lg font-semibold text-gray-900"><span x-text="total('carbs')"></span> g</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-700">Fat</dt>
                                    <dd class="text-lg font-semibold text-gray-900"><span x-text="total('fat')"></span> g</dd>
                                </div>
                            </dl>
                        </div>

                        <fieldset>
                            <legend class="font-medium text-sm text-gray-700">Meal</legend>
                            <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                                @foreach ($mealTypes as $value => $label)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="meal_type" value="{{ $value }}" class="peer sr-only"
                                               @checked($selectedMeal === $value) required>
                                        <span class="block rounded-lg border border-gray-300 px-3 py-2 text-center text-sm font-medium text-gray-800 peer-checked:border-emerald-700 peer-checked:bg-emerald-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-700 peer-focus-visible:ring-offset-2">
                                            {{ $label }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('meal_type')" class="mt-2" />
                        </fieldset>

                        <div>
                            <x-input-label for="logged_at" value="When did you eat this?" />
                            <x-text-input id="logged_at" name="logged_at" type="datetime-local" class="mt-1 block w-full"
                                          :value="old('logged_at', now()->format('Y-m-d\TH:i'))" required />
                            <x-input-error :messages="$errors->get('logged_at')" class="mt-2" />
                        </div>

                        <x-primary-button class="w-full sm:w-auto">Save meal</x-primary-button>
                    </form>
                </section>

                <section class="lg:col-span-2 bg-white border border-gray-200 rounded-2xl p-6" aria-labelledby="today-title">
                    <div class="flex items-baseline justify-between gap-3">
                        <h2 id="today-title" class="text-lg font-semibold">Today's meals</h2>
                        <p class="text-sm text-gray-700">{{ number_format($totalCalories) }} kcal</p>
                    </div>

                    @forelse ($groups as $group)
                        <div class="mt-5">
                            <div class="flex items-baseline justify-between gap-3">
                                <h3 class="text-sm font-semibold text-emerald-900">{{ ucfirst($group['type']) }}</h3>
                                <p class="text-sm text-gray-700">{{ number_format($group['calories']) }} kcal</p>
                            </div>

                            <ul class="mt-2 divide-y divide-gray-100">
                                @foreach ($group['items'] as $item)
                                    <li class="flex items-start justify-between gap-3 py-2 text-sm">
                                        <div>
                                            <p class="text-gray-900">{{ $item['name'] }}</p>
                                            <p class="text-gray-700">{{ $item['quantity'] }} serving(s) · {{ $item['time'] }}</p>
                                        </div>
                                        <p class="shrink-0 text-gray-700">{{ number_format($item['calories']) }} kcal</p>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <div class="mt-6 rounded-xl border border-dashed border-gray-300 p-6 text-center">
                            <p class="font-medium text-gray-900">Nothing logged yet today</p>
                            <p class="mt-1 text-sm text-gray-700">Your first meal takes about a minute to add.</p>
                        </div>
                    @endforelse

                    <a href="{{ route('patient.dashboard') }}"
                       class="mt-6 inline-block text-sm font-medium text-emerald-800 underline">See how today compares with your targets</a>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>