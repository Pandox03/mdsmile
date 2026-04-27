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
            <h1 class="text-xl font-bold text-[#967A4B]">Factures</h1>
            <p class="mt-1 text-sm text-zinc-500">Liste des factures par client</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('factures.create') }}" class="shrink-0 rounded-lg border border-[#967A4B] bg-[#967A4B] px-5 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">
                Nouvelle facture
            </a>
        </div>
    </div>

    {{-- 1. Global filter bar --}}
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4">
        <form method="GET" action="{{ route('factures.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[200px]">
                <label class="mb-1 block text-xs font-medium text-zinc-500">Client (doc)</label>
                <select name="doc_id" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                    <option value="">— Tous —</option>
                    @foreach($docs as $d)
                    <option value="{{ $d->id }}" {{ request('doc_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="mb-1 block text-xs font-medium text-zinc-500">Date du</label>
                <input type="date" name="date_du" value="{{ request('date_du') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 py-2.5 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            </div>
            <div class="min-w-[140px]">
                <label class="mb-1 block text-xs font-medium text-zinc-500">Date au</label>
                <input type="date" name="date_au" value="{{ request('date_au') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 py-2.5 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            </div>
            <div class="min-w-[200px]">
                <label class="mb-1 block text-xs font-medium text-zinc-500">Recherche client (texte)</label>
                <input type="text" name="recherche_client" value="{{ request('recherche_client') }}" placeholder="Nom client..." class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 py-2.5 text-sm text-zinc-100 placeholder-zinc-500 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            </div>
            <div class="min-w-[120px]">
                <label class="mb-1 block text-xs font-medium text-zinc-500">&nbsp;</label>
                <button type="submit" class="w-full rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">Filtrer</button>
            </div>
        </form>
    </div>

    {{-- Regrouper des factures (when doc + date range are set) --}}
    @if(request()->filled('doc_id') && request()->filled('date_du') && request()->filled('date_au'))
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-zinc-100">Regrouper des factures</h2>
                <p class="mt-0.5 text-xs text-zinc-500">Factures dont au moins un travail a une date d'entrée dans la période. Cochez celles à regrouper en une seule.</p>
            </div>
            @if($facturesRegroupement->isNotEmpty())
            <div class="flex items-center gap-2">
                <button type="button" id="btn-regrouper-tout" class="rounded-lg border border-zinc-600 px-3 py-2 text-xs font-medium text-zinc-400 hover:bg-zinc-800">Tout cocher</button>
                <button type="button" id="btn-regrouper-creer" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2 text-sm font-medium text-black transition hover:bg-[#B8986B] disabled:cursor-not-allowed disabled:opacity-50" disabled>Créer facture regroupée</button>
            </div>
            @endif
        </div>
        @if($facturesRegroupement->isEmpty())
        <div class="py-6 text-center">
            <p class="text-sm text-zinc-500">Aucune facture avec un travail (date d'entrée) dans cette période pour ce client.</p>
            <p class="mt-2 text-xs text-zinc-600">Seules les factures dont au moins un travail a une <strong>date d'entrée</strong> entre les deux dates sont listées.</p>
        </div>
        @else
        <form id="form-regrouper" method="POST" action="{{ route('factures.regrouper') }}">
            @csrf
            <input type="hidden" name="date_facture" id="regrouper-date-facture" value="{{ date('Y-m-d') }}">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-[#967A4B]/30 bg-zinc-800/80">
                            <th class="w-10 px-3 py-2">
                                <input type="checkbox" id="regrouper-check-all" class="rounded border-zinc-600 bg-zinc-800 text-[#967A4B] focus:ring-[#967A4B]/30" title="Tout cocher">
                            </th>
                            <th class="px-3 py-2 font-semibold text-[#967A4B]">Numéro</th>
                            <th class="px-3 py-2 font-semibold text-[#967A4B]">Date</th>
                            <th class="px-3 py-2 font-semibold text-[#967A4B]">Client</th>
                            <th class="px-3 py-2 font-semibold text-[#967A4B] text-right">Reste</th>
                            <th class="px-3 py-2 font-semibold text-[#967A4B] w-24">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($facturesRegroupement as $f)
                        <tr class="border-b border-zinc-800/80 hover:bg-zinc-800/30">
                            <td class="px-3 py-2">
                                <input type="checkbox" name="facture_ids[]" value="{{ $f->id }}" class="regrouper-cb rounded border-zinc-600 bg-zinc-800 text-[#967A4B] focus:ring-[#967A4B]/30">
                            </td>
                            <td class="px-3 py-2 font-medium text-zinc-200">{{ $f->numero }}</td>
                            <td class="px-3 py-2 text-zinc-400">{{ $f->date_facture->format('d/m/Y') }}</td>
                            <td class="px-3 py-2 text-zinc-400">{{ $f->doc->name ?? '—' }}</td>
                            <td class="px-3 py-2 text-right font-medium text-zinc-200">{{ number_format($f->reste_a_facturer ?? 0, 0, ',', ' ') }} DHS</td>
                            <td class="px-3 py-2">
                                <a href="{{ route('factures.show', $f) }}" class="text-[#967A4B] hover:underline">Voir</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
        @endif
    </div>

    {{-- Modal: date for regrouped facture --}}
    <div id="modal-regrouper-date" class="fixed inset-0 z-[55] hidden flex items-center justify-center bg-black/60 p-4" aria-modal="true" role="dialog">
        <div class="w-full max-w-sm rounded-xl border border-[#967A4B]/30 bg-zinc-900 shadow-xl">
            <div class="border-b border-zinc-700 px-6 py-4">
                <h2 class="text-lg font-bold text-[#967A4B]">Date de la facture regroupée</h2>
            </div>
            <div class="p-6">
                <label class="mb-1 block text-sm font-medium text-zinc-400">Date facture</label>
                <input type="date" id="regrouper-date-input" value="{{ date('Y-m-d') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            </div>
            <div class="flex justify-end gap-2 border-t border-zinc-700 px-6 py-4">
                <button type="button" id="modal-regrouper-cancel" class="rounded-lg border border-zinc-600 px-4 py-2 text-sm text-zinc-400 hover:bg-zinc-800">Annuler</button>
                <button type="button" id="modal-regrouper-confirm" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2 text-sm font-medium text-black hover:bg-[#B8986B]">Créer la facture</button>
            </div>
        </div>
    </div>
    @endif

    {{-- 2. Factures table --}}
    <div class="overflow-hidden rounded-xl border border-[#967A4B]/20 bg-zinc-900/80">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-sm">
                <thead>
                    <tr class="border-b border-[#967A4B]/30 bg-zinc-800/80">
                        <th class="px-3 py-3 font-semibold text-[#967A4B]">Numéro</th>
                        <th class="px-3 py-3 font-semibold text-[#967A4B]">Date</th>
                        <th class="px-3 py-3 font-semibold text-[#967A4B]">Client</th>
                        <th class="px-3 py-3 font-semibold text-[#967A4B] text-right">Montant (DHS)</th>
                        <th class="px-3 py-3 font-semibold text-[#967A4B] w-24">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($factures as $f)
                    <tr class="border-b border-zinc-800 bg-zinc-800/30">
                        <td class="px-3 py-2.5 font-medium text-zinc-200">{{ $f->numero }}</td>
                        <td class="px-3 py-2.5 text-zinc-300">{{ $f->date_facture->format('d/m/Y') }}</td>
                        <td class="px-3 py-2.5 text-zinc-300">{{ $f->doc->name ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-right text-zinc-200">{{ number_format($f->total_facture, 0, ',', ' ') }}</td>
                        <td class="px-3 py-2.5">
                            <div class="flex items-center gap-1.5">
                                {{-- Voir --}}
                                <a href="{{ route('factures.show', $f) }}" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-700 hover:text-[#967A4B]" title="Voir">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                {{-- PDF --}}
                                <a href="{{ route('factures.pdf', $f) }}" target="_blank" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-700 hover:text-[#967A4B]" title="PDF">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a2 2 0 00-.586-1.414l-4.414-4.414A2 2 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 3v4a1 1 0 001 1h4" />
                                    </svg>
                                </a>
                                {{-- Éditer --}}
                                <a href="{{ route('factures.edit', $f) }}" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-700 hover:text-[#967A4B]" title="Modifier">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 113 3L13 14l-4 1 1-4 8.5-8.5z" />
                                    </svg>
                                </a>
                                {{-- Supprimer --}}
                                <form method="POST" action="{{ route('factures.destroy', $f) }}" onsubmit="return confirm('Supprimer cette facture ?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded p-1.5 text-red-400 hover:bg-red-500/10" title="Supprimer">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-1-3H10a1 1 0 00-1 1v2h6V5a1 1 0 00-1-1z" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-zinc-500">Aucune facture trouvée.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
