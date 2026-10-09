<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Doc;
use App\Models\Prestation;
use App\Models\PrestationCategory;
use App\Models\Stock;
use App\Models\Travail;
use App\Models\TravailTooth;
use App\Support\TypePaiement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TravauxController extends Controller
{
    public function create(): View
    {
        $categories = PrestationCategory::with('prestations')->orderBy('order')->orderBy('name')->get();
        $docs = Doc::with('docPrestationPrices')->orderBy('name')->get();

        $prestationDefaultPrices = [];
        foreach ($categories as $cat) {
            foreach ($cat->prestations as $p) {
                $prestationDefaultPrices[$p->id] = $p->price !== null ? (float) $p->price : null;
            }
        }

        $docOverridePrices = [];
        foreach ($docs as $doc) {
            foreach ($doc->docPrestationPrices as $ov) {
                if (!isset($docOverridePrices[$doc->id])) {
                    $docOverridePrices[$doc->id] = [];
                }
                $docOverridePrices[$doc->id][$ov->prestation_id] = $ov->price !== null ? (float) $ov->price : null;
            }
        }

        return view('travaux.create', [
            'docs' => $docs,
            'categories' => $categories,
            'stockItems' => [],
            'prestationDefaultPrices' => $prestationDefaultPrices,
            'docOverridePrices' => $docOverridePrices,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $isNewDoc = (bool) $request->boolean('add_new_doc');
        $rules = [
            'patient' => ['required', 'string', 'max:255'],
            'numero_fiche' => ['nullable', 'string', 'max:255', Rule::unique('travaux', 'numero_fiche')],
            'patient_age' => ['nullable', 'integer', 'min:1', 'max:150'],
            'date_entree' => ['required', 'date'],
            'date_livraison' => ['required', 'date', 'after_or_equal:date_entree'],
            'date_essiage' => ['nullable', 'date'],
            'prix_dhs' => ['nullable', 'numeric', 'min:0'],
        ];

        if ($isNewDoc) {
            $rules['doc_numero_registration'] = ['required', 'string', 'max:255'];
            $rules['doc_name'] = ['required', 'string', 'max:255'];
            $rules['doc_phone'] = ['nullable', 'string', 'max:255'];
            $rules['doc_email'] = ['nullable', 'email', 'max:255'];
            $rules['doc_adresse'] = ['nullable', 'string'];
        } else {
            $rules['doc_id'] = ['required', 'integer', 'exists:docs,id'];
        }

        $validated = $request->validate($rules);
        $teethData = $request->input('teeth', []);
        if (! is_array($teethData)) {
            $teethData = [];
        }

        $teethWithPrestation = [];
        foreach ($teethData as $toothNum => $data) {
            $prestationId = isset($data['prestation_id']) && $data['prestation_id'] !== '' ? (int) $data['prestation_id'] : null;
            if ($prestationId && (int) $toothNum >= 1 && (int) $toothNum <= 32) {
                $teethWithPrestation[] = ['tooth_number' => (int) $toothNum, 'prestation_id' => $prestationId];
            }
        }
        if (count($teethWithPrestation) === 0) {
            return back()->withErrors(['teeth' => 'Ajoutez au moins une dent avec une prestation.'])->withInput();
        }

        $travail = DB::transaction(function () use ($request, $validated, $isNewDoc, $teethWithPrestation) {
            $docId = null;
            $dentisteName = '';

            if ($isNewDoc) {
                $doc = Doc::create([
                    'numero_registration' => $validated['doc_numero_registration'],
                    'name' => $validated['doc_name'],
                    'phone' => $validated['doc_phone'] ?? null,
                    'email' => $validated['doc_email'] ?? null,
                    'adresse' => $validated['doc_adresse'] ?? null,
                ]);
                ActivityLog::log(ActivityLog::ACTION_CREATED, ActivityLog::SUBJECT_CLIENT, $doc->id, 'Client ' . $doc->name . ' créé');
                $docId = $doc->id;
                $dentisteName = $doc->name;
            } else {
                $doc = Doc::findOrFail($validated['doc_id']);
                $docId = $doc->id;
                $dentisteName = $doc->name;
            }

            $computedPrix = 0;
            $prestationNames = [];
            foreach ($teethWithPrestation as $t) {
                $prestation = Prestation::find($t['prestation_id']);
                if ($prestation) {
                    $computedPrix += (float) ($doc->getPriceForPrestation($prestation) ?? 0);
                    $prestationNames[$prestation->id] = $prestation->name;
                }
            }
            $prixDhs = (float) ($validated['prix_dhs'] ?? 0);
            if ($prixDhs <= 0) {
                $prixDhs = $computedPrix;
            }
            $typeTravail = count($prestationNames) === 1 ? reset($prestationNames) : 'Multiple prestations';
            $firstPrestationId = $teethWithPrestation[0]['prestation_id'] ?? null;

            $travail = Travail::create([
                'doc_id' => $docId,
                'prestation_id' => $firstPrestationId,
                'dentiste' => $dentisteName,
                'patient' => $validated['patient'],
                'numero_fiche' => $validated['numero_fiche'] ?? null,
                'patient_age' => $validated['patient_age'] ?? null,
                'type_travail' => $typeTravail,
                'date_entree' => $validated['date_entree'],
                'date_livraison' => $validated['date_livraison'],
                'date_essiage' => $validated['date_essiage'] ?? null,
                'prix_dhs' => $prixDhs,
                'statut' => Travail::STATUT_EN_ATTENTE,
            ]);

            foreach ($teethWithPrestation as $t) {
                TravailTooth::create([
                    'travail_id' => $travail->id,
                    'tooth_number' => $t['tooth_number'],
                    'prestation_id' => $t['prestation_id'],
                    'phase' => 1,
                    'stock_id' => null,
                    'quantity' => 1,
                ]);
            }
            ActivityLog::log(ActivityLog::ACTION_CREATED, ActivityLog::SUBJECT_TRAVAIL, $travail->id, 'Travail ' . $travail->reference . ' — ' . ($travail->patient ?: '—') . ' créé');

            return $travail;
        });

        return redirect()->route('travaux.show', $travail)->with('success', 'Travail créé avec succès.');
    }

    public function index(Request $request): View
    {
        $query = Travail::query()->with(['doc', 'prestation'])->orderByDesc('date_entree');

        // Recherche par dentiste, patient ou numéro de fiche
        if ($search = $request->filled('recherche') ? $request->get('recherche') : null) {
            $query->where(function ($q) use ($search) {
                $q->where('dentiste', 'like', '%' . $search . '%')
                    ->orWhere('patient', 'like', '%' . $search . '%')
                    ->orWhere('numero_fiche', 'like', '%' . $search . '%');
            });
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->get('statut'));
        }

        // Filtre par date (date d'entrée)
        if ($request->filled('date_debut')) {
            $query->whereDate('date_entree', '>=', $request->get('date_debut'));
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_entree', '<=', $request->get('date_fin'));
        }

        $travaux = $query->paginate(10)->withQueryString();

        return view('travaux.index', [
            'travaux' => $travaux,
            'statutLabels' => Travail::statutLabels(),
            'paiementLabels' => Travail::paiementLabels(),
            'typePaiementLabels' => TypePaiement::labels(),
        ]);
    }

    public function show(Travail $travail): View
    {
        $travail->load(['doc', 'prestation', 'teeth.stock', 'teeth.prestation']);

        return view('travaux.show', [
            'travail' => $travail,
            'statutLabels' => Travail::statutLabels(),
            'paiementLabels' => Travail::paiementLabels(),
            'typePaiementLabels' => TypePaiement::labels(),
        ]);
    }

    public function edit(Travail $travail): View
    {
        $travail->load(['doc', 'prestation', 'teeth.prestation']);

        $categories = PrestationCategory::with('prestations')->orderBy('order')->orderBy('name')->get();
        $docs = Doc::with('docPrestationPrices')->orderBy('name')->get();
        $prestationDefaultPrices = [];
        $docOverridePrices = [];
        foreach ($categories as $cat) {
            foreach ($cat->prestations as $p) {
                $prestationDefaultPrices[$p->id] = $p->price !== null ? (float) $p->price : null;
            }
        }
        foreach ($docs as $doc) {
            foreach ($doc->docPrestationPrices as $ov) {
                $docOverridePrices[$doc->id][$ov->prestation_id] = $ov->price !== null ? (float) $ov->price : null;
            }
        }

        return view('travaux.edit', [
            'travail' => $travail,
            'docs' => $docs,
            'categories' => $categories,
            'stockItems' => [],
            'prestationDefaultPrices' => $prestationDefaultPrices,
            'docOverridePrices' => $docOverridePrices,
            'statutLabels' => Travail::statutLabels(),
            'paiementLabels' => Travail::paiementLabels(),
            'typePaiementLabels' => TypePaiement::labels(),
        ]);
    }

    public function update(Request $request, Travail $travail): RedirectResponse
    {
        $isNewDoc = (bool) $request->boolean('add_new_doc');
        $rules = [
            'patient' => ['required', 'string', 'max:255'],
            'numero_fiche' => ['nullable', 'string', 'max:255', Rule::unique('travaux', 'numero_fiche')->ignore($travail->id)],
            'patient_age' => ['nullable', 'integer', 'min:1', 'max:150'],
            'date_entree' => ['required', 'date'],
            'date_livraison' => ['required', 'date', 'after_or_equal:date_entree'],
            'date_essiage' => ['nullable', 'date'],
            'prix_dhs' => ['nullable', 'numeric', 'min:0'],
            'statut' => ['required', Rule::in(array_keys(Travail::statutLabels()))],
            'paiement_statut' => ['nullable', Rule::in(array_keys(Travail::paiementLabels()))],
            'type_paiement' => [
                Rule::requiredIf(fn () => in_array($request->input('paiement_statut'), [Travail::PAIEMENT_PARTIEL, Travail::PAIEMENT_PAYE], true)),
                'nullable',
                Rule::in(TypePaiement::keys()),
            ],
        ];

        if ($isNewDoc) {
            $rules['doc_numero_registration'] = ['required', 'string', 'max:255'];
            $rules['doc_name'] = ['required', 'string', 'max:255'];
            $rules['doc_phone'] = ['nullable', 'string', 'max:255'];
            $rules['doc_email'] = ['nullable', 'email', 'max:255'];
            $rules['doc_adresse'] = ['nullable', 'string'];
        } else {
            $rules['doc_id'] = ['required', 'integer', 'exists:docs,id'];
        }

        $validated = $request->validate($rules);
        $teethData = $request->input('teeth', []);
        if (! is_array($teethData)) {
            $teethData = [];
        }

        $teethWithPrestation = [];
        foreach ($teethData as $toothNum => $data) {
            $prestationId = isset($data['prestation_id']) && $data['prestation_id'] !== '' ? (int) $data['prestation_id'] : null;
            if ($prestationId && (int) $toothNum >= 1 && (int) $toothNum <= 32) {
                $teethWithPrestation[] = ['tooth_number' => (int) $toothNum, 'prestation_id' => $prestationId];
            }
        }
        if (count($teethWithPrestation) === 0) {
            return back()->withErrors(['teeth' => 'Ajoutez au moins une dent avec une prestation.'])->withInput();
        }

        DB::transaction(function () use ($request, $validated, $isNewDoc, $teethWithPrestation, $travail) {
            $docId = null;
            $dentisteName = '';

            if ($isNewDoc) {
                $doc = Doc::create([
                    'numero_registration' => $validated['doc_numero_registration'],
                    'name' => $validated['doc_name'],
                    'phone' => $validated['doc_phone'] ?? null,
                    'email' => $validated['doc_email'] ?? null,
                    'adresse' => $validated['doc_adresse'] ?? null,
                ]);
                $docId = $doc->id;
                $dentisteName = $doc->name;
            } else {
                $doc = Doc::findOrFail($validated['doc_id']);
                $docId = $doc->id;
                $dentisteName = $doc->name;
            }

            $chartChanged = $this->syncTeethPreservingPhases($travail, $teethWithPrestation);

            $prixDhs = (float) $travail->prix_dhs;
            $typeTravail = $travail->type_travail;
            $firstPrestationId = $travail->prestation_id;
            if ($chartChanged) {
                $recomputed = $this->prixFromAllTeeth($travail, $doc);
                $submittedPrix = (float) ($validated['prix_dhs'] ?? 0);
                $collapsedSum = 0.0;
                foreach ($teethWithPrestation as $t) {
                    $prestation = Prestation::find($t['prestation_id']);
                    if ($prestation) {
                        $collapsedSum += (float) ($doc->getPriceForPrestation($prestation) ?? 0);
                    }
                }
                // The edit screen prices only the visible tooth, so a matching total is not a manual override.
                $prixDhs = ($submittedPrix > 0 && abs($submittedPrix - $collapsedSum) > 0.05)
                    ? $submittedPrix
                    : $recomputed;
                $names = $travail->teeth()->with('prestation')->get()
                    ->map(fn ($row) => $row->prestation?->name)
                    ->filter()
                    ->unique()
                    ->values();
                $typeTravail = $names->count() === 1 ? $names->first() : 'Multiple prestations';
                $firstPrestationId = $travail->teeth()->orderBy('phase')->orderBy('id')->value('prestation_id');
            }

            $updateData = [
                'doc_id' => $docId,
                'prestation_id' => $firstPrestationId,
                'dentiste' => $dentisteName,
                'patient' => $validated['patient'],
                'numero_fiche' => $validated['numero_fiche'] ?? null,
                'patient_age' => $validated['patient_age'] ?? null,
                'type_travail' => $typeTravail,
                'date_entree' => $validated['date_entree'],
                'date_livraison' => $validated['date_livraison'],
                'date_essiage' => $validated['date_essiage'] ?? null,
                'prix_dhs' => $prixDhs,
                'statut' => $validated['statut'],
                'date_fait' => Travail::dateFaitForStatutChange(
                    (string) $travail->statut,
                    $travail->date_fait,
                    (string) $validated['statut']
                ),
            ];

            if (array_key_exists('paiement_statut', $validated) && $validated['paiement_statut'] !== null) {
                $paiementStatut = $validated['paiement_statut'];
                $updateData['paiement_statut'] = $paiementStatut;
                $updateData['type_paiement'] = in_array($paiementStatut, [Travail::PAIEMENT_PARTIEL, Travail::PAIEMENT_PAYE], true)
                    ? ($validated['type_paiement'] ?? null)
                    : null;
            }

            if (! $chartChanged) {
                unset($updateData['prix_dhs']);
            } elseif ($travail->montant_comptabilise !== null && abs((float) $travail->montant_comptabilise - (float) $travail->prix_dhs) < 0.05) {
                $updateData['montant_comptabilise'] = $prixDhs;
            }

            $travail->update($updateData);

            // Keep stored facture in sync: if this travail has exactly one facture with only this travail, update its date.
            $travail->load('factures');
            if ($travail->factures->count() === 1) {
                $f = $travail->factures->first();
                $f->loadCount('travaux');
                if ($f->travaux_count === 1) {
                    $f->update(['date_facture' => $travail->date_entree]);
                }
            }
        });
        ActivityLog::log(ActivityLog::ACTION_UPDATED, ActivityLog::SUBJECT_TRAVAIL, $travail->id, 'Travail ' . $travail->reference . ' modifié');

        return redirect()->route('travaux.show', $travail)->with('success', 'Travail mis à jour.');
    }

    /** Add a new phase to this travail (same teeth with different prestation/material). */
    public function addPhaseForm(Travail $travail): View
    {
        $travail->load('doc');
        $categories = PrestationCategory::with('prestations')->orderBy('order')->orderBy('name')->get();
        $doc = $travail->doc;
        $prestationDefaultPrices = [];
        $docOverridePrices = [];
        foreach ($categories as $cat) {
            foreach ($cat->prestations as $p) {
                $prestationDefaultPrices[$p->id] = $p->price !== null ? (float) $p->price : null;
            }
        }
        if ($doc) {
            foreach ($doc->docPrestationPrices as $ov) {
                $docOverridePrices[$doc->id][$ov->prestation_id] = $ov->price !== null ? (float) $ov->price : null;
            }
        }
        $prestationsForJs = $categories->flatMap(fn ($cat) => $cat->prestations->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'categoryName' => $cat->name,
        ]))->values()->all();

        return view('travaux.add-phase', [
            'travail' => $travail,
            'categories' => $categories,
            'prestationDefaultPrices' => $prestationDefaultPrices,
            'docOverridePrices' => $docOverridePrices,
            'prestationsForJs' => $prestationsForJs,
        ]);
    }

    public function storePhase(Request $request, Travail $travail): RedirectResponse
    {
        $request->validate([
            'prix_dhs' => ['nullable', 'numeric', 'min:0'],
        ]);
        $teethData = $request->input('teeth', []);
        if (! is_array($teethData)) {
            $teethData = [];
        }
        $teethWithPrestation = [];
        foreach ($teethData as $toothNum => $data) {
            $prestationId = isset($data['prestation_id']) && $data['prestation_id'] !== '' ? (int) $data['prestation_id'] : null;
            if ($prestationId && (int) $toothNum >= 1 && (int) $toothNum <= 32) {
                $teethWithPrestation[] = ['tooth_number' => (int) $toothNum, 'prestation_id' => $prestationId];
            }
        }
        if (count($teethWithPrestation) === 0) {
            return back()->withErrors(['teeth' => 'Ajoutez au moins une dent avec une prestation.'])->withInput();
        }

        $doc = $travail->doc;
        $newPhaseTotal = 0;
        foreach ($teethWithPrestation as $t) {
            $prestation = Prestation::find($t['prestation_id']);
            if ($prestation && $doc) {
                $newPhaseTotal += (float) ($doc->getPriceForPrestation($prestation) ?? 0);
            }
        }
        $submittedPrix = (float) ($request->input('prix_dhs') ?? 0);
        if ($submittedPrix > 0) {
            $newPhaseTotal = $submittedPrix;
        }

        $nextPhase = (int) $travail->teeth()->max('phase') + 1;

        DB::transaction(function () use ($travail, $teethWithPrestation, $nextPhase, $newPhaseTotal) {
            foreach ($teethWithPrestation as $t) {
                TravailTooth::create([
                    'travail_id' => $travail->id,
                    'tooth_number' => $t['tooth_number'],
                    'prestation_id' => $t['prestation_id'],
                    'phase' => $nextPhase,
                    'stock_id' => null,
                    'quantity' => 1,
                ]);
            }
            $travail->increment('prix_dhs', $newPhaseTotal);
            // Keep billable amount in sync so situations include every added phase/case.
            if ($travail->montant_comptabilise !== null) {
                $travail->increment('montant_comptabilise', $newPhaseTotal);
            }
            // Sync head facture pivot so it reflects full amount (all phases) for regrouper/PDF
            $travail->refresh();
            $headFacture = $travail->factures()->where('comptabilise_dans_restant', false)->first();
            if ($headFacture) {
                $headFacture->travaux()->updateExistingPivot($travail->id, [
                    'prix_comptabilise' => $travail->montant_comptabilise_cap,
                ]);
                $headFacture->update([
                    'montant_comptabilise' => $headFacture->travaux()->sum('facture_travail.prix_comptabilise'),
                ]);
            }
        });
        ActivityLog::log(ActivityLog::ACTION_UPDATED, ActivityLog::SUBJECT_TRAVAIL, $travail->id, 'Phase ' . $nextPhase . ' ajoutée au travail ' . $travail->reference);

        return redirect()->route('travaux.show', $travail)->with('success', 'Phase ajoutée au travail.');
    }

    public function updateStatut(Request $request, Travail $travail): RedirectResponse
    {
        $request->validate([
            'statut' => ['required', Rule::in(array_keys(Travail::statutLabels()))],
        ]);
        $newStatut = (string) $request->input('statut');
        $travail->update([
            'statut' => $newStatut,
            'date_fait' => Travail::dateFaitForStatutChange(
                (string) $travail->statut,
                $travail->date_fait,
                $newStatut
            ),
        ]);
        ActivityLog::log(ActivityLog::ACTION_UPDATED, ActivityLog::SUBJECT_TRAVAIL, $travail->id, 'Statut travail ' . $travail->reference . ' modifié');
        return back()->with('success', 'Statut mis à jour.');
    }

    public function updatePaiement(Request $request, Travail $travail): RedirectResponse
    {
        $validated = $request->validate([
            'paiement_statut' => ['required', Rule::in(array_keys(Travail::paiementLabels()))],
            'type_paiement' => [
                Rule::requiredIf(fn () => in_array($request->input('paiement_statut'), [Travail::PAIEMENT_PARTIEL, Travail::PAIEMENT_PAYE], true)),
                'nullable',
                Rule::in(TypePaiement::keys()),
            ],
        ]);

        $paiementStatut = $validated['paiement_statut'];
        $travail->update([
            'paiement_statut' => $paiementStatut,
            'type_paiement' => in_array($paiementStatut, [Travail::PAIEMENT_PARTIEL, Travail::PAIEMENT_PAYE], true)
                ? ($validated['type_paiement'] ?? null)
                : null,
        ]);

        ActivityLog::log(ActivityLog::ACTION_UPDATED, ActivityLog::SUBJECT_TRAVAIL, $travail->id, 'Paiement travail ' . $travail->reference . ' modifié');

        return back()->with('success', 'Paiement mis à jour.');
    }

    public function updateMontantComptabilise(Request $request, Travail $travail): RedirectResponse
    {
        $max = (float) $travail->prix_dhs;
        $request->validate([
            'montant_comptabilise' => ['required', 'numeric', 'min:0', 'max:' . $max],
        ]);
        $travail->update(['montant_comptabilise' => (float) $request->input('montant_comptabilise')]);
        // Sync head facture pivot so regrouper/PDF use the comptabilised amount, not full prix_dhs
        $headFacture = $travail->factures()->where('comptabilise_dans_restant', false)->first();
        if ($headFacture) {
            $headFacture->travaux()->updateExistingPivot($travail->id, [
                'prix_comptabilise' => $travail->montant_comptabilise_cap,
            ]);
            $headFacture->update([
                'montant_comptabilise' => $headFacture->travaux()->sum('facture_travail.prix_comptabilise'),
            ]);
        }
        ActivityLog::log(ActivityLog::ACTION_UPDATED, ActivityLog::SUBJECT_TRAVAIL, $travail->id, 'Montant comptabilisé travail ' . $travail->reference);
        return redirect()->route('factures.index')->with('success', 'Montant comptabilisé enregistré. Le reste est en page internes.');
    }

    /**
     * The edit chart keeps one prestation per tooth (the latest phase).
     * Saving a fiche or a date must not delete the other phases.
     * Returns true when the chart itself changed.
     */
    private function syncTeethPreservingPhases(Travail $travail, array $teethWithPrestation): bool
    {
        $existing = $travail->teeth()->orderBy('id')->get();
        $lastByTooth = [];
        foreach ($existing as $row) {
            $lastByTooth[(int) $row->tooth_number] = $row;
        }

        $submitted = [];
        foreach ($teethWithPrestation as $t) {
            $submitted[(int) $t['tooth_number']] = (int) $t['prestation_id'];
        }
        ksort($submitted);

        $collapsed = [];
        foreach ($lastByTooth as $tooth => $row) {
            $collapsed[(int) $tooth] = (int) $row->prestation_id;
        }
        ksort($collapsed);

        if ($collapsed === $submitted) {
            return false;
        }

        $removed = $existing->filter(fn ($row) => ! array_key_exists((int) $row->tooth_number, $submitted));
        foreach ($removed as $row) {
            if ($row->stock_id !== null) {
                Stock::find($row->stock_id)?->increment('quantity', $row->quantity);
            }
            $row->delete();
        }

        foreach ($submitted as $tooth => $prestationId) {
            if (! isset($lastByTooth[$tooth])) {
                TravailTooth::create([
                    'travail_id' => $travail->id,
                    'tooth_number' => $tooth,
                    'prestation_id' => $prestationId,
                    'phase' => 1,
                    'stock_id' => null,
                    'quantity' => 1,
                ]);
                continue;
            }
            $visible = $lastByTooth[$tooth];
            if ((int) $visible->prestation_id !== $prestationId) {
                $visible->update(['prestation_id' => $prestationId]);
            }
        }

        return true;
    }

    private function prixFromAllTeeth(Travail $travail, Doc $doc): float
    {
        $sum = 0.0;
        foreach ($travail->teeth()->with('prestation')->get() as $tooth) {
            if ($tooth->prestation) {
                $sum += (float) ($doc->getPriceForPrestation($tooth->prestation) ?? 0);
            }
        }

        return round($sum, 2);
    }

    public function destroy(Travail $travail): RedirectResponse
    {
        if (auth()->user()->hasRole('assistante')) {
            abort(403, 'Vous n\'êtes pas autorisé à supprimer un travail.');
        }
        $travail->load('teeth');
        DB::transaction(function () use ($travail) {
            foreach ($travail->teeth as $tt) {
                if ($tt->stock_id !== null) {
                    Stock::find($tt->stock_id)?->increment('quantity', $tt->quantity);
                }
            }
            $travail->teeth()->delete();
            $ref = $travail->reference;
            $travail->delete();
            ActivityLog::log(ActivityLog::ACTION_DELETED, ActivityLog::SUBJECT_TRAVAIL, null, 'Travail ' . $ref . ' supprimé');
        });
        return redirect()->route('travaux.index')->with('success', 'Travail supprimé.');
    }
}
