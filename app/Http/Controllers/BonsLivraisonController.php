<?php

namespace App\Http\Controllers;

use App\Models\BonLivraison;
use App\Models\Doc;
use App\Models\Setting;
use App\Models\Travail;
use App\Support\TypePaiement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BonsLivraisonController extends Controller
{
    private const OURS_TO_FDI = [
        1 => 11, 2 => 12, 3 => 13, 4 => 14, 5 => 15, 6 => 16, 7 => 17, 8 => 18,
        9 => 21, 10 => 22, 11 => 23, 12 => 24, 13 => 25, 14 => 26, 15 => 27, 16 => 28,
        17 => 31, 18 => 32, 19 => 33, 20 => 34, 21 => 35, 22 => 36, 23 => 37, 24 => 38,
        25 => 41, 26 => 42, 27 => 43, 28 => 44, 29 => 45, 30 => 46, 31 => 47, 32 => 48,
    ];

    public function index(Request $request): View
    {
        $docs = Doc::orderBy('name')->get();
        $query = BonLivraison::query()->with(['doc', 'travail'])->orderByDesc('date_livraison')->orderByDesc('id');

        if ($request->filled('doc_id')) {
            $query->where('doc_id', $request->integer('doc_id'));
        }
        if ($request->filled('paiement_statut')) {
            $query->where('paiement_statut', $request->get('paiement_statut'));
        }
        if ($request->filled('q')) {
            $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->get('q')) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('numero', 'like', $term)
                    ->orWhere('dentiste_nom', 'like', $term)
                    ->orWhere('patient_nom', 'like', $term)
                    ->orWhere('numero_fiche', 'like', $term)
                    ->orWhere('details_travail', 'like', $term);
            });
        }

        return view('bons-livraison.index', [
            'docs' => $docs,
            'bons' => $query->paginate(20)->withQueryString(),
            'paiementLabels' => BonLivraison::paiementLabels(),
            'typePaiementLabels' => TypePaiement::labels(),
        ]);
    }

    public function create(Request $request): View
    {
        $docs = Doc::orderBy('name')->get();
        $travaux = Travail::with('doc')->orderByDesc('date_entree')->limit(200)->get();
        $prefix = trim(Setting::get('bon_livraison_prefix', 'BL')) ?: 'BL';
        $nextNum = max(1, (int) Setting::get('bon_livraison_prochain_numero', '1'));
        $previewNumero = $prefix . '-' . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);

        $prefill = [
            'date_livraison' => now()->toDateString(),
            'doc_id' => null,
            'travail_id' => null,
            'dentiste_nom' => '',
            'patient_nom' => '',
            'numero_fiche' => '',
            'reference_travail' => '',
            'details_travail' => '',
            'dents' => '',
            'montant' => null,
            'paiement_statut' => BonLivraison::PAIEMENT_NON_PAYE,
            'type_paiement' => null,
            'observations' => '',
            'recu_par' => '',
        ];

        if ($request->filled('travail_id')) {
            $travail = Travail::with(['doc', 'teeth.prestation'])->find($request->integer('travail_id'));
            if ($travail) {
                $prefill = array_merge($prefill, $this->prefillFromTravail($travail));
            }
        }

        return view('bons-livraison.create', [
            'docs' => $docs,
            'travaux' => $travaux,
            'previewNumero' => $previewNumero,
            'prefill' => $prefill,
            'paiementLabels' => BonLivraison::paiementLabels(),
            'typePaiementLabels' => TypePaiement::labels(),
        ]);
    }

    public function prefillTravail(Travail $travail): \Illuminate\Http\JsonResponse
    {
        $travail->load(['doc', 'teeth.prestation']);

        return response()->json($this->prefillFromTravail($travail));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBon($request);
        $validated = $this->normalizePaiementFields($validated);

        $bon = Cache::lock('bon_livraison_numero', 10)->block(10, function () use ($validated) {
            $prefix = trim(Setting::get('bon_livraison_prefix', 'BL')) ?: 'BL';
            $nextNum = max(1, (int) Setting::get('bon_livraison_prochain_numero', '1'));
            $numero = $prefix . '-' . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);

            $bon = BonLivraison::create(array_merge($validated, [
                'numero' => $numero,
                'montant' => isset($validated['montant']) ? (float) $validated['montant'] : null,
            ]));

            Setting::set('bon_livraison_prochain_numero', (string) ($nextNum + 1));

            return $bon;
        });

        return redirect()->route('bons-livraison.edit', $bon)->with('success', 'Bon de livraison enregistré.');
    }

    public function edit(BonLivraison $bonLivraison): View
    {
        $docs = Doc::orderBy('name')->get();
        $travaux = Travail::with('doc')->orderByDesc('date_entree')->limit(200)->get();

        return view('bons-livraison.edit', [
            'bon' => $bonLivraison,
            'docs' => $docs,
            'travaux' => $travaux,
            'paiementLabels' => BonLivraison::paiementLabels(),
            'typePaiementLabels' => TypePaiement::labels(),
        ]);
    }

    public function update(Request $request, BonLivraison $bonLivraison): RedirectResponse
    {
        $validated = $this->normalizePaiementFields($this->validateBon($request));
        $bonLivraison->update(array_merge($validated, [
            'montant' => array_key_exists('montant', $validated) && $validated['montant'] !== null && $validated['montant'] !== ''
                ? (float) $validated['montant']
                : null,
        ]));

        return redirect()->route('bons-livraison.edit', $bonLivraison)->with('success', 'Bon de livraison mis à jour.');
    }

    public function destroy(BonLivraison $bonLivraison): RedirectResponse
    {
        $bonLivraison->delete();

        return redirect()->route('bons-livraison.index')->with('success', 'Bon de livraison supprimé.');
    }

    public function pdf(BonLivraison $bonLivraison): Response
    {
        $bonLivraison->load(['doc', 'travail']);
        $labName = Setting::get('lab_name', 'MD Smile');
        $logoPath = public_path('images/mdsmile-logo.png');
        $devise = Setting::get('facture_devise', 'DHS');

        $pdf = Pdf::loadView('bons-livraison.pdf', [
            'bon' => $bonLivraison,
            'labName' => $labName,
            'logoPath' => $logoPath,
            'labAdresse' => Setting::get('lab_adresse'),
            'labPhone' => Setting::get('lab_phone'),
            'labEmail' => Setting::get('lab_email'),
            'devise' => $devise,
            'paiementLabel' => $bonLivraison->paiement_label,
            'typePaiementLabel' => TypePaiement::label($bonLivraison->type_paiement),
        ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->stream('bon-livraison-' . $bonLivraison->numero . '.pdf');
    }

    private function validateBon(Request $request): array
    {
        return $request->validate([
            'date_livraison' => ['required', 'date'],
            'doc_id' => ['nullable', 'integer', 'exists:docs,id'],
            'travail_id' => ['nullable', 'integer', 'exists:travaux,id'],
            'dentiste_nom' => ['required', 'string', 'max:255'],
            'patient_nom' => ['required', 'string', 'max:255'],
            'numero_fiche' => ['nullable', 'string', 'max:255'],
            'reference_travail' => ['nullable', 'string', 'max:64'],
            'details_travail' => ['required', 'string', 'max:5000'],
            'dents' => ['nullable', 'string', 'max:500'],
            'montant' => ['nullable', 'numeric', 'min:0'],
            'paiement_statut' => ['required', Rule::in(array_keys(BonLivraison::paiementLabels()))],
            'type_paiement' => [
                Rule::requiredIf(fn () => in_array($request->input('paiement_statut'), [BonLivraison::PAIEMENT_PARTIEL, BonLivraison::PAIEMENT_PAYE], true)),
                'nullable',
                Rule::in(TypePaiement::keys()),
            ],
            'observations' => ['nullable', 'string', 'max:5000'],
            'recu_par' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /** @param  array<string, mixed>  $validated */
    private function normalizePaiementFields(array $validated): array
    {
        $validated['type_paiement'] = $validated['type_paiement'] ?? null;

        return $validated;
    }

    /** @return array<string, mixed> */
    private function prefillFromTravail(Travail $travail): array
    {
        $dents = $travail->teeth->pluck('tooth_number')
            ->map(fn ($n) => self::OURS_TO_FDI[$n] ?? $n)
            ->sort()
            ->values()
            ->implode(', ');

        $details = [];
        $phases = $travail->teeth->groupBy('phase')->sortKeys();
        if ($phases->isEmpty()) {
            $details[] = $travail->type_travail_display;
        } else {
            foreach ($phases as $phaseNum => $teethInPhase) {
                $byPrest = $teethInPhase->groupBy('prestation_id');
                foreach ($byPrest as $items) {
                    $first = $items->first();
                    $name = $first->prestation?->name ?? $travail->type_travail_display;
                    $toothStr = $items->pluck('tooth_number')
                        ->map(fn ($n) => self::OURS_TO_FDI[$n] ?? $n)
                        ->sort()->values()->implode(', ');
                    $line = $name . ($toothStr !== '' ? ' — Dents ' . $toothStr : '');
                    if ((int) $phaseNum > 1) {
                        $line = 'Phase ' . $phaseNum . ' — ' . $line;
                    }
                    $details[] = $line;
                }
            }
        }

        $cap = (float) $travail->montant_comptabilise_cap;
        $paye = (float) $travail->montantPayeFactures();
        if ($travail->paiement_statut) {
            $paiement = $travail->paiement_statut;
        } elseif ($cap <= 0 || $paye <= 0) {
            $paiement = BonLivraison::PAIEMENT_NON_PAYE;
        } elseif ($paye + 0.005 >= $cap) {
            $paiement = BonLivraison::PAIEMENT_PAYE;
        } else {
            $paiement = BonLivraison::PAIEMENT_PARTIEL;
        }

        return [
            'date_livraison' => ($travail->date_livraison?->toDateString()) ?: now()->toDateString(),
            'doc_id' => $travail->doc_id,
            'travail_id' => $travail->id,
            'dentiste_nom' => $travail->doc?->name ?? ($travail->dentiste ?: ''),
            'patient_nom' => $travail->patient ?: '',
            'numero_fiche' => $travail->numero_fiche,
            'reference_travail' => $travail->reference,
            'details_travail' => implode("\n", $details),
            'dents' => $dents,
            'montant' => $cap,
            'paiement_statut' => $paiement,
            'type_paiement' => in_array($paiement, [BonLivraison::PAIEMENT_PARTIEL, BonLivraison::PAIEMENT_PAYE], true)
                ? $travail->type_paiement
                : null,
        ];
    }
}
