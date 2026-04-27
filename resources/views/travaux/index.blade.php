@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
        {{ session('success') }}
    </div>
    @endif

    {{-- Title + Create button --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
            <h1 class="text-xl font-bold text-[#967A4B]">Gestion des Travaux</h1>
        </div>
        @hasanyrole('manager|secretaire|assistante|cadcam')
        <a href="{{ route('travaux.create') }}" class="shrink-0 rounded-lg border border-[#967A4B] bg-[#967A4B] px-5 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">
            Créer un travail
        </a>
        @endhasanyrole
    </div>

    {{-- Filters --}}
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4">
        <form method="GET" action="{{ route('travaux.index') }}" class="flex flex-wrap items-center gap-4">
            <div class="relative min-w-[200px] flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="recherche" value="{{ request('recherche') }}" placeholder="Rechercher par Dentiste, Patient ou N° fiche..." class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 py-2.5 pl-10 pr-3 text-sm text-zinc-100 placeholder-zinc-500 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            </div>
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <select name="statut" class="auth-input min-w-[140px] rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                    <option value="">Filtrer par Statut</option>
                    @foreach($statutLabels as $value => $label)
                        <option value="{{ $value }}" {{ request('statut') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="auth-input rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="auth-input rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">
                    Appliquer Filtres
                </button>
                @if(request()->hasAny(['recherche', 'statut', 'date_debut', 'date_fin']))
                <a href="{{ route('travaux.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#967A4B]/50 bg-transparent px-4 py-2.5 text-sm font-medium text-[#967A4B] transition hover:bg-[#967A4B]/10">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Réinitialiser
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-[#967A4B]/20 bg-zinc-900/80">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] text-left text-sm">
                <thead>
                    <tr class="border-b border-[#967A4B]/30 bg-zinc-800/80">
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Dentiste</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Patient</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">N° fiche</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Type de travail</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Date d'entrée</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Date de livraison</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Date d'essiage</th>
                        @hasanyrole('manager|secretaire')
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Prix (DHS)</th>
                        @endhasanyrole
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Statut</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B] w-40">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($travaux as $t)
                    <tr role="button" tabindex="0" onclick="window.location='{{ route('travaux.show', $t) }}'" onkeydown="if(event.key==='Enter') window.location='{{ route('travaux.show', $t) }}'" class="cursor-pointer border-b border-zinc-800 transition hover:bg-zinc-800/50">
                        <td class="px-4 py-3 text-zinc-200">{{ $t->doc?->name ?? $t->dentiste }}</td>
                        <td class="px-4 py-3 text-zinc-200">{{ $t->patient }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $t->numero_fiche ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-200">{{ $t->type_travail_display }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $t->date_entree->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $t->date_livraison->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $t->date_essiage ? $t->date_essiage->format('d/m/Y') : '—' }}</td>
                        @hasanyrole('manager|secretaire')
                        <td class="px-4 py-3 text-zinc-200">{{ number_format($t->prix_actuel, 0, ',', ' ') }} DHS</td>
                        @endhasanyrole
                        <td class="px-4 py-3">
                            @if($t->statut === 'termine')
                                <span class="inline-flex items-center rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-medium text-emerald-300 ring-1 ring-emerald-500/30">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                            @elseif($t->statut === 'livrer')
                                <span class="inline-flex items-center rounded-full bg-teal-500/15 px-3 py-1 text-xs font-medium text-teal-300 ring-1 ring-teal-500/30">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                            @elseif($t->statut === 'annule')
                                <span class="inline-flex items-center rounded-full bg-red-500/15 px-3 py-1 text-xs font-medium text-red-300 ring-1 ring-red-500/30">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                            @elseif($t->statut === 'a_refaire')
                                <span class="inline-flex items-center rounded-full bg-violet-500/15 px-3 py-1 text-xs font-medium text-violet-300 ring-1 ring-violet-500/30">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                            @elseif($t->statut === 'en_attente')
                                <span class="inline-flex items-center rounded-full bg-amber-500/15 px-3 py-1 text-xs font-medium text-amber-300 ring-1 ring-amber-500/30">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                            @elseif($t->statut === 'en_cours')
                                <span class="inline-flex items-center rounded-full bg-blue-500/15 px-3 py-1 text-xs font-medium text-blue-300 ring-1 ring-blue-500/30">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-zinc-500/15 px-3 py-1 text-xs font-medium text-zinc-300 ring-1 ring-zinc-500/30">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3" onclick="event.stopPropagation()">
                            @hasanyrole('manager|secretaire|assistante|cadcam')
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('travaux.updateStatut', $t) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <select name="statut" onchange="this.form.submit()" class="rounded border border-zinc-600 bg-zinc-800 px-2 py-1.5 text-xs text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                                        @foreach($statutLabels as $value => $label)
                                            <option value="{{ $value }}" {{ $t->statut === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                <a href="{{ route('travaux.edit', $t) }}" class="rounded p-1.5 text-[#967A4B] hover:bg-[#967A4B]/10" title="Modifier">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @hasanyrole('manager|secretaire')
                                <form method="POST" action="{{ route('travaux.destroy', $t) }}" class="inline" onsubmit="return confirm('Supprimer ce travail ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded p-1.5 text-red-400 transition hover:bg-red-500/10" title="Supprimer">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endhasanyrole
                            </div>
                            @endhasanyrole
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->hasAnyRole(['manager', 'secretaire']) ? 10 : 9 }}" class="px-4 py-12 text-center text-zinc-500">Aucun travail trouvé.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($travaux->hasPages())
        <div class="flex items-center justify-center gap-2 border-t border-zinc-800 px-4 py-3">
            <span class="text-sm text-zinc-400">Page {{ $travaux->currentPage() }} sur {{ $travaux->lastPage() }}</span>
            <div class="flex gap-1">
                @if($travaux->onFirstPage())
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-500 cursor-not-allowed">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                @else
                    <a href="{{ $travaux->previousPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B] hover:bg-[#967A4B]/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                @endif
                @if($travaux->hasMorePages())
                    <a href="{{ $travaux->nextPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B] hover:bg-[#967A4B]/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-500 cursor-not-allowed">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
