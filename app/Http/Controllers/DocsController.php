<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Doc;
use App\Models\DocPrestationPrice;
use App\Models\DocSituationEncaissement;
use App\Models\Prestation;
use App\Models\PrestationCategory;
use App\Models\Setting;
use App\Models\Travail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocsController extends Controller
{
    public function create(): View
    {
        return view('clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'numero_registration' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['nullable', 'string'],
        ]);

        $doc = Doc::create($validated);
        ActivityLog::log(ActivityLog::ACTION_CREATED, ActivityLog::SUBJECT_CLIENT, $doc->id, 'Client ' . $doc->name . ' créé');

        return redirect()->route('clients.index')->with('success', 'Client créé avec succès.');
    }

    public function index(Request $request): View
    {
        $query = Doc::query()->withCount('travaux')->orderBy('name');

        if ($request->filled('recherche')) {
            $term = $request->get('recherche');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('numero_registration', 'like', '%' . $term . '%')
                    ->orWhere('phone', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%')
                    ->orWhere('adresse', 'like', '%' . $term . '%');
            });
        }

        $docs = $query->paginate(15)->withQueryString();

        return view('clients.index', [
            'docs' => $docs,
        ]);
    }

    public function show(Doc $doc): View
    {
        $doc->load(['travaux' => fn ($q) => $q->orderByDesc('date_entree')]);

        $travaux = $doc->travaux;
        $totalDhs = (float) $travaux->sum(fn ($t) => $t->prix_actuel);
        $byStatut = $travaux->groupBy('statut')->map(fn ($items) => [
            'count' => $items->count(),
            'total_dhs' => (float) $items->sum(fn ($t) => $t->prix_actuel),
        ]);

        return view('clients.show', [
            'doc' => $doc,
            'travaux' => $travaux,
            'totalDhs' => $totalDhs,
            'byStatut' => $byStatut,
            'statutLabels' => \App\Models\Travail::statutLabels(),
        ]);
    }

    public function edit(Doc $doc): View
    {
        return view('clients.edit', ['doc' => $doc]);
    }

    public function update(Request $request, Doc $doc): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'numero_registration' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['nullable', 'string'],
        ]);

        $doc->update($validated);
        ActivityLog::log(ActivityLog::ACTION_UPDATED, ActivityLog::SUBJECT_CLIENT, $doc->id, 'Client ' . $doc->name . ' modifié');

        return redirect()->route('clients.index')->with('success', 'Client mis à jour.');
    }

    public function destroy(Doc $doc): RedirectResponse
    {
        if ($doc->travaux()->exists()) {
            return redirect()->route('clients.index')->with('error', 'Impossible de supprimer ce client : des travaux y sont rattachés.');
        }

        $name = $doc->name;
        $doc->delete();
        ActivityLog::log(ActivityLog::ACTION_DELETED, ActivityLog::SUBJECT_CLIENT, null, 'Client ' . $name . ' supprimé');

        return redirect()->route('clients.index')->with('success', 'Client supprimé.');
    }

    /** Tarifs du client (manager only). */
    public function clientPrestations(Doc $doc): View
    {
        $doc->load('docPrestationPrices');
        $categories = PrestationCategory::with('prestations')->orderBy('order')->orderBy('name')->get();
        return view('clients.prestations', ['doc' => $doc, 'categories' => $categories]);
    }

    public function updateClientPrestations(Request $request, Doc $doc): RedirectResponse
    {
        $prices = $request->input('prices', []);
        foreach (Prestation::pluck('id') as $prestationId) {
            $value = $prices[$prestationId] ?? '';
            if ($value !== '' && $value !== null) {
                DocPrestationPrice::updateOrCreate(
                    ['doc_id' => $doc->id, 'prestation_id' => $prestationId],
                    ['price' => (float) $value]
                );
            } else {
                DocPrestationPrice::where('doc_id', $doc->id)->where('prestation_id', $prestationId)->delete();
            }
        }
        return redirect()->route('clients.prestations', $doc)->with('success', 'Grille tarifaire enregistrée.');
    }

    /**
     * Build situation groups: one group per travail, with detailed lines per phase/prestation.
     * Each group has patient, numero_fiche, total amount, and lines (nature per phase) for rowspan display.
     */
    private function buildSituationRows(Doc $doc, $travaux): array
    {
        $OURS_TO_FDI = [
            1 => 11, 2 => 12, 3 => 13, 4 => 14, 5 => 15, 6 => 16, 7 => 17, 8 => 18,
            9 => 21, 10 => 22, 11 => 23, 12 => 24, 13 => 25, 14 => 26, 15 => 27, 16 => 28,
            17 => 31, 18 => 32, 19 => 33, 20 => 34, 21 => 35, 22 => 36, 23 => 37, 24 => 38,
            25 => 41, 26 => 42, 27 => 43, 28 => 44, 29 => 45, 30 => 46, 31 => 47, 32 => 48,
        ];
        $groups = [];
        $totalDhs = 0.0;
        foreach ($travaux as $t) {
            $statut = (string) $t->statut;
            $amount = in_array($statut, [Travail::STATUT_ANNULE, Travail::STATUT_A_REFAIRE], true)
                ? 0.0
                : (float) $t->prix_actuel;

            $phases = $t->teeth->groupBy('phase')->sortKeys();
            $lines = [];
            if ($phases->isEmpty()) {
                $lines[] = ['nature' => $t->type_travail_display];
            } else {
                foreach ($phases->values() as $teethInPhase) {
                    $first = $teethInPhase->first();
                    $prestationName = $first->prestation?->name ?? $t->type_travail_display;
                    $toothNumbers = $teethInPhase->pluck('tooth_number')->sort()->values()
                        ->map(fn ($n) => $OURS_TO_FDI[$n] ?? $n)->sort()->values();
                    $toothStr = $toothNumbers->implode(', ');
                    $lines[] = ['nature' => $prestationName . ($toothStr !== '' ? ' — Dents ' . $toothStr : '')];
                }
            }
            $groups[] = [
                'patient' => $t->patient,
                'numero_fiche' => $t->numero_fiche ?? '',
                'amount' => $amount,
                'lines' => $lines,
            ];
            $totalDhs += $amount;
        }
        return ['groups' => $groups, 'totalDhs' => $totalDhs];
    }

    private function periodSituationSummary(Doc $doc, Carbon $dateFrom, Carbon $dateTo): array
    {
        $periodStart = $dateFrom->copy()->startOfDay();
        $periodEnd = $dateTo->copy()->endOfDay();

        $travauxOfPeriod = Travail::where('doc_id', $doc->id)
            ->whereDate('date_entree', '>=', $periodStart->toDateString())
            ->whereDate('date_entree', '<=', $periodEnd->toDateString())
            ->with(['teeth' => fn ($q) => $q->orderBy('phase')->orderBy('tooth_number'), 'teeth.prestation'])
            ->orderBy('date_entree')
            ->orderBy('id')
            ->get();

        $periodRows = $this->buildSituationRows($doc, $travauxOfPeriod);
        $travauxPeriodTotal = (float) $periodRows['totalDhs'];

        $travauxBefore = Travail::where('doc_id', $doc->id)
            ->whereDate('date_entree', '<', $periodStart->toDateString())
            ->get()
            ->sum(function ($t) {
                return in_array((string) $t->statut, [Travail::STATUT_ANNULE, Travail::STATUT_A_REFAIRE], true)
                    ? 0
                    : (float) $t->prix_actuel;
            });

        $recuBefore = (float) DocSituationEncaissement::where('doc_id', $doc->id)
            ->whereDate('paid_on', '<', $periodStart->toDateString())
            ->sum('montant');

        $carryover = max(0, round((float) $travauxBefore - $recuBefore, 2));

        $encaissementsDuPeriode = DocSituationEncaissement::where('doc_id', $doc->id)
            ->whereDate('paid_on', '>=', $periodStart->toDateString())
            ->whereDate('paid_on', '<=', $periodEnd->toDateString())
            ->orderBy('paid_on')
            ->orderBy('id')
            ->get();

        $montantRecuPeriode = (float) $encaissementsDuPeriode->sum('montant');

        $situationTotal = round($carryover + $travauxPeriodTotal, 2);
        $soldeFinPeriode = max(0, round($situationTotal - $montantRecuPeriode, 2));

        return [
            'groups' => $periodRows['groups'],
            'travauxPeriodTotal' => $travauxPeriodTotal,
            'carryover' => $carryover,
            'montantRecuPeriode' => $montantRecuPeriode,
            'situationTotal' => $situationTotal,
            'soldeFinPeriode' => $soldeFinPeriode,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'encaissementsDuPeriode' => $encaissementsDuPeriode,
        ];
    }

    /** Ajouter un encaissement situation ; date du paiement saisie manuellement (rétrodatage autorisé). */
    public function storeSituationEncaissement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'doc_id' => ['required', 'integer', 'exists:docs,id'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'paid_on' => ['required', 'date', 'before_or_equal:today'],
            'montant' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
        ]);

        $dateFrom = Carbon::parse($validated['date_from'])->startOfDay();
        $dateTo = Carbon::parse($validated['date_to'])->endOfDay();

        $paidOn = Carbon::parse($validated['paid_on'])->startOfDay();

        DocSituationEncaissement::create([
            'doc_id' => (int) $validated['doc_id'],
            'year' => (int) $paidOn->year,
            'month' => (int) $paidOn->month,
            'montant' => round((float) $validated['montant'], 2),
            'paid_on' => $paidOn->toDateString(),
        ]);

        return redirect()->route('doc.situations.index', [
            'doc_id' => $validated['doc_id'],
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
        ])->with('success', 'Encaissement enregistré.');
    }

    public function destroySituationEncaissement(Request $request, DocSituationEncaissement $docSituationEncaissement): RedirectResponse
    {
        $docId = (int) $docSituationEncaissement->doc_id;
        $fallbackStart = Carbon::create((int) $docSituationEncaissement->year, (int) $docSituationEncaissement->month, 1)->startOfMonth();
        $fallbackEnd = $fallbackStart->copy()->endOfMonth();
        $dateFrom = Carbon::parse((string) $request->get('date_from', $fallbackStart->toDateString()))->startOfDay();
        $dateTo = Carbon::parse((string) $request->get('date_to', $fallbackEnd->toDateString()))->endOfDay();

        $docSituationEncaissement->delete();

        return redirect()->route('doc.situations.index', [
            'doc_id' => $docId,
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
        ])->with('success', 'Encaissement supprimé.');
    }

    /**
     * Doc situations: list travaux filtered by doc and period, with PDF download.
     */
    public function situationsIndex(Request $request): View
    {
        $docs = Doc::orderBy('name')->get();
        $current = Carbon::now();
        $doc = null;
        $groups = [];
        $travauxPeriodTotal = 0.0;
        $carryover = 0.0;
        $montantRecuPeriode = 0.0;
        $situationTotal = 0.0;
        $soldeFinPeriode = 0.0;
        $encaissementsDuPeriode = collect();
        $dateFrom = Carbon::parse((string) $request->get('date_from', $current->copy()->startOfMonth()->toDateString()))->startOfDay();
        $dateTo = Carbon::parse((string) $request->get('date_to', $current->copy()->endOfMonth()->toDateString()))->endOfDay();
        if ($dateTo->lt($dateFrom)) {
            $dateTo = $dateFrom->copy()->endOfDay();
        }
        $docId = $request->get('doc_id');

        if ($docId) {
            $doc = Doc::find($docId);
            if ($doc) {
                $result = $this->periodSituationSummary($doc, $dateFrom, $dateTo);
                $groups = $result['groups'];
                $travauxPeriodTotal = $result['travauxPeriodTotal'];
                $carryover = $result['carryover'];
                $montantRecuPeriode = $result['montantRecuPeriode'];
                $situationTotal = $result['situationTotal'];
                $soldeFinPeriode = $result['soldeFinPeriode'];
                $encaissementsDuPeriode = $result['encaissementsDuPeriode'];
            }
        }

        return view('docs.situations', [
            'docs' => $docs,
            'doc' => $doc,
            'groups' => $groups,
            'travauxPeriodTotal' => $travauxPeriodTotal,
            'carryover' => $carryover,
            'montantRecuPeriode' => $montantRecuPeriode,
            'situationTotal' => $situationTotal,
            'soldeFinPeriode' => $soldeFinPeriode,
            'dateFrom' => $dateFrom->toDateString(),
            'dateTo' => $dateTo->toDateString(),
            'docId' => $docId,
            'encaissementsDuPeriode' => $encaissementsDuPeriode,
        ]);
    }

    /**
     * Download PDF: situations report for one doc over a period.
     */
    public function situationsPdf(Request $request)
    {
        $request->validate([
            'doc_id' => ['required', 'integer', 'exists:docs,id'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
        ]);

        $doc = Doc::findOrFail($request->get('doc_id'));
        $dateFrom = Carbon::parse((string) $request->get('date_from'))->startOfDay();
        $dateTo = Carbon::parse((string) $request->get('date_to'))->endOfDay();
        $result = $this->periodSituationSummary($doc, $dateFrom, $dateTo);
        $periodLabel = 'Du ' . $dateFrom->format('d/m/Y') . ' au ' . $dateTo->format('d/m/Y');

        $labName = Setting::get('lab_name', 'MD Smile');
        $logoPath = public_path('images/mdsmile-logo.png');
        $labVille = Setting::get('lab_ville', 'Casablanca');

        $moisCourant = Carbon::now();
        $moisCourantDebut = $moisCourant->copy()->startOfMonth()->toDateString();
        $moisCourantFin = $moisCourant->copy()->endOfMonth()->toDateString();
        $montantRecuMoisCourant = (float) DocSituationEncaissement::where('doc_id', $doc->id)
            ->whereDate('paid_on', '>=', $moisCourantDebut)
            ->whereDate('paid_on', '<=', $moisCourantFin)
            ->sum('montant');
        $moisCourantLibelle = $moisCourant->copy()->locale('fr')->translatedFormat('F Y');

        $pdf = Pdf::loadView('docs.situations-pdf', [
            'doc' => $doc,
            'groups' => $result['groups'],
            'travauxPeriodTotal' => $result['travauxPeriodTotal'],
            'carryover' => $result['carryover'],
            'montantRecuPeriode' => $result['montantRecuPeriode'],
            'situationTotal' => $result['situationTotal'],
            'soldeFinPeriode' => $result['soldeFinPeriode'],
            'periodLabel' => $periodLabel,
            'montantRecuMoisCourant' => $montantRecuMoisCourant,
            'moisCourantLibelle' => $moisCourantLibelle,
            'labName' => $labName,
            'logoPath' => $logoPath,
            'labVille' => $labVille,
            'encaissementsDuPeriode' => $result['encaissementsDuPeriode'],
        ])->setPaper('a4', 'portrait');

        $filename = 'situation-' . \Illuminate\Support\Str::slug($doc->name) . '-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }
}
