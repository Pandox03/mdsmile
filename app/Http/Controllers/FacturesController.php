<?php

namespace App\Http\Controllers;

use App\Helpers\NumberToFrench;
use App\Models\ActivityLog;
use App\Models\Doc;
use App\Models\Facture;
use App\Models\Setting;
use App\Models\Travail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class FacturesController extends Controller
{
    public function index(Request $request): View
    {
        $docs = Doc::orderBy('name')->get();

        // Main factures list (no longer tied to travaux)
        $facturesQuery = Facture::query()->with('doc')->orderByDesc('date_facture');

        if ($request->filled('doc_id')) {
            $facturesQuery->where('doc_id', $request->get('doc_id'));
        }
        if ($request->filled('recherche_client')) {
            $term = $request->get('recherche_client');
            $facturesQuery->whereHas('doc', fn ($q) => $q->where('name', 'like', '%' . $term . '%'));
        }
        if ($request->filled('date_du')) {
            $facturesQuery->whereDate('date_facture', '>=', $request->get('date_du'));
        }
        if ($request->filled('date_au')) {
            $facturesQuery->whereDate('date_facture', '<=', $request->get('date_au'));
        }
        $factures = $facturesQuery->get();

        $facturesRegroupement = collect();
        if ($request->filled('doc_id') && $request->filled('date_du') && $request->filled('date_au')) {
            $dateDu = \Carbon\Carbon::parse($request->get('date_du'))->startOfDay()->format('Y-m-d');
            $dateAu = \Carbon\Carbon::parse($request->get('date_au'))->endOfDay()->format('Y-m-d');
            // Only head factures (the one with the total, not partial/sous factures); exclude partials even if paid
            $facturesRegroupement = Facture::where('doc_id', $request->get('doc_id'))
                ->where('comptabilise_dans_restant', false)
                ->whereHas('travaux', function ($q) use ($dateDu, $dateAu) {
                    $q->whereDate('date_entree', '>=', $dateDu)->whereDate('date_entree', '<=', $dateAu);
                })
                ->with(['doc', 'travaux.factures'])
                ->orderBy('date_facture')
                ->get();

            // For each head facture, set reste_a_facturer = sum of (cap - déjà facturé via partiels) for its travaux
            $facturesRegroupement->each(function ($f) {
                $reste = 0;
                foreach ($f->travaux as $t) {
                    $cap = (float) ($t->montant_comptabilise ?? $t->prix_dhs);
                    $deja = (float) $t->factures->sum(fn ($ff) => ($ff->comptabilise_dans_restant ?? true) ? (float) ($ff->pivot->prix_comptabilise ?? 0) : 0);
                    $reste += max(0, $cap - $deja);
                }
                $f->reste_a_facturer = $reste;
            });
        }

        return view('factures.index', [
            'docs' => $docs,
            'factures' => $factures,
            'facturesRegroupement' => $facturesRegroupement,
        ]);
    }

    public function create(Request $request): View
    {
        $docs = Doc::orderBy('name')->get();
        $docId = $request->get('doc_id');

        return view('factures.create', [
            'docs' => $docs,
            'selectedDocId' => $docId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'doc_id' => ['required', 'integer', 'exists:docs,id'],
            'date_facture' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'min:0.01'],
        ]);

        $doc = Doc::findOrFail($validated['doc_id']);
        $montant = (float) $validated['montant'];

        $facture = Cache::lock('facture_numero', 10)->block(10, function () use ($validated, $doc, $montant) {
            $prefix = trim(Setting::get('facture_prefix', 'FAC')) ?: 'FAC';
            $nextNum = max(1, (int) Setting::get('facture_prochain_numero', '1'));
            $numero = $prefix . '-' . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);

            $facture = Facture::create([
                'doc_id' => $doc->id,
                'numero' => $numero,
                'date_facture' => $validated['date_facture'],
                'montant_comptabilise' => $montant,
            ]);

            Setting::set('facture_prochain_numero', (string) ($nextNum + 1));
            return $facture;
        });

        ActivityLog::log(ActivityLog::ACTION_CREATED, ActivityLog::SUBJECT_FACTURE, $facture->id, 'Facture ' . $facture->numero . ' créée (montant manuel)');

        return redirect()->route('factures.show', $facture)->with('success', 'Facture créée.');
    }

    /**
     * Create one consolidated facture from selected factures (same doc, move all travaux to the new facture).
     */
    public function regrouper(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'facture_ids' => ['required', 'array', 'min:1'],
            'facture_ids.*' => ['integer', 'exists:factures,id'],
            'date_facture' => ['required', 'date'],
        ]);

        $factures = Facture::whereIn('id', $validated['facture_ids'])->with('travaux')->get();
        if ($factures->isEmpty()) {
            return back()->withErrors(['facture_ids' => 'Aucune facture valide sélectionnée.'])->withInput();
        }

        $docId = $factures->first()->doc_id;
        if ($factures->contains(fn ($f) => $f->doc_id !== $docId)) {
            return back()->withErrors(['facture_ids' => 'Toutes les factures doivent concerner le même client.'])->withInput();
        }

        $doc = Doc::findOrFail($docId);

        $facture = Cache::lock('facture_numero', 10)->block(10, function () use ($validated, $doc) {
            $prefix = trim(Setting::get('facture_prefix', 'FAC')) ?: 'FAC';
            $nextNum = max(1, (int) Setting::get('facture_prochain_numero', '1'));
            $numero = $prefix . '-' . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);

            $facture = Facture::create([
                'doc_id' => $doc->id,
                'numero' => $numero,
                'date_facture' => $validated['date_facture'],
                'montant_comptabilise' => null,
            ]);

            Setting::set('facture_prochain_numero', (string) ($nextNum + 1));
            return $facture;
        });

        // Group by travail_id and sum pivot amounts (same travail on multiple factures = one attach with sum).
        // Cap each travail at its comptabilised cap so we never invoice more than the edited "montant comptabilisé".
        $travailAmounts = [];
        foreach ($factures as $f) {
            foreach ($f->travaux as $t) {
                $tid = (int) $t->id;
                $pc = (float) ($t->pivot->prix_comptabilise ?? 0);
                $travailAmounts[$tid] = ($travailAmounts[$tid] ?? 0) + $pc;
            }
        }
        $travauxCaps = Travail::whereIn('id', array_keys($travailAmounts))->get()->keyBy('id');
        $totalFacture = 0;
        foreach ($travailAmounts as $tid => $summed) {
            $cap = (float) ($travauxCaps->get($tid)?->montant_comptabilise_cap ?? $summed);
            $pc = min($summed, $cap);
            $facture->travaux()->attach($tid, ['prix_comptabilise' => $pc]);
            $totalFacture += $pc;
        }
        foreach ($factures as $f) {
            $f->travaux()->detach();
        }
        $facture->update(['montant_comptabilise' => $totalFacture]);

        ActivityLog::log(ActivityLog::ACTION_CREATED, ActivityLog::SUBJECT_FACTURE, $facture->id, 'Facture regroupée ' . $facture->numero . ' créée à partir de ' . $factures->count() . ' facture(s).');

        return redirect()->route('factures.show', $facture)->with('success', 'Facture regroupée créée.');
    }

    public function show(Facture $facture): View
    {
        $facture->load(['doc', 'travaux']);

        return view('factures.show', [
            'facture' => $facture,
        ]);
    }

    public function updateMontantComptabilise(Request $request, Facture $facture): RedirectResponse
    {
        $facture->load('travaux');
        $total = $facture->total_facture;
        $request->validate([
            'montant_comptabilise' => ['required', 'numeric', 'min:0', 'max:' . (float) $total],
        ]);
        $facture->update(['montant_comptabilise' => (float) $request->get('montant_comptabilise')]);
        ActivityLog::log(ActivityLog::ACTION_UPDATED, ActivityLog::SUBJECT_FACTURE, $facture->id, 'Montant comptabilisé facture ' . $facture->numero);
        return redirect()->route('factures.show', $facture)->with('success', 'Montant comptabilisé mis à jour.');
    }

    public function edit(Facture $facture): View
    {
        $docs = Doc::orderBy('name')->get();

        return view('factures.edit', [
            'facture' => $facture,
            'docs' => $docs,
        ]);
    }

    public function update(Request $request, Facture $facture): RedirectResponse
    {
        $validated = $request->validate([
            'doc_id' => ['required', 'integer', 'exists:docs,id'],
            'date_facture' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'min:0.01'],
        ]);

        $facture->update([
            'doc_id' => $validated['doc_id'],
            'date_facture' => $validated['date_facture'],
            'montant_comptabilise' => (float) $validated['montant'],
        ]);

        ActivityLog::log(ActivityLog::ACTION_UPDATED, ActivityLog::SUBJECT_FACTURE, $facture->id, 'Facture ' . $facture->numero . ' mise à jour');

        return redirect()->route('factures.show', $facture)->with('success', 'Facture mise à jour.');
    }

    public function destroy(Facture $facture): RedirectResponse
    {
        $numero = $facture->numero;
        $facture->travaux()->detach();
        $facture->delete();

        ActivityLog::log(ActivityLog::ACTION_DELETED, ActivityLog::SUBJECT_FACTURE, $facture->id, 'Facture ' . $numero . ' supprimée');

        return redirect()->route('factures.index')->with('success', 'Facture supprimée.');
    }

    public function pdf(Request $request, Facture $facture)
    {
        $facture->load(['doc', 'travaux.factures']);
        $split = $request->get('split');
        $splitMontants = [];
        if (is_string($split) && trim($split) !== '') {
            $splitMontants = array_values(array_filter(array_map('floatval', explode(',', $split))));
        }

        $labName = Setting::get('lab_name', 'MD Smile');
        $logoPath = public_path('images/mdsmile-logo.png');
        $labAdresse = Setting::get('lab_adresse');
        $labPhone = Setting::get('lab_phone');
        $labEmail = Setting::get('lab_email');
        $labSiegeSocial = Setting::get('lab_siege_social', $labAdresse);
        $labIce = Setting::get('lab_ice');
        $labTp = Setting::get('lab_tp');
        $labIf = Setting::get('lab_if');
        $devise = Setting::get('facture_devise', 'DHS');
        $tvaRate = (float) Setting::get('facture_tva_rate', '20');

        // Head facture (comptabilise_dans_restant = false): show only the "reste" (remaining amount after partiels)
        $lignesReste = [];
        if ($facture->comptabilise_dans_restant === false) {
            foreach ($facture->travaux as $t) {
                $cap = (float) ($t->montant_comptabilise ?? $t->prix_dhs);
                $deja = (float) $t->factures->sum(fn ($ff) => ($ff->comptabilise_dans_restant ?? true) ? (float) ($ff->pivot->prix_comptabilise ?? 0) : 0);
                $reste = max(0, $cap - $deja);
                $lignesReste[] = ['label' => $t->type_travail, 'montant' => $reste];
            }
            $totalHT = array_sum(array_column($lignesReste, 'montant'));
        } else {
            // Factures ventilées ou sans détail : une seule ligne libellée dans le PDF (voir factures/pdf.blade.php).
            $lignesReste = [];
            $totalHT = (float) $facture->montant_comptabilise_affiche;
        }

        // Stored total is TTC: HT = TTC / (1 + TVA/100), TVA = TTC - HT
        $totalTTC = round($totalHT, 2);
        $divisor = 1 + ($tvaRate / 100);
        $totalHT = $divisor > 0 ? round($totalTTC / $divisor, 2) : $totalTTC;
        $tvaAmount = round($totalTTC - $totalHT, 2);
        $montantEnLettres = NumberToFrench::toLetters($totalTTC, 'dirhams');

        $pdf = Pdf::loadView('factures.pdf', [
            'facture' => $facture,
            'lignesReste' => $lignesReste ?? [],
            'splitMontants' => $splitMontants ?? [],
            'logoPath' => $logoPath,
            'labName' => $labName,
            'labAdresse' => $labAdresse,
            'labPhone' => $labPhone,
            'labEmail' => $labEmail,
            'labSiegeSocial' => $labSiegeSocial,
            'labIce' => $labIce,
            'labTp' => $labTp,
            'labIf' => $labIf,
            'devise' => $devise,
            'tvaRate' => $tvaRate,
            'totalHT' => $totalHT,
            'tvaAmount' => $tvaAmount,
            'totalTTC' => $totalTTC,
            'montantEnLettres' => $montantEnLettres,
        ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->stream('facture-' . $facture->numero . '.pdf');
    }

    /**
     * Détails internes : par travail (montant total, comptabilisé, non comptabilisé).
     */
    public function internesIndex(Request $request): View
    {
        $query = Travail::query()->with('doc')->orderByDesc('date_entree');

        if ($request->filled('doc_id')) {
            $query->where('doc_id', $request->get('doc_id'));
        }
        if ($request->filled('recherche')) {
            $term = $request->get('recherche');
            $query->where(function ($q) use ($term) {
                $q->where('patient', 'like', '%' . $term . '%')
                    ->orWhere('type_travail', 'like', '%' . $term . '%');
                $idFromRef = preg_replace('/^TR-0*/i', '', $term);
                if ($idFromRef !== '' && ctype_digit($idFromRef)) {
                    $q->orWhere('id', (int) $idFromRef);
                } elseif (ctype_digit($term)) {
                    $q->orWhere('id', (int) $term);
                }
            });
        }

        $travaux = $query->paginate(15)->withQueryString();
        $docs = Doc::orderBy('name')->get();

        return view('factures.internes-index', [
            'travaux' => $travaux,
            'docs' => $docs,
        ]);
    }

    /**
     * Détail d'une facture : montants non comptabilisés.
     */
    public function internes(Facture $facture): View
    {
        $facture->load(['doc', 'travaux']);

        return view('factures.internes', ['facture' => $facture]);
    }
}
