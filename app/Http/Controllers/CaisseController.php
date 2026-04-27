<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CaisseMouvement;
use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CaisseController extends Controller
{
    public function index(Request $request): View
    {
        $baseQuery = CaisseMouvement::query()->orderByDesc('date_mouvement')->orderByDesc('created_at');

        if ($request->filled('date_from')) {
            $baseQuery->where('date_mouvement', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $baseQuery->where('date_mouvement', '<=', $request->get('date_to'));
        }

        $entrees = (clone $baseQuery)->where('type', CaisseMouvement::TYPE_ENTREE)->with('facture')->get();
        $sorties = (clone $baseQuery)->where('type', CaisseMouvement::TYPE_SORTIE)->get();

        $totalEntrees = $entrees->sum('montant');
        $totalSorties = $sorties->sum('montant');
        $solde = $totalEntrees - $totalSorties;

        return view('caisse.index', [
            'entrees' => $entrees,
            'sorties' => $sorties,
            'totalEntrees' => $totalEntrees,
            'totalSorties' => $totalSorties,
            'solde' => $solde,
            'dateFrom' => $request->get('date_from'),
            'dateTo' => $request->get('date_to'),
        ]);
    }

    /**
     * Export caisse movements as a PDF report for the current filter period.
     */
    public function report(Request $request)
    {
        $baseQuery = CaisseMouvement::query()->orderByDesc('date_mouvement')->orderByDesc('created_at');

        if ($request->filled('date_from')) {
            $baseQuery->where('date_mouvement', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $baseQuery->where('date_mouvement', '<=', $request->get('date_to'));
        }

        $entrees = (clone $baseQuery)->where('type', CaisseMouvement::TYPE_ENTREE)->with('facture')->get();
        $sorties = (clone $baseQuery)->where('type', CaisseMouvement::TYPE_SORTIE)->get();

        $totalEntrees = $entrees->sum('montant');
        $totalSorties = $sorties->sum('montant');
        $solde = $totalEntrees - $totalSorties;

        $pdf = Pdf::loadView('caisse.report', [
            'entrees' => $entrees,
            'sorties' => $sorties,
            'totalEntrees' => $totalEntrees,
            'totalSorties' => $totalSorties,
            'solde' => $solde,
            'dateFrom' => $request->get('date_from'),
            'dateTo' => $request->get('date_to'),
        ])->setPaper('a4', 'portrait');

        $filename = 'caisse-' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    public function create(Request $request): View
    {
        $type = $request->get('type', CaisseMouvement::TYPE_ENTREE);
        if (!in_array($type, [CaisseMouvement::TYPE_ENTREE, CaisseMouvement::TYPE_SORTIE], true)) {
            $type = CaisseMouvement::TYPE_ENTREE;
        }
        $factures = Facture::query()
            ->with('doc')
            ->orderByDesc('date_facture')
            ->get();
        return view('caisse.create', ['type' => $type, 'factures' => $factures]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:entree,sortie'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'date_mouvement' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
            'facture_id' => ['nullable', 'exists:factures,id'],
        ]);

        $mouvement = CaisseMouvement::create($validated);
        $label = $validated['type'] === CaisseMouvement::TYPE_ENTREE ? 'Entrée' : 'Sortie';
        ActivityLog::log(ActivityLog::ACTION_CREATED, ActivityLog::SUBJECT_CAISSE, $mouvement->id, $label . ' caisse ' . number_format($mouvement->montant, 2, ',', ' ') . ' DHS');
        return redirect()->route('caisse.index')->with('success', $label . ' enregistrée.');
    }

    public function edit(CaisseMouvement $caisseMouvement): View
    {
        $factures = Facture::query()
            ->with('doc')
            ->orderByDesc('date_facture')
            ->get();
        return view('caisse.edit', ['mouvement' => $caisseMouvement, 'factures' => $factures]);
    }

    public function update(Request $request, CaisseMouvement $caisseMouvement): RedirectResponse
    {
        $validated = $request->validate([
            'montant' => ['required', 'numeric', 'min:0.01'],
            'date_mouvement' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
            'facture_id' => ['nullable', 'exists:factures,id'],
        ]);

        $caisseMouvement->update($validated);
        ActivityLog::log(ActivityLog::ACTION_UPDATED, ActivityLog::SUBJECT_CAISSE, $caisseMouvement->id, 'Mouvement caisse modifié');
        return redirect()->route('caisse.index')->with('success', 'Mouvement mis à jour.');
    }

    public function destroy(CaisseMouvement $caisseMouvement): RedirectResponse
    {
        if (auth()->user()->hasRole('assistante')) {
            abort(403, 'Vous n\'êtes pas autorisé à supprimer un mouvement caisse.');
        }
        $caisseMouvement->delete();
        ActivityLog::log(ActivityLog::ACTION_DELETED, ActivityLog::SUBJECT_CAISSE, null, 'Mouvement caisse supprimé');
        return redirect()->route('caisse.index')->with('success', 'Mouvement supprimé.');
    }
}
