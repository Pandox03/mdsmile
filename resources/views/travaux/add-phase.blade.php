@extends('layouts.dashboard')

@push('vite')
@vite(['resources/js/odontogram/index.jsx'])
@endpush

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
            <h1 class="text-xl font-bold text-[#967A4B]">Ajouter une phase</h1>
            <p class="mt-1 text-sm text-zinc-400">Travail {{ $travail->reference }} — {{ $travail->patient }}. Même(s) dent(s) avec une autre prestation ou matériau (ex. essayage puis finition).</p>
        </div>
        <a href="{{ route('travaux.show', $travail) }}" class="shrink-0 rounded-lg border border-[#967A4B]/50 bg-zinc-800 px-5 py-2.5 text-sm font-medium text-[#967A4B] hover:bg-[#967A4B]/10">
            ← Retour au travail
        </a>
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

    <form method="POST" action="{{ route('travaux.add-phase.store', $travail) }}" class="space-y-8">
        @csrf

        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
            <h2 class="mb-4 text-lg font-semibold text-[#967A4B]">Dents et prestation pour cette phase</h2>
            <p class="mb-4 text-sm text-zinc-500">Sélectionnez les dents concernées (souvent les mêmes que la phase précédente), choisissez la prestation puis appliquez.</p>

            @php
                $docId = $travail->doc_id;
            @endphp
            <script>
                window.MDSMILE_STOCK_ITEMS = @json([]);
                window.MDSMILE_PRESTATIONS = @json($prestationsForJs ?? []);
                window.MDSMILE_PRESTATION_DEFAULT_PRICES = @json($prestationDefaultPrices ?? []);
                window.MDSMILE_DOC_OVERRIDE_PRICES = @json($docOverridePrices ?? []);
                window.MDSMILE_DOC_ID = {{ $docId ?? 'null' }};
            </script>
            <div id="odontogram-root"></div>

            @hasanyrole('manager|secretaire')
            <div class="mt-6">
                <label class="mb-1 block text-sm font-medium text-zinc-400">Prix de cette phase (DHS)</label>
                <input type="number" id="prix-dhs-input" name="prix_dhs" value="{{ old('prix_dhs', 0) }}" min="0" step="0.01" class="auth-input w-full max-w-xs rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                <p class="mt-1 text-xs text-zinc-500">Sera ajouté au total du travail. Modifiable si besoin.</p>
            </div>
            @endhasanyrole
        </div>

        <div class="flex gap-4">
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-6 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">
                Ajouter la phase
            </button>
            <a href="{{ route('travaux.show', $travail) }}" class="rounded-lg border border-zinc-600 px-6 py-2.5 text-sm font-medium text-zinc-400 hover:bg-zinc-800">
                Annuler
            </a>
        </div>
    </form>
</div>

<script>
(function() {
    var defaultPrices = window.MDSMILE_PRESTATION_DEFAULT_PRICES || {};
    var docOverrides = window.MDSMILE_DOC_OVERRIDE_PRICES || {};
    var fixedDocId = window.MDSMILE_DOC_ID != null ? String(window.MDSMILE_DOC_ID) : null;
    var prixEl = document.getElementById('prix-dhs-input');
    if (!prixEl) return;

    function getUnitPrice(docId, prestationId) {
        if ((docId == null || docId === '') && fixedDocId) docId = fixedDocId;
        if (docId == null || docId === '' || prestationId == null || prestationId === '') return 0;
        var d = String(docId), p = String(prestationId);
        var override = docOverrides[d] && docOverrides[d][p];
        if (override !== undefined && override !== null) return Number(override);
        var def = defaultPrices[p] !== undefined ? defaultPrices[p] : defaultPrices[prestationId];
        return (def !== undefined && def !== null) ? Number(def) : 0;
    }

    function updatePrix() {
        var teethList = window.MDSMILE_TEETH_LIST || [];
        var total = 0;
        teethList.forEach(function(t) {
            if (t.prestation_id != null && t.prestation_id !== '') total += getUnitPrice(fixedDocId, t.prestation_id);
        });
        prixEl.value = total;
    }

    window.MDSMILE_UPDATE_PRIX = updatePrix;
    window.addEventListener('teeth-count-changed', function(e) {
        window.MDSMILE_TEETH_LIST = (e && e.detail && e.detail.teeth) ? e.detail.teeth : (window.MDSMILE_TEETH_LIST || []);
        updatePrix();
    });
    updatePrix();
})();
</script>
@endsection
