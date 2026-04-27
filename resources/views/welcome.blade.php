<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Laboratoire Dentaire</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
            @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    </head>
<body class="min-h-screen bg-black text-zinc-100 antialiased">
    <div class="flex min-h-screen flex-col">
        {{-- Header with logo and actions --}}
        <header class="border-b border-[#967A4B]/30 bg-black/95 backdrop-blur">
            <div class="mx-auto flex h-20 max-w-6xl items-center justify-between px-6">
                <a href="/" class="flex items-center gap-3">
                    <img src="{{ asset('images/mdsmile-logo.png') }}" alt="MdSmile" class="h-14 w-auto object-contain" />
                </a>
                <nav class="flex items-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-lg border border-[#967A4B]/50 px-5 py-2.5 text-sm font-medium text-[#967A4B] transition hover:bg-[#967A4B]/10 hover:border-[#967A4B]">
                            Tableau de bord
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg bg-[#967A4B] px-5 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">
                            Connexion
                        </a>
                    @endauth
                </nav>
            </div>
        </header>

        {{-- Hero & lab info --}}
        <main class="flex flex-1 flex-col items-center justify-center px-6 py-16">
            <div class="mx-auto max-w-3xl text-center">
                {{-- Logo (mobile/centered) --}}
                <div class="mb-10 flex justify-center lg:hidden">
                    <img src="{{ asset('images/mdsmile-logo.png') }}" alt="MdSmile" class="h-24 w-auto object-contain" />
                </div>

                <h1 class="mb-4 text-3xl font-bold text-[#967A4B] sm:text-4xl">
                    Bienvenue chez {{ config('app.name') }}
                </h1>
                <p class="mb-2 text-lg text-zinc-400">
                    Laboratoire dentaire — Gestion prothésiste
                </p>
                <p class="mb-12 text-zinc-500">
                    Création de prothèses dentaires, suivi des travaux, facturation et gestion du stock
                </p>

                {{-- Quick infos --}}
                <div class="mb-12 grid gap-6 sm:grid-cols-3">
                    <div class="rounded-xl border border-[#967A4B]/30 bg-zinc-900/80 p-6">
                        <div class="mb-2 text-2xl font-semibold text-[#967A4B]">Prothèses</div>
                        <p class="text-sm text-zinc-500">Couronnes, bridges, implants et plus</p>
                    </div>
                    <div class="rounded-xl border border-[#967A4B]/30 bg-zinc-900/80 p-6">
                        <div class="mb-2 text-2xl font-semibold text-[#967A4B]">Suivi travaux</div>
                        <p class="text-sm text-zinc-500">Du modelage à la livraison</p>
                    </div>
                    <div class="rounded-xl border border-[#967A4B]/30 bg-zinc-900/80 p-6">
                        <div class="mb-2 text-2xl font-semibold text-[#967A4B]">CAD/CAM</div>
                        <p class="text-sm text-zinc-500">Technologie et précision</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ route('login') }}" class="w-full rounded-xl bg-[#967A4B] px-8 py-4 text-center font-semibold text-black transition hover:bg-[#B8986B] sm:w-auto">
                        Connexion
                    </a>
        </div>

                @guest
                    <p class="mt-6 text-sm text-zinc-500">
                        Connectez-vous pour accéder au tableau de bord
                    </p>
                @endguest
            </div>
        </main>

        {{-- Footer --}}
        <footer class="border-t border-[#967A4B]/20 py-6 text-center text-sm text-zinc-600">
            &copy; {{ date('Y') }} {{ config('app.name') }} — Laboratoire Dentaire
        </footer>
    </div>
    @livewireScripts
    </body>
</html>
