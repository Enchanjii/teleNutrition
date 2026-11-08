@php
    $features = [
        [
            'Log meals in seconds',
            'Search the food list, choose a portion and preview the nutrition before you save.',
            '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3.5"/>',
        ],
        [
            'Targets made for you',
            'Your profile sets daily calorie and nutrient targets, and a chart shows how your week is going.',
            '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.2"/>',
        ],
        [
            'Insights for your dietitian',
            'Your dietitian sees helpful flags, like high sodium or low protein, and writes structured care notes.',
            '<path d="M6 21V4"/><path d="M6 5h11l-2 4 2 4H6"/>',
        ],
        [
            'Video visits',
            'Book a time, then join a secure video consultation right in your browser.',
            '<rect x="3" y="6" width="12" height="12" rx="2"/><path d="M15 10l6-3v10l-6-3"/>',
        ],
        [
            'Privacy by design',
            'Exports are de-identified, access depends on your role, and views of patient records are logged.',
            '<rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/>',
        ],
        [
            'Works on any device',
            'Nothing to install. Use your phone, tablet or laptop.',
            '<rect x="7" y="3" width="10" height="18" rx="2"/><path d="M11 18h2"/>',
        ],
    ];

    $steps = [
        ['Create your account', 'Sign up and complete a short health profile so we can work out your daily targets.'],
        ['Log what you eat', 'Add meals as you go and watch your calories, protein, carbs and fat add up.'],
        ['Meet your dietitian', 'Book a time and join a secure video consultation. Your dietitian already sees your logs.'],
    ];

    $patientPoints = [
        'Daily targets based on your age, body and activity',
        'A weekly chart and a simple logging streak',
        'Easy booking for video consultations',
    ];

    $dietitianPoints = [
        'One list of patients with decision-support flags',
        'Structured ADIME chart notes',
        'Appointments and video sessions in one place',
    ];

    $btnPrimary = 'inline-flex items-center justify-center px-6 py-3 text-base font-medium text-white bg-emerald-700 rounded-lg hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-700 transition';
    $btnOutline = 'inline-flex items-center justify-center px-6 py-3 text-base font-medium text-gray-900 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-700 transition';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth motion-reduce:scroll-auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Log your meals, see your daily nutrition targets, and meet your dietitian by secure video.">
    <meta name="theme-color" content="#047857">
    <title>{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: none; }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        @keyframes ring-fill {
            to { stroke-dashoffset: 72; }
        }
        .fade-up { animation: fade-up .7s ease-out both; }
        .delay-1 { animation-delay: .12s; }
        .delay-2 { animation-delay: .24s; }
        .delay-3 { animation-delay: .36s; }
        .float { animation: float 6s ease-in-out infinite; }
        .float-late { animation: float 7s ease-in-out 1.2s infinite; }
        .ring-anim { stroke-dasharray: 326.73; stroke-dashoffset: 326.73; animation: ring-fill 1.4s .5s ease-out forwards; }

        @media (prefers-reduced-motion: reduce) {
            .fade-up, .float, .float-late { animation: none; }
            .ring-anim { animation: none; stroke-dashoffset: 72; }
        }
    </style>
