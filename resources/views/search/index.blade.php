@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
        <h1 class="text-xl font-bold text-[#967A4B]">Recherche</h1>
        <p class="mt-1 text-sm text-zinc-400">Recherchez parmi les clients, travaux, factures et stock</p>
    </div>

    {{-- Search form (repeat on results page so user can refine) --}}
    <form action="{{ route('search.index') }}" method="GET" class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 pointer-events-none">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="search" name="q" value="{{ $q }}" placeholder="Nom client, patient, type travail, n° facture, référence stock…" class="w-full rounded-lg border border-zinc-600 bg-zinc-800/90 py-2.5 pl-10 pr-3 text-sm text-zinc-100 placeholder-zinc-500 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" autofocus>
            </div>
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">Rechercher</button>
        </div>
    </form>

    @if($q === '')
        <p class="rounded-xl border border-zinc-700/80 bg-zinc-800/50 px-4 py-6 text-center text-zinc-500">Saisissez un terme pour lancer la recherche.</p>
    @else
        @php $hasResults = $clients->isNotEmpty() || $travaux->isNotEmpty() || $factures->isNotEmpty() || $stock->isNotEmpty(); @endphp
        @if(!$hasResults)
            <p class="rounded-xl border border-zinc-700/80 bg-zinc-800/50 px-4 py-6 text-center text-zinc-500">Aucun résultat pour « {{ e($q) }} ».</p>
        @else
            <div class="space-y-6">
                @if($clients->isNotEmpty())
                <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 overflow-hidden">
                    <h2 class="border-b border-[#967A4B]/30 bg-zinc-800/80 px-4 py-3 text-lg font-semibold text-[#967A4B]">Clients ({{ $clients->count() }})</h2>
                    <ul class="divide-y divide-zinc-800">
                        @foreach($clients as $doc)
                        <li class="hover:bg-zinc-800/50">
                            <a href="{{ route('clients.show', $doc) }}" class="flex items-center justify-between px-4 py-3">
                                <span class="font-medium text-zinc-200">{{ $doc->name }}</span>
                                <span class="text-xs text-zinc-500">{{ $doc->numero_registration ?: '—' }}</span>
                                <svg class="h-4 w-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    @if($clients->count() >= 10)
                    <p class="border-t border-zinc-800 px-4 py-2 text-xs text-zinc-500">Résultats limités à 10. Affinez la recherche pour plus de précision.</p>
                    @endif
                </div>
                @endif

                @if($travaux->isNotEmpty())
                <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 overflow-hidden">
                    <h2 class="border-b border-[#967A4B]/30 bg-zinc-800/80 px-4 py-3 text-lg font-semibold text-[#967A4B]">Travaux ({{ $travaux->count() }})</h2>
                    <ul class="divide-y divide-zinc-800">
                        @foreach($travaux as $t)
                        <li class="hover:bg-zinc-800/50">
                            <a href="{{ route('travaux.show', $t) }}" class="flex items-center justify-between px-4 py-3">
                                <div>
                                    <span class="font-medium text-zinc-200">{{ $t->reference }}</span>
                                    <span class="ml-2 text-zinc-400">— {{ $t->patient ?: '—' }} · {{ $t->type_travail ?: '—' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full border border-zinc-600 bg-zinc-800 px-2 py-0.5 text-xs text-zinc-400">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                                    <span class="text-xs text-zinc-500">{{ $t->doc->name ?? '—' }}</span>
                                    <svg class="h-4 w-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    @if($travaux->count() >= 10)
                    <p class="border-t border-zinc-800 px-4 py-2 text-xs text-zinc-500">Résultats limités à 10.</p>
                    @endif
                </div>
                @endif

                @if($factures->isNotEmpty())
                <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 overflow-hidden">
                    <h2 class="border-b border-[#967A4B]/30 bg-zinc-800/80 px-4 py-3 text-lg font-semibold text-[#967A4B]">Factures ({{ $factures->count() }})</h2>
                    <ul class="divide-y divide-zinc-800">
                        @foreach($factures as $f)
                        <li class="hover:bg-zinc-800/50">
                            <a href="{{ route('factures.show', $f) }}" class="flex items-center justify-between px-4 py-3">
                                <span class="font-medium text-zinc-200">{{ $f->numero }}</span>
                                <span class="text-sm text-zinc-400">{{ $f->date_facture->format('d/m/Y') }} · {{ $f->doc->name ?? '—' }}</span>
                                <svg class="h-4 w-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    @if($factures->count() >= 10)
                    <p class="border-t border-zinc-800 px-4 py-2 text-xs text-zinc-500">Résultats limités à 10.</p>
                    @endif
                </div>
                @endif

                @if($stock->isNotEmpty())
                <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 overflow-hidden">
                    <h2 class="border-b border-[#967A4B]/30 bg-zinc-800/80 px-4 py-3 text-lg font-semibold text-[#967A4B]">Stock ({{ $stock->count() }})</h2>
                    <ul class="divide-y divide-zinc-800">
                        @foreach($stock as $s)
                        <li class="hover:bg-zinc-800/50">
                            <a href="{{ route('stock.index') }}?recherche={{ urlencode($q) }}" class="flex items-center justify-between px-4 py-3">
                                <span class="font-medium text-zinc-200">{{ $s->name }}</span>
                                <span class="text-sm text-zinc-400">{{ $s->reference ?: '—' }} · {{ number_format($s->quantity, 0, ',', ' ') }} {{ $s->unite }}</span>
                                <svg class="h-4 w-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    @if($stock->count() >= 10)
                    <p class="border-t border-zinc-800 px-4 py-2 text-xs text-zinc-500">Résultats limités à 10.</p>
                    @endif
                </div>
                @endif
            </div>
        @endif
    @endif
</div>
@endsection
