@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
        <h1 class="text-xl font-bold text-[#967A4B]">Nouvelle facture</h1>
    </div>

    @if($errors->any())
    <div class="rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-400">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('factures.store') }}" class="space-y-8">
        @csrf
        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
            <h2 class="mb-4 text-lg font-semibold text-[#967A4B]">Informations facture</h2>
            <p class="mb-4 text-sm text-zinc-500">Le numéro de facture est attribué automatiquement (ex. FAC-001, FAC-002…). Configurez le prochain numéro dans <a href="{{ route('parametres.index') }}" class="text-[#967A4B] hover:underline">Paramètres</a>.</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Client (dentiste) <span class="text-red-400">*</span></label>
                    <select name="doc_id" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                        <option value="">— Sélectionner un client —</option>
                        @foreach($docs as $d)
                            <option value="{{ $d->id }}" {{ old('doc_id', $selectedDocId) == $d->id ? 'selected' : '' }}>{{ $d->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Date facture <span class="text-red-400">*</span></label>
                    <input type="date" name="date_facture" value="{{ old('date_facture', date('Y-m-d')) }}" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Montant (DHS) <span class="text-red-400">*</span></label>
                    <input type="number" name="montant" value="{{ old('montant') }}" min="0.01" step="0.01" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-right text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-6 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">
                Créer la facture
            </button>
            <a href="{{ route('factures.index') }}" class="rounded-lg border border-zinc-600 px-6 py-2.5 text-sm font-medium text-zinc-400 hover:bg-zinc-800">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
        <h1 class="text-xl font-bold text-[#967A4B]">Nouvelle facture</h1>
        <p class="mt-1 text-sm text-zinc-400">Répartissez le montant sur un ou plusieurs travaux. Le total = somme des lignes &gt; 0.</p>
    </div>

    @if($errors->any())
    <div class="rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-400">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="GET" action="{{ route('factures.create') }}" class="rounded-xl border border-zinc-700 bg-zinc-900/50 p-4">
        <label class="mb-2 block text-sm font-medium text-zinc-400">Choisir un client pour afficher ses travaux</label>
        <div class="flex flex-wrap items-end gap-3">
            <select name="doc_id" onchange="this.form.submit()" class="auth-input min-w-[240px] rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                <option value="">— Sélectionner un client —</option>
                @foreach($docs as $d)
                    <option value="{{ $d->id }}" {{ (string)($selectedDocId ?? '') === (string)$d->id ? 'selected' : '' }}>{{ $d->display_name }}</option>
                @endforeach
            </select>
            @if($selectedDocId)
            <a href="{{ route('factures.create') }}" class="text-sm text-zinc-500 hover:text-[#967A4B]">Réinitialiser</a>
            @endif
        </div>
    </form>

    @if($selectedDocId && $travauxEligibles->isEmpty())
    <div class="rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
        Aucun travail avec reste à facturer pour ce client (tout est déjà couvert par des factures ou aucun travail actif).
    </div>
    @endif

    @if($selectedDocId && $travauxEligibles->isNotEmpty())
    <form method="POST" action="{{ route('factures.store') }}" class="space-y-8" id="facture-form">
        @csrf
        <input type="hidden" name="doc_id" value="{{ $selectedDocId }}">

        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
            <h2 class="mb-4 text-lg font-semibold text-[#967A4B]">Informations facture</h2>
            <p class="mb-4 text-sm text-zinc-500">Le numéro est attribué automatiquement (FAC-001…). Configurez le prochain numéro dans <a href="{{ route('parametres.index') }}" class="text-[#967A4B] hover:underline">Paramètres</a>.</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Date facture <span class="text-red-400">*</span></label>
                    <input type="date" name="date_facture" value="{{ old('date_facture', date('Y-m-d')) }}" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
            <h2 class="mb-4 text-lg font-semibold text-[#967A4B]">Ventilation sur les travaux</h2>
            <p class="mb-4 text-sm text-zinc-500">Saisissez le montant facturé pour chaque travail concerné (0 = ignorer).</p>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-[#967A4B]/30 bg-zinc-800/80">
                            <th class="px-3 py-3 font-semibold text-[#967A4B]">Travail</th>
                            <th class="px-3 py-3 font-semibold text-[#967A4B]">Patient</th>
                            <th class="px-3 py-3 font-semibold text-[#967A4B] text-right">Plafond</th>
                            <th class="px-3 py-3 font-semibold text-[#967A4B] text-right">Déjà facturé</th>
                            <th class="px-3 py-3 font-semibold text-[#967A4B] text-right">Reste max</th>
                            <th class="px-3 py-3 font-semibold text-[#967A4B] text-right w-36">Montant (DHS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($travauxEligibles as $t)
                        @php
                            $reste = $t->montant_restant_a_facturer;
                            $deja = $t->montant_comptabilise_cap - $reste;
                        @endphp
                        <tr class="border-b border-zinc-800">
                            <td class="px-3 py-2 font-mono text-zinc-200">{{ $t->reference }}</td>
                            <td class="px-3 py-2 text-zinc-300">{{ $t->patient }}</td>
                            <td class="px-3 py-2 text-right text-zinc-400">{{ number_format($t->montant_comptabilise_cap, 0, ',', ' ') }}</td>
                            <td class="px-3 py-2 text-right text-zinc-400">{{ number_format(max(0, $deja), 0, ',', ' ') }}</td>
                            <td class="px-3 py-2 text-right text-[#967A4B]">{{ number_format($reste, 0, ',', ' ') }}</td>
                            <td class="px-3 py-2">
                                <input type="hidden" name="lignes[{{ $loop->index }}][travail_id]" value="{{ $t->id }}">
                                <input type="number" name="lignes[{{ $loop->index }}][montant]" value="{{ old('lignes.'.$loop->index.'.montant', '0') }}" min="0" step="0.01" max="{{ $reste }}" class="ligne-montant auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-2 py-2 text-right text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-4 text-sm text-zinc-400">Total des lignes saisies : <span id="total-lignes" class="font-semibold text-[#967A4B]">0</span> DHS</p>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-6 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">
                Créer la facture
            </button>
            <a href="{{ route('factures.index') }}" class="rounded-lg border border-zinc-600 px-6 py-2.5 text-sm font-medium text-zinc-400 hover:bg-zinc-800">
                Annuler
            </a>
        </div>
    </form>
    <script>
        document.querySelectorAll('#facture-form .ligne-montant').forEach(function (el) {
            el.addEventListener('input', function () {
                var t = 0;
                document.querySelectorAll('#facture-form .ligne-montant').forEach(function (i) {
                    var v = parseFloat(String(i.value).replace(',', '.'));
                    if (!isNaN(v) && v > 0) t += v;
                });
                document.getElementById('total-lignes').textContent = (Math.round(t * 100) / 100).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            });
        });
        document.querySelector('#facture-form .ligne-montant')?.dispatchEvent(new Event('input'));
    </script>
    @endif
</div>
@endsection
