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

    <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
        <h1 class="text-xl font-bold text-[#967A4B]">Situation mensuelle par dentiste (doc)</h1>
        <p class="mt-1 text-sm text-zinc-400">Filtrer par plage de dates. Le report intègre les travaux passés et les encaissements enregistrés sur la période. Les encaissements ne peuvent être ajoutés ou retirés que pour le <strong class="text-zinc-300">mois calendaire en cours</strong>.</p>
        <p class="mt-2 text-sm text-zinc-500">Les travaux <span class="text-zinc-300">Annulé</span> et <span class="text-zinc-300">À refaire</span> sont comptés à <strong class="text-zinc-300">0 DHS</strong>.</p>
    </div>

    <form method="GET" action="{{ route('doc.situations.index') }}" class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-zinc-500">Dentiste (doc)</label>
                <select name="doc_id" class="auth-input min-w-[220px] rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                    <option value="">— Choisir un dentiste —</option>
                    @foreach($docs as $d)
                        <option value="{{ $d->id }}" {{ ($docId ?? '') == $d->id ? 'selected' : '' }}>{{ $d->name }}{{ $d->numero_registration ? ' (N° ' . $d->numero_registration . ')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-zinc-500">Du</label>
                <input type="date" name="date_from" value="{{ $dateFrom ?? now()->startOfMonth()->toDateString() }}" class="auth-input rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-zinc-500">Au</label>
                <input type="date" name="date_to" value="{{ $dateTo ?? now()->endOfMonth()->toDateString() }}" class="auth-input rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            </div>
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">Afficher</button>
        </div>
    </form>

    @if($doc)
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 overflow-hidden">
        <div class="border-b border-[#967A4B]/30 bg-zinc-800/80 px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-[#967A4B]">Dr {{ $doc->name }}</h2>
            <a href="{{ route('doc.situations.pdf', ['doc_id' => $doc->id, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" target="_blank" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">
                Télécharger PDF
            </a>
        </div>
        <div class="grid gap-3 border-b border-zinc-800 bg-zinc-900/70 p-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-zinc-700 bg-zinc-900/80 p-3">
                <p class="text-xs text-zinc-500">Report mois précédents</p>
                <p class="mt-1 text-lg font-semibold text-zinc-200">{{ number_format($carryover, 0, ',', ' ') }} DHS</p>
            </div>
            <div class="rounded-lg border border-zinc-700 bg-zinc-900/80 p-3">
                <p class="text-xs text-zinc-500">Travaux de la période</p>
                <p class="mt-1 text-lg font-semibold text-zinc-200">{{ number_format($travauxPeriodTotal, 0, ',', ' ') }} DHS</p>
            </div>
            <div class="rounded-lg border border-zinc-700 bg-zinc-900/80 p-3">
                <p class="text-xs text-zinc-500">Total encaissé (situation, période)</p>
                <p class="mt-1 text-lg font-semibold text-zinc-200">{{ number_format($montantRecuPeriode, 0, ',', ' ') }} DHS</p>
                @if($situationEncaissementEditable ?? false)
                <form method="POST" action="{{ route('doc.situations.encaissement') }}" class="mt-3 flex flex-wrap items-end gap-2 border-t border-zinc-800 pt-3">
                    @csrf
                    <input type="hidden" name="doc_id" value="{{ $doc->id }}">
                    <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                    <input type="hidden" name="date_to" value="{{ $dateTo }}">
                    <div class="flex min-w-0 flex-1 flex-col gap-1 sm:max-w-[200px]">
                        <label class="text-xs font-medium text-zinc-500">Montant à enregistrer (DHS)</label>
                        <input type="number" name="montant" step="0.01" min="0.01" value="{{ old('montant') }}" class="auth-input w-full rounded-lg border {{ $errors->has('montant') ? 'border-red-500' : 'border-zinc-600' }} bg-zinc-800/90 px-3 py-2 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="0.00">
                    </div>
                    <button type="submit" class="rounded-lg border border-[#967A4B]/60 bg-[#967A4B]/20 px-4 py-2 text-sm font-medium text-[#967A4B] hover:bg-[#967A4B]/30">Enregistrer</button>
                </form>
                <p class="mt-2 text-xs text-zinc-500">La date enregistrée est celle du jour où vous cliquez sur Enregistrer.</p>
                @error('montant')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
                @else
                <p class="mt-2 text-xs text-zinc-500">Période close — saisie et suppression des encaissements désactivées.</p>
                @endif
            </div>
            <div class="rounded-lg border border-[#967A4B]/40 bg-[#967A4B]/10 p-3">
                <p class="text-xs text-zinc-500">Reste à payer (fin de période)</p>
                <p class="mt-1 text-lg font-bold text-[#967A4B]">{{ number_format($soldeFinPeriode, 0, ',', ' ') }} DHS</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-sm">
                <thead>
                    <tr class="border-b border-[#967A4B]/30 bg-zinc-800/50">
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Patient</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">N° fiche</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Nature de prothèse</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B] text-right">Montant (DHS)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $group)
                    @php $lineCount = count($group['lines']); @endphp
                    @foreach($group['lines'] as $i => $line)
                    <tr class="border-b border-zinc-800 hover:bg-zinc-800/50">
                        @if($i === 0)
                        <td rowspan="{{ $lineCount }}" class="px-4 py-3 align-top text-zinc-200">{{ $group['patient'] }}</td>
                        <td rowspan="{{ $lineCount }}" class="px-4 py-3 align-top text-zinc-300">{{ $group['numero_fiche'] ?: '—' }}</td>
                        @endif
                        <td class="px-4 py-3 text-zinc-300">{{ $line['nature'] }}</td>
                        @if($i === 0)
                        <td rowspan="{{ $lineCount }}" class="px-4 py-3 text-right font-medium align-top text-zinc-200">{{ number_format((float) $group['amount'], 0, ',', ' ') }}</td>
                        @endif
                    </tr>
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-zinc-500">Aucun travail sur cette période.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-800 bg-zinc-900/60 px-4 py-4">
            <h3 class="text-sm font-semibold text-[#967A4B]">Encaissements enregistrés (situation)</h3>
            <p class="mt-1 text-xs text-zinc-500">Détail par date d’enregistrement (jour du clic sur Enregistrer). Indépendant de la comptabilité factures.</p>

            <div class="mt-3 overflow-x-auto">
                <table class="w-full min-w-[420px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-700 bg-zinc-800/50">
                            <th class="px-4 py-2.5 font-semibold text-zinc-400">Date d’enregistrement</th>
                            <th class="px-4 py-2.5 font-semibold text-zinc-400 text-right">Montant (DHS)</th>
                            @if($situationEncaissementEditable ?? false)
                            <th class="px-4 py-2.5 font-semibold text-zinc-400 w-28"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($encaissementsDuPeriode as $enc)
                        <tr class="border-b border-zinc-800 hover:bg-zinc-800/40">
                            <td class="px-4 py-2.5 text-zinc-200">{{ $enc->paid_on?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2.5 text-right font-medium text-zinc-200">{{ number_format((float) $enc->montant, 0, ',', ' ') }}</td>
                            @if($situationEncaissementEditable ?? false)
                            <td class="px-4 py-2.5 text-right">
                                <form method="POST" action="{{ route('doc.situations.encaissement.destroy', $enc) }}" class="inline" onsubmit="return confirm('Supprimer cet encaissement ?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                    <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                    <button type="submit" class="text-xs text-red-400 hover:underline">Supprimer</button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ ($situationEncaissementEditable ?? false) ? 3 : 2 }}" class="px-4 py-6 text-center text-zinc-500">Aucun encaissement enregistré pour cette période.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($encaissementsDuPeriode->isNotEmpty())
                    <tfoot>
                        <tr class="border-t border-zinc-700 bg-zinc-800/30">
                            <td class="px-4 py-2.5 text-right text-sm font-medium text-zinc-400">Total</td>
                            <td class="px-4 py-2.5 text-right text-sm font-semibold text-zinc-200">{{ number_format((float) $encaissementsDuPeriode->sum('montant'), 0, ',', ' ') }}</td>
                            @if($situationEncaissementEditable ?? false)
                            <td></td>
                            @endif
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        @if(count($groups) > 0 || $carryover > 0)
        <div class="border-t border-[#967A4B]/30 bg-zinc-800/50 px-4 py-3 flex justify-end">
            <p class="text-lg font-bold text-[#967A4B]">Reste de la période : {{ number_format($soldeFinPeriode, 0, ',', ' ') }} DHS</p>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
