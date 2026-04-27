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
            <h1 class="text-xl font-bold text-[#967A4B]">Prestations</h1>
            <p class="mt-1 text-sm text-zinc-400">Prix des travaux par catégorie</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('prestations.categories.create') }}" class="rounded-lg border border-[#967A4B]/50 bg-zinc-800 px-4 py-2.5 text-sm font-medium text-[#967A4B] hover:bg-[#967A4B]/10">
                Nouvelle catégorie
            </a>
            <a href="{{ route('prestations.create') }}" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">
                Nouvelle prestation
            </a>
        </div>
    </div>

    @forelse($categories as $category)
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#967A4B]/30 bg-zinc-800/80 px-4 py-3">
            <div>
                <h2 class="text-lg font-bold text-zinc-100">{{ $category->name }}</h2>
                <p class="text-sm text-zinc-500">{{ $category->prestations_count }} prestation(s)</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('prestations.create', ['category' => $category->id]) }}" class="rounded p-2 text-[#967A4B] hover:bg-[#967A4B]/10" title="Ajouter une prestation">+ Prestation</a>
                <a href="{{ route('prestations.categories.edit', $category) }}" class="rounded p-2 text-zinc-400 hover:bg-zinc-700 hover:text-zinc-100" title="Modifier la catégorie">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
                <form method="POST" action="{{ route('prestations.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Supprimer cette catégorie et toutes ses prestations ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded p-2 text-red-400 hover:bg-red-500/10" title="Supprimer la catégorie">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>
        <div class="divide-y divide-zinc-800">
            @forelse($category->prestations as $prestation)
            <div class="flex items-center justify-between gap-4 px-4 py-3 hover:bg-zinc-800/40">
                <span class="text-zinc-200">{{ $prestation->name }}</span>
                <div class="flex items-center gap-2">
                    <span class="font-medium text-[#967A4B]">{{ $prestation->price_display }}</span>
                    <a href="{{ route('prestations.edit', $prestation) }}" class="rounded p-1.5 text-zinc-500 hover:bg-zinc-700 hover:text-[#967A4B]" title="Modifier">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form method="POST" action="{{ route('prestations.destroy', $prestation) }}" class="inline" onsubmit="return confirm('Supprimer cette prestation ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded p-1.5 text-red-400 hover:bg-red-500/10" title="Supprimer">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-sm text-zinc-500">Aucune prestation dans cette catégorie. <a href="{{ route('prestations.create', ['category' => $category->id]) }}" class="text-[#967A4B] hover:underline">Ajouter</a></div>
            @endforelse
        </div>
    </div>
    @empty
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-12 text-center">
        <p class="text-zinc-400">Aucune catégorie. Créez une catégorie puis ajoutez des prestations.</p>
        <a href="{{ route('prestations.categories.create') }}" class="mt-4 inline-block rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">Nouvelle catégorie</a>
    </div>
    @endforelse
</div>
@endsection
