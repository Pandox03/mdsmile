<x-layouts.app :title="'403 — Accès refusé'">
    <div class="flex min-h-screen flex-col items-center justify-center px-6 py-12">
        <div class="w-full max-w-md rounded-2xl border border-[#967A4B]/30 bg-zinc-900/90 p-8 text-center shadow-xl">
            <div class="mb-6 flex justify-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-amber-500/50 bg-amber-500/10">
                    <svg class="h-10 w-10 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>
            <p class="text-6xl font-bold tracking-tight text-[#967A4B]">403</p>
            <h1 class="mt-2 text-xl font-semibold text-zinc-100">Accès refusé</h1>
            <p class="mt-3 text-sm text-zinc-400">
                Vous n'avez pas les droits nécessaires pour accéder à cette page.
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-lg border border-[#967A4B] bg-[#967A4B] px-5 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">
                        Retour au tableau de bord
                    </a>
                @else
                    <a href="{{ url('/') }}" class="inline-flex items-center justify-center rounded-lg border border-[#967A4B] bg-[#967A4B] px-5 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">
                        Retour à l'accueil
                    </a>
                @endauth
                <a href="javascript:history.back()" class="inline-flex items-center justify-center rounded-lg border border-zinc-600 bg-zinc-800 px-5 py-2.5 text-sm font-medium text-zinc-200 transition hover:bg-zinc-700">
                    Page précédente
                </a>
            </div>
        </div>
        <a href="{{ url('/') }}" class="mt-8 flex items-center gap-2 text-sm text-zinc-500 hover:text-[#967A4B]">
            <img src="{{ asset('images/mdsmile-logo.png') }}" alt="MdSmile" class="h-8 w-auto opacity-80" />
            <span>MdSmile — Gestion Laboratoire Dentaire</span>
        </a>
    </div>
</x-layouts.app>
