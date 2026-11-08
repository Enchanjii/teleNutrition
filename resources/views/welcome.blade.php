<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Log your meals, see your daily nutrition targets, and meet your dietitian by video.">
    <title>{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col">

        <header class="max-w-5xl w-full mx-auto px-6 py-6 flex items-center justify-between">
            <a href="{{ url('/') }}"
                class="text-lg font-semibold text-emerald-800 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
                {{ config('app.name') }}
            </a>

            <nav aria-label="Account" class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="px-4 py-2 text-sm font-medium text-white bg-emerald-700 rounded-md hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-700">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-800 rounded-md hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
                        Log in
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="px-4 py-2 text-sm font-medium text-white bg-emerald-700 rounded-md hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-700">
                            Register
                        </a>
                    @endif
                @endauth
            </nav>
        </header>

        <main class="flex-1">
            <section class="max-w-5xl mx-auto px-6 py-16 sm:py-24 text-center">
                <h1 class="text-4xl sm:text-5xl font-semibold tracking-tight text-gray-900">
                    Nutrition care, wherever you are.
                </h1>
                <p class="mt-6 max-w-2xl mx-auto text-lg text-gray-700">
                    Log your meals, see how you compare with your daily targets,
                    and meet your dietitian by video.
                </p>

                <div class="mt-10 flex flex-wrap justify-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-emerald-700 rounded-md hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-700">
                            Go to your dashboard
                        </a>
                    @else
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-emerald-700 rounded-md hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-700">
                                Get started
                            </a>
                        @endif
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center px-6 py-3 text-base font-medium text-gray-900 bg-white border border-gray-300 rounded-md hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-700">
                            Log in
                        </a>
                    @endauth
                </div>
            </section>

            <section class="max-w-5xl mx-auto px-6 pb-20" aria-labelledby="how-it-works">
                <h2 id="how-it-works" class="text-2xl font-semibold text-center text-gray-900">How it works</h2>

                <div class="mt-10 grid gap-6 sm:grid-cols-3">
                    <article class="bg-white border border-gray-200 rounded-lg p-6">
                        <p class="text-sm font-semibold text-emerald-800">Step 1</p>
                        <h3 class="mt-1 text-lg font-medium text-gray-900">Log your meals</h3>
                        <p class="mt-2 text-gray-700">
                            Record what you eat and see your daily calories and nutrients add up.
                        </p>
                    </article>

                    <article class="bg-white border border-gray-200 rounded-lg p-6">
                        <p class="text-sm font-semibold text-emerald-800">Step 2</p>
                        <h3 class="mt-1 text-lg font-medium text-gray-900">Know your targets</h3>
                        <p class="mt-2 text-gray-700">
                            Your health profile sets personal daily targets, and a chart shows how you are doing.
                        </p>
                    </article>

                    <article class="bg-white border border-gray-200 rounded-lg p-6">
                        <p class="text-sm font-semibold text-emerald-800">Step 3</p>
                        <h3 class="mt-1 text-lg font-medium text-gray-900">Meet your dietitian</h3>
                        <p class="mt-2 text-gray-700">
                            Book a time and join a secure video consultation from any device.
                        </p>
                    </article>
                </div>
            </section>
        </main>

        <footer class="max-w-5xl w-full mx-auto px-6 py-8 text-sm text-gray-600 text-center">
            {{ config('app.name') }} is a school project. It does not replace advice from a licensed health
            professional.
        </footer>
    </div>
</body>

</html>