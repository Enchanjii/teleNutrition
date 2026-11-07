<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Patient Dashboard</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <p>Welcome, {{ auth()->user()->name }}.</p>
                <div class="flex gap-3">
                    <a href="{{ route('patient.profile.edit') }}"
                        class="inline-block px-4 py-2 bg-gray-800 text-white rounded-md">Health profile</a>
                    <a href="{{ route('patient.meals.index') }}"
                        class="inline-block px-4 py-2 bg-gray-800 text-white rounded-md">Log a meal</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>