</head>
<body class="antialiased bg-white text-gray-900"
      style="font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;">

    <a href="#main"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-emerald-800 focus:shadow">
        Skip to main content
    </a>

    <header class="sticky top-0 z-40 border-b border-gray-100 bg-white/85 backdrop-blur">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
                <x-application-logo class="h-9 w-9 text-emerald-700" />
                <span class="text-lg font-semibold text-gray-900">{{ config('app.name') }}</span>
            </a>

            <nav aria-label="Sections" class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-700">
                <a href="#features" class="hover:text-emerald-800 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">Features</a>
                <a href="#how-it-works" class="hover:text-emerald-800 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">How it works</a>
                <a href="#roles" class="hover:text-emerald-800 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">Who it is for</a>
            </nav>

            <nav aria-label="Account" class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="px-4 py-2 text-sm font-medium text-white bg-emerald-700 rounded-lg hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-700">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
                        Log in
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="px-4 py-2 text-sm font-medium text-white bg-emerald-700 rounded-lg hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-700">
                            Register
                        </a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    <main id="main">

        <section class="relative overflow-hidden" style="background-image: linear-gradient(180deg, #ecfdf5 0%, #ffffff 100%);">
            <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-emerald-200/50 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute top-40 -left-32 h-80 w-80 rounded-full bg-teal-200/40 blur-3xl" aria-hidden="true"></div>

            <div class="relative max-w-6xl mx-auto px-6 pt-16 pb-24 lg:pt-24 grid items-center gap-16 lg:grid-cols-2">
                <div>
                    <p class="fade-up inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-3 py-1 text-sm font-medium text-emerald-900">
                        <span class="h-2 w-2 rounded-full bg-emerald-600" aria-hidden="true"></span>
                        Tele-nutrition platform
                    </p>

                    <h1 class="fade-up delay-1 mt-6 text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-gray-900">
                        Nutrition care,
                        <span class="text-emerald-700">wherever you are.</span>
                    </h1>

                    <p class="fade-up delay-2 mt-6 max-w-xl text-lg text-gray-700">
                        Log your meals, see how you compare with your daily targets,
                        and meet your dietitian by secure video.
                    </p>

                    <div class="fade-up delay-3 mt-8 flex flex-wrap gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="{{ $btnPrimary }}">Go to your dashboard</a>
                        @else
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="{{ $btnPrimary }}">Get started</a>
                            @endif
                            <a href="{{ route('login') }}" class="{{ $btnOutline }}">Log in</a>
                        @endauth
                    </div>

                    <ul class="fade-up delay-3 mt-8 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-700">
                        @foreach (['No app to install', 'Secure video visits', 'Works on any device'] as $point)
                            <li class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l4 4 10-10"/></svg>
                                {{ $point }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="relative mx-auto w-full max-w-md pb-12 lg:max-w-none" aria-hidden="true">
                    <div class="float rounded-3xl border border-gray-200 bg-white p-6 shadow-xl">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-700">Energy today</p>
                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">Sample preview</span>
                        </div>

                        <div class="mt-5 flex items-center gap-5">
                            <div class="relative h-28 w-28 shrink-0">
                                <svg viewBox="0 0 120 120" class="h-28 w-28 -rotate-90">
                                    <circle cx="60" cy="60" r="52" fill="none" stroke="#e5e7eb" stroke-width="10" />
                                    <circle class="ring-anim" cx="60" cy="60" r="52" fill="none" stroke="#047857" stroke-width="10" stroke-linecap="round" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center text-lg font-semibold">78%</div>
                            </div>
                            <div>
                                <p class="text-3xl font-semibold">1,560</p>
                                <p class="text-sm text-gray-700">of 2,000 kcal</p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-3">
                            @foreach ([['Protein', 82], ['Carbs', 71], ['Fat', 64]] as [$name, $width])
                                <div>
                                    <div class="flex justify-between text-xs text-gray-700">
                                        <span>{{ $name }}</span>
                                        <span>{{ $width }}%</span>
                                    </div>
                                    <div class="mt-1 h-2 rounded-full bg-gray-100">
                                        <div class="h-2 rounded-full bg-emerald-600" style="width: {{ $width }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="float-late absolute -bottom-2 -left-2 w-64 rounded-2xl border border-gray-200 bg-white p-4 shadow-lg sm:-left-8">
                        <p class="text-xs font-medium text-gray-700">Next consultation</p>
                        <div class="mt-2 flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-800">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="12" height="12" rx="2"/><path d="M15 10l6-3v10l-6-3"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Tomorrow, 10:00 AM</p>
                                <p class="text-xs text-gray-700">Video session with your dietitian</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="scroll-mt-20 max-w-6xl mx-auto px-6 py-20" aria-labelledby="features-title">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Features</p>
                <h2 id="features-title" class="mt-2 text-3xl font-bold tracking-tight">Everything in one place</h2>
                <p class="mt-4 text-gray-700">
                    From the first meal you log to the video visit with your dietitian, it all connects.
                </p>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($features as [$title, $text, $icon])
                    <article class="rounded-2xl border border-gray-200 bg-white p-6 transition hover:-translate-y-1 hover:shadow-md motion-reduce:transition-none motion-reduce:hover:transform-none">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icon !!}</svg>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-gray-900">{{ $title }}</h3>
                        <p class="mt-2 text-gray-700">{{ $text }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section id="how-it-works" class="scroll-mt-20 bg-gray-50 border-y border-gray-100" aria-labelledby="how-title">
            <div class="max-w-6xl mx-auto px-6 py-20">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">How it works</p>
                    <h2 id="how-title" class="mt-2 text-3xl font-bold tracking-tight">Up and running in three steps</h2>
                </div>

                <ol class="mt-12 grid gap-8 md:grid-cols-3">
                    @foreach ($steps as $index => [$title, $text])
                        <li class="relative rounded-2xl bg-white border border-gray-200 p-6">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-700 text-lg font-semibold text-white" aria-hidden="true">
                                {{ $index + 1 }}
                            </span>
                            <h3 class="mt-5 text-lg font-semibold text-gray-900">
                                <span class="sr-only">Step {{ $index + 1 }}: </span>{{ $title }}
                            </h3>
                            <p class="mt-2 text-gray-700">{{ $text }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        <section id="roles" class="scroll-mt-20 max-w-6xl mx-auto px-6 py-20" aria-labelledby="roles-title">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Who it is for</p>
                <h2 id="roles-title" class="mt-2 text-3xl font-bold tracking-tight">Built for both sides of the consultation</h2>
            </div>

            <div class="mt-12 grid gap-6 lg:grid-cols-2">
                <article class="rounded-3xl border border-gray-200 bg-white p-8">
                    <h3 class="text-xl font-semibold text-gray-900">For patients</h3>
                    <ul class="mt-6 space-y-3">
                        @foreach ($patientPoints as $point)
                            <li class="flex items-start gap-3 text-gray-700">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l4 4 10-10"/></svg>
                                {{ $point }}
                            </li>
                        @endforeach
                    </ul>
                </article>

                <article class="rounded-3xl p-8 text-white"
                         style="background-image: linear-gradient(135deg, #065f46 0%, #115e59 100%);">
                    <h3 class="text-xl font-semibold">For dietitians</h3>
                    <ul class="mt-6 space-y-3">
                        @foreach ($dietitianPoints as $point)
                            <li class="flex items-start gap-3 text-emerald-50">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l4 4 10-10"/></svg>
                                {{ $point }}
                            </li>
                        @endforeach
                    </ul>
                </article>
            </div>
        </section>

        <section class="max-w-6xl mx-auto px-6 pb-20" aria-labelledby="cta-title">
            <div class="rounded-3xl px-8 py-14 text-center text-white"
                 style="background-image: linear-gradient(135deg, #047857 0%, #0f766e 100%);">
                <h2 id="cta-title" class="text-3xl font-bold tracking-tight">Ready to start?</h2>
                <p class="mx-auto mt-4 max-w-xl text-emerald-50">
                    Create an account, set up your profile and log your first meal in just a few minutes.
                </p>

                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center px-6 py-3 rounded-lg bg-white text-emerald-900 font-medium hover:bg-emerald-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-700">
                            Go to your dashboard
                        </a>
                    @else
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="inline-flex items-center px-6 py-3 rounded-lg bg-white text-emerald-900 font-medium hover:bg-emerald-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-700">
                                Create your account
                            </a>
                        @endif
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center px-6 py-3 rounded-lg border border-white text-white font-medium hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-700">
                            Log in
                        </a>
                    @endauth
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-gray-100 bg-white">
        <div class="max-w-6xl mx-auto px-6 py-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <x-application-logo class="h-7 w-7 text-emerald-700" />
                <span class="font-semibold text-gray-900">{{ config('app.name') }}</span>
            </div>
            <p class="text-sm text-gray-700 max-w-xl">
                A school project. It does not replace advice from a licensed health professional.
            </p>
        </div>
    </footer>
</body>
</html>