<?php

namespace App\Http\Controllers;

use App\Models\Doc;
use App\Models\Facture;
use App\Models\Stock;
use App\Models\Travail;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $clients = collect();
        $travaux = collect();
        $factures = collect();
        $stock = collect();

        if ($q !== '') {
            $term = '%' . $q . '%';
            $idFromRef = preg_replace('/^TR-0*/i', '', $q);
            $numericId = ($idFromRef !== '' && ctype_digit($idFromRef)) ? (int) $idFromRef : null;

            $clients = Doc::query()
                ->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhere('numero_registration', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('adresse', 'like', $term);
                })
                ->orderBy('name')
                ->limit(10)
                ->get();

            $travaux = Travail::query()
                ->with('doc')
                ->where(function ($query) use ($term, $numericId, $q) {
                    $query->where('patient', 'like', $term)
                        ->orWhere('type_travail', 'like', $term)
                        ->orWhere('dentiste', 'like', $term);
                    if ($numericId !== null) {
                        $query->orWhere('id', $numericId);
                    }
                    if (ctype_digit($q)) {
                        $query->orWhere('id', (int) $q);
                    }
                })
                ->orderByDesc('date_entree')
                ->limit(10)
                ->get();

            $factures = Facture::query()
                ->with('doc')
                ->where('numero', 'like', $term)
                ->orderByDesc('date_facture')
                ->limit(10)
                ->get();

            $stock = Stock::query()
                ->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhere('reference', 'like', $term);
                })
                ->orderBy('name')
                ->limit(10)
                ->get();
        }

        return view('search.index', [
            'q' => $q,
            'clients' => $clients,
            'travaux' => $travaux,
            'factures' => $factures,
            'stock' => $stock,
            'statutLabels' => Travail::statutLabels(),
        ]);
    }
}
