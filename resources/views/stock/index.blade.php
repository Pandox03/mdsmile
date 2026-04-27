@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    @if(session('success'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
        {{ session('error') }}
    </div>
    @endif

    {{-- Title --}}
    <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
        <h1 class="text-xl font-bold text-[#967A4B]">Stock</h1>
        <p class="mt-1 text-sm text-zinc-400">Matériaux et fournisseurs</p>
    </div>

    {{-- Materials section --}}
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4">
        <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-semibold text-[#967A4B]">Matériaux</h2>
            @hasanyrole('manager|secretaire')
            <a href="{{ route('stock.materials.create') }}" class="shrink-0 rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">
                Ajouter un matériau
            </a>
            @endhasanyrole
        </div>
        <form method="GET" action="{{ route('stock.index') }}" class="mb-4 flex flex-wrap items-center gap-4">
            <input type="hidden" name="fournisseurs_page" value="{{ request('fournisseurs_page') }}">
            <input type="text" name="recherche" value="{{ request('recherche') }}" placeholder="Nom ou référence..." class="auth-input min-w-[180px] rounded-lg border border-zinc-600 bg-zinc-800/90 py-2.5 px-3 text-sm text-zinc-100 placeholder-zinc-500 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">Filtrer</button>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-left text-sm">
                <thead>
                    <tr class="border-b border-[#967A4B]/30 bg-zinc-800/80">
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Nom</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Référence</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B] text-right">Quantité</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B] text-right">Seuil alerte</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Unité</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Fournisseur</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B] w-28">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $m)
                    @php $isFaible = $m->quantity < ($m->seuil_alerte_min ?? 5); @endphp
                    <tr class="border-b border-zinc-800 hover:bg-zinc-800/50 {{ $isFaible ? 'border-l-4 border-l-amber-500 bg-amber-950/25' : '' }}">
                        <td class="px-4 py-3 font-medium text-zinc-200">{{ $m->name }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $m->reference ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-zinc-300">{{ number_format($m->quantity, 0, ',', ' ') }}</span>
                            @if($isFaible)
                                <span class="ml-2 inline-flex items-center rounded-full border border-amber-500/50 bg-amber-500/20 px-2 py-0.5 text-xs font-medium text-amber-400">Stock faible</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-zinc-300">{{ number_format((float) ($m->seuil_alerte_min ?? 5), 0, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $m->unite }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $m->fournisseur?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('stock.materials.edit', $m) }}" class="rounded p-1.5 text-[#967A4B] hover:bg-[#967A4B]/10" title="Modifier">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @hasanyrole('manager|secretaire')
                                <form method="POST" action="{{ route('stock.materials.destroy', $m) }}" class="inline" onsubmit="return confirm('Supprimer ce matériau ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded p-1.5 text-red-400 hover:bg-red-500/10" title="Supprimer">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endhasanyrole
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-zinc-500">Aucun matériau.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($materials->hasPages())
        <div class="mt-3 flex items-center justify-center gap-2 border-t border-zinc-800 pt-3">
            <span class="text-sm text-zinc-400">Page {{ $materials->currentPage() }} / {{ $materials->lastPage() }}</span>
            <div class="flex gap-1">
                @if($materials->onFirstPage())
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-500 cursor-not-allowed"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></span>
                @else
                    <a href="{{ $materials->previousPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B] hover:bg-[#967A4B]/20"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
                @endif
                @if($materials->hasMorePages())
                    <a href="{{ $materials->nextPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B] hover:bg-[#967A4B]/20"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                @else
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-500 cursor-not-allowed"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Fournisseurs section (manager + secrétaire only; technicien cannot add or manage fournisseurs) --}}
    @hasanyrole('manager|secretaire')
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4">
        <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-semibold text-[#967A4B]">Fournisseurs</h2>
            <a href="{{ route('stock.fournisseurs.create') }}" class="shrink-0 rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">
                Ajouter un fournisseur
            </a>
        </div>
        <form method="GET" action="{{ route('stock.index') }}" class="mb-4 flex flex-wrap items-center gap-4">
            <input type="hidden" name="materials_page" value="{{ request('materials_page') }}">
            <input type="hidden" name="recherche" value="{{ request('recherche') }}">
            <input type="text" name="fournisseur_recherche" value="{{ request('fournisseur_recherche') }}" placeholder="Nom, téléphone, email..." class="auth-input min-w-[180px] rounded-lg border border-zinc-600 bg-zinc-800/90 py-2.5 px-3 text-sm text-zinc-100 placeholder-zinc-500 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">Filtrer</button>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-left text-sm">
                <thead>
                    <tr class="border-b border-[#967A4B]/30 bg-zinc-800/80">
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Nom</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Téléphone</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Email</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Matériaux</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B] w-28">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fournisseurs as $f)
                    <tr class="border-b border-zinc-800 hover:bg-zinc-800/50">
                        <td class="px-4 py-3 font-medium text-zinc-200">{{ $f->name }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $f->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $f->email ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $f->stock_items_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('stock.fournisseurs.edit', $f) }}" class="rounded p-1.5 text-[#967A4B] hover:bg-[#967A4B]/10" title="Modifier">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('stock.fournisseurs.destroy', $f) }}" class="inline" onsubmit="return confirm('Supprimer ce fournisseur ? Les matériaux ne seront pas supprimés.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded p-1.5 text-red-400 hover:bg-red-500/10" title="Supprimer">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-zinc-500">Aucun fournisseur.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($fournisseurs->hasPages())
        <div class="mt-3 flex items-center justify-center gap-2 border-t border-zinc-800 pt-3">
            <span class="text-sm text-zinc-400">Page {{ $fournisseurs->currentPage() }} / {{ $fournisseurs->lastPage() }}</span>
            <div class="flex gap-1">
                @if($fournisseurs->onFirstPage())
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-500 cursor-not-allowed"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></span>
                @else
                    <a href="{{ $fournisseurs->previousPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B] hover:bg-[#967A4B]/20"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
                @endif
                @if($fournisseurs->hasMorePages())
                    <a href="{{ $fournisseurs->nextPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B] hover:bg-[#967A4B]/20"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                @else
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-500 cursor-not-allowed"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
                @endif
            </div>
        </div>
        @endif
    </div>
    @endhasanyrole
</div>
@endsection
