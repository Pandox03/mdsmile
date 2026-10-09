@php
    $isEdit = isset($bon);
    $bon = $isEdit ? $bon : null;
    $prefill = $prefill ?? [];
    $v = function (string $key, $default = '') use ($isEdit, $bon, $prefill) {
        if ($isEdit) {
            return old($key, $bon->{$key} ?? $default);
        }
        return old($key, $prefill[$key] ?? $default);
    };
@endphp

<div class="mx-auto max-w-3xl space-y-6 rounded-xl border border-[#967A4B]/30 bg-zinc-900/80 p-6">
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs font-medium text-zinc-500">N° bon</label>
            @if($isEdit)
            <p class="rounded-lg border border-zinc-700 bg-zinc-800/80 px-3 py-2.5 font-mono text-[#967A4B]">{{ $bon->numero }}</p>
            @else
            <p class="rounded-lg border border-dashed border-[#967A4B]/50 bg-zinc-800/80 px-3 py-2.5 font-mono text-[#E8D4B0]">{{ $previewNumero ?? '' }}</p>
            @endif
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-zinc-500">Date de livraison</label>
            <input type="date" name="date_livraison" value="{{ $v('date_livraison', now()->toDateString()) }}" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100">
        </div>
    </div>

    <div>
        <label class="mb-1 block text-xs font-medium text-zinc-500">Depuis un travail (remplit dentiste, patient, détail, paiement)</label>
        <select name="travail_id" id="bl-travail-id" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100">
            <option value="">— Saisie manuelle —</option>
            @foreach($travaux as $t)
            <option value="{{ $t->id }}" {{ (string) $v('travail_id') === (string) $t->id ? 'selected' : '' }}>
                {{ $t->reference }} — {{ $t->patient ?: '—' }} ({{ $t->doc?->name ?? $t->dentiste ?? '—' }})
            </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-zinc-500">Choisir un travail remplit automatiquement les champs ci-dessous (modifiable).</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs font-medium text-zinc-500">Dentiste</label>
            <input type="text" name="dentiste_nom" id="bl-dentiste" value="{{ $v('dentiste_nom') }}" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100" placeholder="Dr …">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-zinc-500">Fiche client (optionnel)</label>
            <select name="doc_id" id="bl-doc-id" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100">
                <option value="">— Aucune —</option>
                @foreach($docs as $doc)
                <option value="{{ $doc->id }}" data-name="{{ $doc->name }}" {{ (string) $v('doc_id') === (string) $doc->id ? 'selected' : '' }}>{{ $doc->display_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-zinc-500">Patient</label>
            <input type="text" name="patient_nom" id="bl-patient" value="{{ $v('patient_nom') }}" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-zinc-500">N° fiche</label>
            <input type="text" name="numero_fiche" id="bl-fiche" value="{{ $v('numero_fiche') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-zinc-500">Réf. travail</label>
            <input type="text" name="reference_travail" id="bl-ref" value="{{ $v('reference_travail') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100" placeholder="TR-00001">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-zinc-500">Dents (FDI)</label>
            <input type="text" name="dents" id="bl-dents" value="{{ $v('dents') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100" placeholder="11, 12, 21…">
        </div>
    </div>

    <div>
        <label class="mb-1 block text-xs font-medium text-zinc-500">Détail du travail</label>
        <textarea name="details_travail" id="bl-details" rows="5" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100" placeholder="Prestation(s), phases, dents…">{{ $v('details_travail') }}</textarea>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs font-medium text-zinc-500">Montant (DHS)</label>
            <input type="number" step="0.01" min="0" name="montant" id="bl-montant" value="{{ $v('montant') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-zinc-500">Paiement</label>
            <select name="paiement_statut" id="bl-paiement" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100">
                @foreach($paiementLabels as $value => $label)
                <option value="{{ $value }}" {{ $v('paiement_statut', 'non_paye') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div id="bl-type-wrap" class="sm:col-span-2">
            <label class="mb-1 block text-xs font-medium text-zinc-500">Type de paiement</label>
            <select name="type_paiement" id="bl-type-paiement" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100">
                <option value="">— Choisir —</option>
                @foreach(($typePaiementLabels ?? []) as $value => $label)
                <option value="{{ $value }}" {{ $v('type_paiement') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="mb-1 block text-xs font-medium text-zinc-500">Observations</label>
        <textarea name="observations" rows="2" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100">{{ $v('observations') }}</textarea>
    </div>

    <div>
        <label class="mb-1 block text-xs font-medium text-zinc-500">Reçu par (cabinet)</label>
        <input type="text" name="recu_par" value="{{ $v('recu_par') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100" placeholder="Nom de la personne qui réceptionne">
    </div>
</div>

<script>
(function() {
    var select = document.getElementById('bl-travail-id');
    var paiementEl = document.getElementById('bl-paiement');
    var typeWrap = document.getElementById('bl-type-wrap');
    var typeEl = document.getElementById('bl-type-paiement');
    var urlBase = @json(url('/bons-livraison/prefill'));

    function syncTypePaiement() {
        if (!paiementEl || !typeWrap) return;
        var paid = paiementEl.value === 'paye' || paiementEl.value === 'partiel';
        if (typeEl) typeEl.required = paid;
    }

    function apply(data) {
        if (!data) return;
        var map = {
            'bl-dentiste': data.dentiste_nom,
            'bl-patient': data.patient_nom,
            'bl-fiche': data.numero_fiche,
            'bl-ref': data.reference_travail,
            'bl-dents': data.dents,
            'bl-details': data.details_travail,
            'bl-montant': data.montant,
            'bl-paiement': data.paiement_statut,
            'bl-type-paiement': data.type_paiement
        };
        Object.keys(map).forEach(function(id) {
            var el = document.getElementById(id);
            if (el && map[id] != null) el.value = map[id];
        });
        var dateEl = document.querySelector('input[name="date_livraison"]');
        if (dateEl && data.date_livraison) dateEl.value = data.date_livraison;
        var docEl = document.getElementById('bl-doc-id');
        if (docEl && data.doc_id != null) docEl.value = String(data.doc_id);
        syncTypePaiement();
    }

    if (paiementEl) paiementEl.addEventListener('change', syncTypePaiement);
    syncTypePaiement();

    if (select) {
        select.addEventListener('change', function() {
            var id = select.value;
            if (!id) return;
            fetch(urlBase + '/' + id, { headers: { 'Accept': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(apply)
                .catch(function() {});
        });
    }

    var docSelect = document.getElementById('bl-doc-id');
    var dentiste = document.getElementById('bl-dentiste');
    if (docSelect && dentiste) {
        docSelect.addEventListener('change', function() {
            var opt = docSelect.options[docSelect.selectedIndex];
            var name = opt && opt.getAttribute('data-name');
            if (name && !dentiste.value.trim()) dentiste.value = name;
        });
    }
})();
</script>
