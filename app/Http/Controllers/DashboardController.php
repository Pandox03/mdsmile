<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\Stock;
use App\Models\Travail;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $travauxEnCours = Travail::query()
            ->whereIn('statut', [Travail::STATUT_EN_ATTENTE, Travail::STATUT_EN_COURS])
            ->count();

        $allFactures = Facture::with('travaux')->get();
        $chiffreAffaires = $allFactures->sum(fn ($f) => (float) $f->total_facture);
        $facturesImpayeesMontant = 0.0;
        $facturesImpayeesCount = 0;

        $stockFaibleItems = Stock::whereColumn('quantity', '<', 'seuil_alerte_min')->orderBy('quantity')->get();
        $stockFaible = $stockFaibleItems->count();

        $last6Months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $last6Months->push([
                'label' => $date->locale('fr')->translatedFormat('M'),
                'year_month' => $date->format('Y-m'),
            ]);
        }
        $revenueByMonth = Facture::with('travaux')
            ->get()
            ->groupBy(fn ($f) => $f->date_facture->format('Y-m'));
        $chartLabels = $last6Months->pluck('label')->toArray();
        $chartData = $last6Months->map(function ($m) use ($revenueByMonth) {
            $factures = $revenueByMonth->get($m['year_month'], collect());
            return $factures->sum(fn ($f) => (float) $f->total_facture);
        })->values()->toArray();

        $derniersTravaux = Travail::with('doc')
            ->orderByDesc('date_entree')
            ->limit(8)
            ->get();

        $livraisonsDemain = Travail::with('doc')
            ->whereDate('date_livraison', Carbon::tomorrow())
            ->orderBy('date_livraison')
            ->get();

        return view('dashboard', [
            'travauxEnCours' => $travauxEnCours,
            'chiffreAffaires' => $chiffreAffaires,
            'facturesImpayeesMontant' => $facturesImpayeesMontant,
            'facturesImpayeesCount' => $facturesImpayeesCount,
            'stockFaible' => $stockFaible,
            'stockFaibleItems' => $stockFaibleItems,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'derniersTravaux' => $derniersTravaux,
            'livraisonsDemain' => $livraisonsDemain,
            'statutLabels' => Travail::statutLabels(),
        ]);
    }
}
