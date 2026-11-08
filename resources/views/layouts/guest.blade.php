<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TeleNutrition') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-900"
    style="font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;">
    @php
        $heading = request()->routeIs('register')
            ? 'Create your account'
            : (request()->routeIs('login')
                ? 'Welcome back'
                : (request()->routeIs('password.*') ? 'Reset your password' : config('app.name')));
    @endphp

    <div class="min-h-screen grid lg:grid-cols-2">
        <aside class="hidden lg:flex flex-col justify-between p-12 text-white bg-emerald-800"
            style="background-image: linear-gradient(135deg, #047857 0%, #115e59 100%);">
            <a href="/"
                class="flex items-center gap-3 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                <x-application-logo class="h-10 w-10 text-emerald-900" />
                <span class="text-xl font-semibold">{{ config('app.name') }}</span>
            </a>

            <div>
                <p class="text-4xl font-semibold leading-tight">Nutrition care, wherever you are.</p>
                <ul class="mt-8 space-y-4 text-emerald-50">
                    @foreach (['Log your meals and see your daily targets', 'Get guidance from your dietitian', 'Meet by secure video, from any device'] as $benefit)
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-200" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M16.704 5.296a1 1 0 010 1.414l-7.5 7.5a1 1 0 01-1.414 0l-3.5-3.5a1 1 0 011.414-1.414L8.5 12.086l6.793-6.79a1 1 0 011.411 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>{{ $benefit }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="text-sm text-emerald-100">A school project. It does not replace advice from a licensed health
                professional.</p>
        </aside>

        <main class="flex flex-col justify-center px-6 py-12 sm:px-12 bg-gray-50">
            <div class="mx-auto w-full max-w-md">
                <a href="/"
                    class="lg:hidden mb-8 flex items-center gap-3 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
                    <x-application-logo class="h-10 w-10 text-emerald-700" />
                    <span class="text-xl font-semibold text-gray-900">{{ config('app.name') }}</span>
                </a>

                <h1 class="text-2xl font-semibold text-gray-900">{{ $heading }}</h1>

                <div class="mt-6 bg-white border border-gray-200 shadow-sm rounded-2xl p-6 sm:p-8">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>
</body>

</html>