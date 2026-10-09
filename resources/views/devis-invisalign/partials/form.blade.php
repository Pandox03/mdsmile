@php
    $isEdit = isset($devis);
    $lignesOld = old('lignes');
    if ($lignesOld !== null) {
        $lignesRows = $lignesOld;
    } elseif ($isEdit && $devis->lignes->isNotEmpty()) {
        $lignesRows = $devis->lignes->map(fn ($l) => [
            'designation' => $l->designation,
            'quantite' => $l->quantite ?? 1,
            'arcade' => $l->arcade,
            'prix_unitaire' => $l->prix_unitaire,
            'total_ttc' => $l->total_ttc,
        ])->all();
    } else {
        $lignesRows = [
            ['designation' => 'Z 18 Sup', 'quantite' => '1', 'arcade' => '', 'prix_unitaire' => '6000', 'total_ttc' => '6000'],
            ['designation' => 'Z 18 Inf', 'quantite' => '1', 'arcade' => '', 'prix_unitaire' => '6000', 'total_ttc' => '6000'],
        ];
    }
@endphp

<div id="devis-invisalign-sheet" class="mx-auto max-w-[210mm] border border-[#967A4B]/35 bg-black p-6 text-sm text-white shadow-xl [color-scheme:dark] sm:p-10">
    <style>
        #devis-invisalign-sheet #di-lignes-table tbody td { background-color: #000000 !important; }
        #devis-invisalign-sheet #di-lignes-table input.di-ligne-input {
            background-color: #000000 !important;
            color: #f4f4f5 !important;
            -webkit-text-fill-color: #f4f4f5 !important;
            caret-color: #f4f4f5;
        }
        #devis-invisalign-sheet #di-lignes-table input.di-ligne-input::placeholder { color: #a1a1aa !important; }
    </style>

    <div class="mb-6 flex flex-col gap-4 border-b border-zinc-700 pb-6 sm:flex-row sm:justify-between">
        <div class="flex items-start gap-3">
            <img src="{{ asset('images/mdsmile-logo.png') }}" alt="" class="h-12 w-auto object-contain brightness-110">
            <div>
                <p class="font-semibold text-white">{{ \App\Models\Setting::get('lab_name', 'Laboratoire') }}</p>
                <p class="mt-1 text-xs font-medium uppercase tracking-wide text-[#967A4B]">Devis Invisalign</p>
                @if(\App\Models\Setting::get('lab_adresse'))
                <p class="mt-1 whitespace-pre-line text-xs text-zinc-400">{{ \App\Models\Setting::get('lab_adresse') }}</p>
                @endif
            </div>
        </div>
        <div class="grid gap-2 text-right text-xs text-zinc-300 sm:text-sm">
            <div class="flex flex-wrap items-center justify-end gap-2">
                <span class="font-bold uppercase tracking-wide text-white">Devis N°</span>
                @if($isEdit)
                <span class="rounded border border-[#967A4B]/50 bg-zinc-900 px-2 py-1 font-mono font-semibold text-[#967A4B]">{{ $devis->numero }}</span>
                @else
                <span class="rounded border border-dashed border-[#967A4B] bg-zinc-900 px-2 py-1 font-mono text-[#E8D4B0]" title="Numéro attribué à l'enregistrement">{{ $previewNumero ?? '' }}</span>
                @endif
            </div>
            <label class="flex items-center justify-end gap-2">
                <span class="text-zinc-300">Date</span>
                <input type="date" name="date_devis" value="{{ old('date_devis', $isEdit ? $devis->date_devis?->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="rounded border border-zinc-600 bg-zinc-900 px-2 py-1 text-white">
            </label>
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase text-zinc-300">Client (cabinet / Dr)</label>
            <input type="text" name="client_nom" value="{{ old('client_nom', $isEdit ? $devis->client_nom : '') }}" required class="w-full rounded border border-zinc-600 bg-zinc-900 px-3 py-2 text-white placeholder-zinc-500" placeholder="Dr …">
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase text-zinc-300">Patient</label>
            <input type="text" name="patient_nom" value="{{ old('patient_nom', $isEdit ? $devis->patient_nom : '') }}" required class="w-full rounded border border-zinc-600 bg-zinc-900 px-3 py-2 text-white placeholder-zinc-500">
        </div>
        <div class="sm:col-span-2">
            <label class="mb-1 block text-xs font-semibold uppercase text-zinc-300">Lier à une fiche client (optionnel)</label>
            <select name="doc_id" id="di-doc-id" class="w-full rounded border border-zinc-600 bg-zinc-900 px-3 py-2 text-white">
                <option value="">— Aucune —</option>
                @foreach($docs as $doc)
                <option value="{{ $doc->id }}" data-client-name="{{ $doc->name }}" {{ (string) old('doc_id', $isEdit ? $devis->doc_id : ($selectedDocId ?? '')) === (string) $doc->id ? 'selected' : '' }}>{{ $doc->display_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mb-6 flex flex-wrap gap-6 border-y border-zinc-700 bg-zinc-950 py-4">
        <label class="flex items-center gap-2 font-semibold text-white">
            <input type="checkbox" name="opt_maxillaire" value="1" class="h-4 w-4 rounded border-zinc-500 accent-[#967A4B]" {{ old('opt_maxillaire', $isEdit ? $devis->opt_maxillaire : false) ? 'checked' : '' }}>
            MAXILLAIRE
        </label>
        <label class="flex items-center gap-2 font-semibold text-white">
            <input type="checkbox" name="opt_mandibulaire" value="1" class="h-4 w-4 rounded border-zinc-500 accent-[#967A4B]" {{ old('opt_mandibulaire', $isEdit ? $devis->opt_mandibulaire : false) ? 'checked' : '' }}>
            MANDIBULAIRE
        </label>
        <label class="flex items-center gap-2 font-semibold text-white">
            <input type="checkbox" name="taquets" value="1" class="h-4 w-4 rounded border-zinc-500 accent-[#967A4B]" {{ old('taquets', $isEdit ? $devis->taquets : true) ? 'checked' : '' }}>
            TAQUETS
        </label>
        <label class="flex items-center gap-2 font-semibold text-white">
            <input type="checkbox" name="stripping" value="1" class="h-4 w-4 rounded border-zinc-500 accent-[#967A4B]" {{ old('stripping', $isEdit ? $devis->stripping : true) ? 'checked' : '' }}>
            STRIPPING
        </label>
    </div>

    <div class="mb-2 flex items-center justify-between">
        <h2 class="text-xs font-bold uppercase tracking-wider text-white">Lignes du devis</h2>
        <button type="button" id="di-add-ligne" class="rounded border border-[#967A4B] bg-[#967A4B] px-3 py-1.5 text-xs font-semibold text-black hover:bg-[#B8986B]">+ Ligne</button>
    </div>
    <div class="overflow-x-auto border border-zinc-600">
        <table class="w-full border-collapse text-xs sm:text-sm" id="di-lignes-table">
            <thead>
                <tr class="bg-zinc-800 text-left text-[11px] font-bold uppercase text-white">
                    <th class="border border-zinc-600 px-2 py-2">Désignation</th>
                    <th class="w-24 border border-zinc-600 px-2 py-2 text-right">Quantité</th>
                    <th class="w-28 border border-zinc-600 px-2 py-2">Arcade</th>
                    <th class="w-32 border border-zinc-600 px-2 py-2 text-right">Prix unitaire</th>
                    <th class="w-32 border border-zinc-600 px-2 py-2 text-right">Total HT</th>
                    <th class="w-10 border border-zinc-600 px-1 py-2"></th>
                </tr>
            </thead>
            <tbody id="di-lignes-body">
                @foreach($lignesRows as $idx => $row)
                <tr class="di-ligne-row">
                    <td class="border border-zinc-600 !bg-black p-0">
                        <input type="text" name="lignes[{{ $idx }}][designation]" value="{{ $row['designation'] ?? '' }}" required class="di-ligne-input w-full border-0 px-2 py-2 focus:ring-2 focus:ring-[#967A4B]/50" style="background-color:#000;color:#f4f4f5;-webkit-text-fill-color:#f4f4f5;" placeholder="Ex. Z 18 Sup">
                    </td>
                    <td class="border border-zinc-600 !bg-black p-0">
                        <input type="number" step="1" min="0" name="lignes[{{ $idx }}][quantite]" value="{{ $row['quantite'] ?? 1 }}" class="di-ligne-input di-qty w-full border-0 px-2 py-2 text-right focus:ring-2 focus:ring-[#967A4B]/50" style="background-color:#000;color:#f4f4f5;-webkit-text-fill-color:#f4f4f5;" placeholder="1">
                    </td>
                    <td class="border border-zinc-600 !bg-black p-0">
                        <input type="text" name="lignes[{{ $idx }}][arcade]" value="{{ $row['arcade'] ?? '' }}" class="di-ligne-input w-full border-0 px-2 py-2 focus:ring-2 focus:ring-[#967A4B]/50" style="background-color:#000;color:#f4f4f5;-webkit-text-fill-color:#f4f4f5;">
                    </td>
                    <td class="border border-zinc-600 !bg-black p-0">
                        <input type="number" step="0.01" min="0" name="lignes[{{ $idx }}][prix_unitaire]" value="{{ $row['prix_unitaire'] ?? '' }}" class="di-ligne-input di-pu w-full border-0 px-2 py-2 text-right focus:ring-2 focus:ring-[#967A4B]/50" style="background-color:#000;color:#f4f4f5;-webkit-text-fill-color:#f4f4f5;" placeholder="0">
                    </td>
                    <td class="border border-zinc-600 !bg-black p-0">
                        @php
                            $lineQty = (float) ($row['quantite'] ?? 1);
                            $linePu = (float) ($row['prix_unitaire'] ?? 0);
                        @endphp
                        <input type="number" step="0.01" min="0" name="lignes[{{ $idx }}][total_ttc]" value="{{ number_format($lineQty * $linePu, 2, '.', '') }}" readonly tabindex="-1" class="di-ligne-input di-ht w-full border-0 px-2 py-2 text-right font-semibold" style="background-color:#000;color:#f4f4f5;-webkit-text-fill-color:#f4f4f5;" placeholder="0">
                    </td>
                    <td class="border border-zinc-600 p-1 text-center">
                        <button type="button" class="di-remove-ligne text-red-400 hover:text-red-300" title="Retirer">&times;</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex justify-end border-t border-zinc-700 pt-4">
        <p class="text-sm font-bold text-white">TOTAL HT : <span id="di-total-display" class="tabular-nums text-[#967A4B]">0,00</span></p>
    </div>

    <div class="mt-8 space-y-4 border-t border-zinc-700 pt-6">
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase text-zinc-300">Conditions de paiement</label>
            <textarea name="conditions_paiement" rows="3" class="w-full rounded border border-zinc-600 bg-zinc-900 px-3 py-2 text-white placeholder-zinc-500" placeholder="50% à la commande / 50% à la livraison">{{ old('conditions_paiement', $isEdit ? $devis->conditions_paiement : ($defaults['conditions_paiement'] ?? '')) }}</textarea>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-zinc-300">Banque</label>
                <input type="text" name="banque_ligne" value="{{ old('banque_ligne', $isEdit ? $devis->banque_ligne : ($defaults['banque_ligne'] ?? '')) }}" class="w-full rounded border border-zinc-600 bg-zinc-900 px-3 py-2 text-white" placeholder="Banque …">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-zinc-300">Bénéficiaire</label>
                <input type="text" name="beneficiaire" value="{{ old('beneficiaire', $isEdit ? $devis->beneficiaire : ($defaults['beneficiaire'] ?? '')) }}" class="w-full rounded border border-zinc-600 bg-zinc-900 px-3 py-2 text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-zinc-300">RIB</label>
                <input type="text" name="rib" value="{{ old('rib', $isEdit ? $devis->rib : ($defaults['rib'] ?? '')) }}" class="w-full rounded border border-zinc-600 bg-zinc-900 px-3 py-2 font-mono text-sm text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-zinc-300">E-mail commandes</label>
                <input type="email" name="commandes_email" value="{{ old('commandes_email', $isEdit ? $devis->commandes_email : ($defaults['commandes_email'] ?? '')) }}" class="w-full rounded border border-zinc-600 bg-zinc-900 px-3 py-2 text-white">
            </div>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase text-zinc-300">Mentions légales / pied de page</label>
            <textarea name="societe_footer" rows="3" class="w-full rounded border border-zinc-600 bg-zinc-900 px-3 py-2 text-xs text-white">{{ old('societe_footer', $isEdit ? $devis->societe_footer : ($defaults['societe_footer'] ?? '')) }}</textarea>
        </div>
    </div>
</div>

<script>
(function() {
    var body = document.getElementById('di-lignes-body');
    var addBtn = document.getElementById('di-add-ligne');
    var totalEl = document.getElementById('di-total-display');
    var docSelect = document.getElementById('di-doc-id');
    var clientInput = document.querySelector('input[name="client_nom"]');
    var inputStyle = 'background-color:#000;color:#f4f4f5;-webkit-text-fill-color:#f4f4f5;caret-color:#f4f4f5;';

    function parseNum(v) {
        var n = parseFloat(String(v).replace(',', '.'));
        return isNaN(n) ? 0 : n;
    }

    function reindexRows() {
        if (!body) return;
        body.querySelectorAll('.di-ligne-row').forEach(function(row, idx) {
            row.querySelectorAll('input').forEach(function(inp) {
                var name = inp.getAttribute('name');
                if (name && name.indexOf('lignes[') === 0) {
                    inp.setAttribute('name', name.replace(/lignes\[\d+\]/, 'lignes[' + idx + ']'));
                }
            });
        });
    }

    function syncRow(row) {
        var qty = row.querySelector('.di-qty');
        var pu = row.querySelector('.di-pu');
        var ht = row.querySelector('.di-ht');
        if (!pu || !ht) return;
        var q = qty && qty.value !== '' ? parseNum(qty.value) : 0;
        var price = pu.value !== '' ? parseNum(pu.value) : 0;
        ht.value = (q * price).toFixed(2);
    }

    function refreshTotal() {
        if (!body || !totalEl) return;
        var sum = 0;
        body.querySelectorAll('.di-ligne-row').forEach(function(row) {
            syncRow(row);
            var ht = row.querySelector('.di-ht');
            if (ht) sum += parseNum(ht.value);
        });
        totalEl.textContent = sum.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function bindRow(row) {
        row.querySelectorAll('.di-qty, .di-pu').forEach(function(inp) {
            inp.addEventListener('input', refreshTotal);
        });
        var rm = row.querySelector('.di-remove-ligne');
        if (rm) {
            rm.addEventListener('click', function() {
                if (body.querySelectorAll('.di-ligne-row').length <= 1) return;
                row.remove();
                reindexRows();
                refreshTotal();
            });
        }
    }

    if (body) {
        body.querySelectorAll('.di-ligne-row').forEach(bindRow);
        refreshTotal();
    }

    if (addBtn && body) {
        addBtn.addEventListener('click', function() {
            var idx = body.querySelectorAll('.di-ligne-row').length;
            var tr = document.createElement('tr');
            tr.className = 'di-ligne-row';
            tr.innerHTML =
                '<td class="border border-zinc-600 !bg-black p-0"><input type="text" name="lignes[' + idx + '][designation]" required class="di-ligne-input w-full border-0 px-2 py-2 focus:ring-2 focus:ring-[#967A4B]/50" style="' + inputStyle + '"></td>' +
                '<td class="border border-zinc-600 !bg-black p-0"><input type="number" step="1" min="0" name="lignes[' + idx + '][quantite]" value="1" class="di-ligne-input di-qty w-full border-0 px-2 py-2 text-right focus:ring-2 focus:ring-[#967A4B]/50" style="' + inputStyle + '"></td>' +
                '<td class="border border-zinc-600 !bg-black p-0"><input type="text" name="lignes[' + idx + '][arcade]" class="di-ligne-input w-full border-0 px-2 py-2 focus:ring-2 focus:ring-[#967A4B]/50" style="' + inputStyle + '"></td>' +
                '<td class="border border-zinc-600 !bg-black p-0"><input type="number" step="0.01" min="0" name="lignes[' + idx + '][prix_unitaire]" class="di-ligne-input di-pu w-full border-0 px-2 py-2 text-right focus:ring-2 focus:ring-[#967A4B]/50" style="' + inputStyle + '"></td>' +
                '<td class="border border-zinc-600 !bg-black p-0"><input type="number" step="0.01" min="0" name="lignes[' + idx + '][total_ttc]" readonly tabindex="-1" class="di-ligne-input di-ht w-full border-0 px-2 py-2 text-right font-semibold" style="' + inputStyle + '"></td>' +
                '<td class="border border-zinc-600 p-1 text-center"><button type="button" class="di-remove-ligne text-red-400 hover:text-red-300" title="Retirer">&times;</button></td>';
            body.appendChild(tr);
            bindRow(tr);
            reindexRows();
            refreshTotal();
        });
    }

    if (docSelect && clientInput) {
        docSelect.addEventListener('change', function() {
            var opt = docSelect.options[docSelect.selectedIndex];
            var name = opt && opt.getAttribute('data-client-name');
            if (name && !clientInput.value.trim()) clientInput.value = name;
        });
    }
})();
</script>
