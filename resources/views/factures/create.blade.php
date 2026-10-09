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

    <form method="POST" action="{{ route('factures.store') }}" class="space-y-8" id="facture-create-form">
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
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Paiement <span class="text-red-400">*</span></label>
                    <select name="statut_paiement" id="facture-statut-paiement" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                        @foreach($statutPaiementLabels as $value => $label)
                            <option value="{{ $value }}" {{ old('statut_paiement', 'non_payee') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="facture-type-wrap">
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Type de paiement</label>
                    <select name="type_paiement" id="facture-type-paiement" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                        <option value="">— Choisir —</option>
                        @foreach($typePaiementLabels as $value => $label)
                            <option value="{{ $value }}" {{ old('type_paiement') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
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
<script>
(function() {
    var statut = document.getElementById('facture-statut-paiement');
    var wrap = document.getElementById('facture-type-wrap');
    var typeEl = document.getElementById('facture-type-paiement');
    if (!statut) return;
    function sync() {
        var paid = statut.value === 'payee';
        if (typeEl) typeEl.required = paid;
    }
    statut.addEventListener('change', sync);
    sync();
})();
</script>
@endsection
