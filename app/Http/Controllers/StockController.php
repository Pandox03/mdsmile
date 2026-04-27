<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Fournisseur;
use App\Models\Stock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $materialQuery = Stock::query()->with('fournisseur')->orderBy('name');
        if ($request->filled('recherche')) {
            $term = $request->get('recherche');
            $materialQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('reference', 'like', '%' . $term . '%');
            });
        }
        $materials = $materialQuery->paginate(15, ['*'], 'materials_page')->withQueryString();

        $fournisseurQuery = Fournisseur::query()->withCount('stockItems')->orderBy('name');
        if ($request->filled('fournisseur_recherche')) {
            $term = $request->get('fournisseur_recherche');
            $fournisseurQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('phone', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%');
            });
        }
        $fournisseurs = $fournisseurQuery->paginate(15, ['*'], 'fournisseurs_page')->withQueryString();

        return view('stock.index', [
            'materials' => $materials,
            'fournisseurs' => $fournisseurs,
        ]);
    }

    // ——— Materials ———
    public function createMaterial(): View
    {
        $fournisseurs = Fournisseur::orderBy('name')->get();
        return view('stock.materials.create', ['fournisseurs' => $fournisseurs]);
    }

    public function storeMaterial(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'seuil_alerte_min' => ['nullable', 'numeric', 'min:0'],
            'unite' => ['nullable', 'string', 'max:32'],
            'fournisseur_id' => ['nullable', 'exists:fournisseurs,id'],
        ]);
        $validated['quantity'] = $validated['quantity'] ?? 0;
        $validated['seuil_alerte_min'] = $validated['seuil_alerte_min'] ?? 5;
        $validated['unite'] = $validated['unite'] ?? 'pce';

        $stock = Stock::create($validated);
        ActivityLog::log(ActivityLog::ACTION_CREATED, ActivityLog::SUBJECT_STOCK, $stock->id, 'Matériau ' . $stock->name . ' ajouté au stock');
        return redirect()->route('stock.index')->with('success', 'Matériau ajouté au stock.');
    }

    public function editMaterial(Stock $stock): View
    {
        $fournisseurs = Fournisseur::orderBy('name')->get();
        return view('stock.materials.edit', ['stock' => $stock, 'fournisseurs' => $fournisseurs]);
    }

    public function updateMaterial(Request $request, Stock $stock): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'seuil_alerte_min' => ['nullable', 'numeric', 'min:0'],
            'unite' => ['nullable', 'string', 'max:32'],
            'fournisseur_id' => ['nullable', 'exists:fournisseurs,id'],
        ]);
        $validated['quantity'] = $validated['quantity'] ?? 0;
        $validated['seuil_alerte_min'] = $validated['seuil_alerte_min'] ?? 5;
        $validated['unite'] = $validated['unite'] ?? 'pce';

        $stock->update($validated);
        ActivityLog::log(ActivityLog::ACTION_UPDATED, ActivityLog::SUBJECT_STOCK, $stock->id, 'Matériau ' . $stock->name . ' modifié');
        return redirect()->route('stock.index')->with('success', 'Matériau mis à jour.');
    }

    public function destroyMaterial(Stock $stock): RedirectResponse
    {
        if ($stock->travailTeeth()->exists()) {
            return redirect()->route('stock.index')->with('error', 'Ce matériau est utilisé dans des travaux ; suppression impossible.');
        }
        $name = $stock->name;
        $stock->delete();
        ActivityLog::log(ActivityLog::ACTION_DELETED, ActivityLog::SUBJECT_STOCK, null, 'Matériau ' . $name . ' supprimé');
        return redirect()->route('stock.index')->with('success', 'Matériau supprimé.');
    }

    // ——— Fournisseurs ———
    public function createFournisseur(): View
    {
        return view('stock.fournisseurs.create');
    }

    public function storeFournisseur(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['nullable', 'string'],
        ]);

        $fournisseur = Fournisseur::create($validated);
        ActivityLog::log(ActivityLog::ACTION_CREATED, ActivityLog::SUBJECT_FOURNISSEUR, $fournisseur->id, 'Fournisseur ' . $fournisseur->name . ' ajouté');
        return redirect()->route('stock.index')->with('success', 'Fournisseur ajouté.');
    }

    public function editFournisseur(Fournisseur $fournisseur): View
    {
        return view('stock.fournisseurs.edit', ['fournisseur' => $fournisseur]);
    }

    public function updateFournisseur(Request $request, Fournisseur $fournisseur): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['nullable', 'string'],
        ]);

        $fournisseur->update($validated);
        ActivityLog::log(ActivityLog::ACTION_UPDATED, ActivityLog::SUBJECT_FOURNISSEUR, $fournisseur->id, 'Fournisseur ' . $fournisseur->name . ' modifié');
        return redirect()->route('stock.index')->with('success', 'Fournisseur mis à jour.');
    }

    public function destroyFournisseur(Fournisseur $fournisseur): RedirectResponse
    {
        $name = $fournisseur->name;
        $fournisseur->stockItems()->update(['fournisseur_id' => null]);
        $fournisseur->delete();
        ActivityLog::log(ActivityLog::ACTION_DELETED, ActivityLog::SUBJECT_FOURNISSEUR, null, 'Fournisseur ' . $name . ' supprimé');
        return redirect()->route('stock.index')->with('success', 'Fournisseur supprimé.');
    }
}
