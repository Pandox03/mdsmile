@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
        {{ session('success') }}
    </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
            <h1 class="text-xl font-bold text-[#967A4B]">Tarifs pour {{ $doc->name }}</h1>
            <p class="mt-1 text-sm text-zinc-400">Modifiez les prix pour ce client puis enregistrez.</p>
        </div>
        <a href="{{ route('clients.index') }}" class="shrink-0 rounded-lg border border-[#967A4B]/50 bg-zinc-800 px-5 py-2.5 text-sm font-medium text-[#967A4B] hover:bg-[#967A4B]/10">
            Retour aux clients
        </a>
    </div>

    <form method="POST" action="{{ route('clients.prestations.update', $doc) }}">
        @csrf
        @method('PUT')

        @forelse($categories as $category)
        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 overflow-hidden mb-6">
            <div class="border-b border-[#967A4B]/30 bg-zinc-800/80 px-4 py-3">
                <h2 class="text-lg font-bold text-zinc-100 uppercase">{{ $category->name }}</h2>
            </div>
            <div class="divide-y divide-zinc-800">
                @forelse($category->prestations as $prestation)
                @php
                    $effectivePrice = $doc->getPriceForPrestation($prestation);
                @endphp
                <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-3 hover:bg-zinc-800/40">
                    <span class="text-zinc-200">{{ $prestation->name }}</span>
                    <div class="flex items-center gap-2">
                        <input type="number" step="0.01" min="0" name="prices[{{ $prestation->id }}]" value="{{ $effectivePrice !== null ? $effectivePrice : '' }}" placeholder="Sur devis" class="w-28 rounded-lg border border-zinc-600 bg-zinc-800 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-500 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                        <span class="text-zinc-500 text-sm w-6">DH</span>
                    </div>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-sm text-zinc-500">Aucune prestation dans cette catégorie.</div>
                @endforelse
            </div>
        </div>
        @empty
        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-12 text-center text-zinc-400">
            Aucune catégorie de prestations. Configurez les prestations dans Paramètres / Prestations.
        </div>
        @endforelse

        @if($categories->isNotEmpty())
        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4">
            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-zinc-700/80 px-5 py-3.5 text-sm font-medium text-zinc-100 transition hover:bg-zinc-600/80 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                <svg class="h-5 w-5 shrink-0 text-[#967A4B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Enregistrer la grille tarifaire
            </button>
        </div>
        @endif
    </form>
</div>
@endsection
