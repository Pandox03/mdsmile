@extends('layouts.dashboard')

@push('vite')
@vite(['resources/js/odontogram/index.jsx'])
@endpush

@section('content')
<div class="space-y-8" x-data="travailForm()">
    {{-- Title --}}
    <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
        <h1 class="text-xl font-bold text-[#967A4B]">Créer un travail</h1>
    </div>

    @if(session('success'))
    <div class="rounded-lg border border-emerald-500/40 bg-emerald-500/10 p-4 text-sm text-emerald-400">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-400">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('travaux.store') }}" class="space-y-8">
        @csrf

        {{-- Doc section --}}
        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
            <h2 class="mb-4 text-lg font-semibold text-[#967A4B]">Dentiste (Doc)</h2>
            <div class="space-y-4">
                <div class="flex flex-wrap gap-4">
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="radio" name="add_new_doc" value="0" checked x-model="addNewDoc" class="auth-checkbox">
                        <span class="text-zinc-300">Choisir un doc existant</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="radio" name="add_new_doc" value="1" x-model="addNewDoc" class="auth-checkbox">
                        <span class="text-zinc-300">Ajouter un nouveau doc</span>
                    </label>
                </div>

                <div x-show="addNewDoc != 1" x-cloak>
                    <select name="doc_id" class="auth-input w-full max-w-md rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                        <option value="">— Sélectionner un doc —</option>
                        @foreach($docs as $doc)
                            <option value="{{ $doc->id }}" {{ old('doc_id', request('doc_id')) == $doc->id ? 'selected' : '' }}>{{ $doc->display_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="addNewDoc == 1" x-cloak class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-400">N° d'enregistrement</label>
                        <input type="text" name="doc_numero_registration" value="{{ old('doc_numero_registration') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-400">Nom</label>
                        <input type="text" name="doc_name" value="{{ old('doc_name') }}" x-bind:required="addNewDoc == 1" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-400">Téléphone</label>
                        <input type="text" name="doc_phone" value="{{ old('doc_phone') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-400">Email</label>
                        <input type="email" name="doc_email" value="{{ old('doc_email') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-zinc-400">Adresse</label>
                        <textarea name="doc_adresse" rows="2" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">{{ old('doc_adresse') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Patient & basic info --}}
        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
            <h2 class="mb-4 text-lg font-semibold text-[#967A4B]">Patient & Informations</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Patient</label>
                    <input type="text" name="patient" value="{{ old('patient') }}" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Numéro de fiche</label>
                    <input type="text" name="numero_fiche" value="{{ old('numero_fiche') }}" placeholder="—" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 placeholder-zinc-500 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Âge du patient</label>
                    <input type="number" name="patient_age" value="{{ old('patient_age') }}" min="1" max="150" placeholder="—" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 placeholder-zinc-500 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Date d'entrée</label>
                    <input type="date" name="date_entree" value="{{ old('date_entree', date('Y-m-d')) }}" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Date de livraison</label>
                    <input type="date" name="date_livraison" value="{{ old('date_livraison') }}" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Date d'essiage</label>
                    <input type="date" name="date_essiage" value="{{ old('date_essiage') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                </div>
                @hasanyrole('manager|secretaire')
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Prix total (DHS)</label>
                    <input type="number" id="prix-dhs-input" name="prix_dhs" value="{{ old('prix_dhs', 0) }}" min="0" step="0.01" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                    <p class="mt-1 text-xs text-zinc-500">Somme des prestations (mise à jour auto). Vous pouvez modifier le montant si besoin.</p>
                </div>
                @endhasanyrole
            </div>
        </div>

        {{-- Teeth (React odontogram) --}}
        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
            <h2 class="mb-4 text-lg font-semibold text-[#967A4B]">Dents concernées</h2>
            <p class="mb-4 text-sm text-zinc-500">Cliquez sur les dents, choisissez une prestation dans la liste puis appliquez. Vous pouvez appliquer des prestations différentes à différents groupes de dents.</p>

            @php
                $stockItemsForJs = collect($stockItems ?? [])->map(fn ($s) => [
                    'id' => $s->id ?? $s['id'] ?? null,
                    'name' => $s->name ?? $s['name'] ?? '',
                    'quantity' => $s->quantity ?? $s['quantity'] ?? 0,
                    'unite' => $s->unite ?? $s['unite'] ?? '',
                ])->values()->all();
                $prestationsForJs = collect($categories ?? [])->flatMap(fn ($cat) => collect($cat->prestations ?? [])->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'categoryName' => $cat->name,
                ]))->values()->all();
            @endphp
            <script>
                window.MDSMILE_STOCK_ITEMS = @json($stockItemsForJs);
                window.MDSMILE_PRESTATIONS = @json($prestationsForJs);
                window.MDSMILE_PRESTATION_DEFAULT_PRICES = @json($prestationDefaultPrices ?? []);
                window.MDSMILE_DOC_OVERRIDE_PRICES = @json($docOverridePrices ?? []);
            </script>
            <div id="odontogram-root"></div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-6 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">
                Créer le travail
            </button>
            <a href="{{ route('travaux.index') }}" class="rounded-lg border border-zinc-600 px-6 py-2.5 text-sm font-medium text-zinc-400 hover:bg-zinc-800">
                Annuler
            </a>
        </div>
    </form>
</div>

<script>
function travailForm() {
    return {
        addNewDoc: {{ old('add_new_doc', false) ? 'true' : 'false' }},
    };
}

(function() {
    var defaultPrices = window.MDSMILE_PRESTATION_DEFAULT_PRICES || {};
    var docOverrides = window.MDSMILE_DOC_OVERRIDE_PRICES || {};
    var prixEl = document.getElementById('prix-dhs-input');
    if (!prixEl) return;

    function getUnitPrice(docId, prestationId) {
        if (docId == null || docId === '' || prestationId == null || prestationId === '') return 0;
        var d = String(docId), p = String(prestationId);
        var override = docOverrides[d] && docOverrides[d][p];
        if (override !== undefined && override !== null) return Number(override);
        var def = defaultPrices[p] !== undefined ? defaultPrices[p] : defaultPrices[prestationId];
        return (def !== undefined && def !== null) ? Number(def) : 0;
    }

    function updatePrix() {
        var docSelect = document.querySelector('select[name="doc_id"]');
        var docId = docSelect && docSelect.value ? docSelect.value : null;
        var teethList = window.MDSMILE_TEETH_LIST || [];
        var total = 0;
        teethList.forEach(function(t) {
            if (t.prestation_id != null && t.prestation_id !== '') total += getUnitPrice(docId, t.prestation_id);
        });
        prixEl.value = total;
    }

    window.MDSMILE_UPDATE_PRIX = updatePrix;
    document.querySelector('select[name="doc_id"]')?.addEventListener('change', updatePrix);
    window.addEventListener('teeth-count-changed', function(e) {
        window.MDSMILE_TEETH_COUNT = (e && e.detail && e.detail.count !== undefined) ? e.detail.count : 0;
        window.MDSMILE_TEETH_LIST = (e && e.detail && e.detail.teeth) ? e.detail.teeth : (window.MDSMILE_TEETH_LIST || []);
        updatePrix();
    });
    updatePrix();
})();
</script>
<style>[x-cloak]{display:none!important}</style>
@endsection
