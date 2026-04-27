<?php

namespace App\Http\Controllers;

use App\Models\Prestation;
use App\Models\PrestationCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrestationsController extends Controller
{
    public function index(): View
    {
        $categories = PrestationCategory::withCount('prestations')
            ->with('prestations')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('prestations.index', ['categories' => $categories]);
    }

    public function createCategory(): View
    {
        return view('prestations.category-form', ['category' => null]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);
        $validated['order'] = $validated['order'] ?? 0;
        PrestationCategory::create($validated);
        return redirect()->route('prestations.index')->with('success', 'Catégorie créée.');
    }

    public function editCategory(PrestationCategory $prestationCategory): View
    {
        return view('prestations.category-form', ['category' => $prestationCategory]);
    }

    public function updateCategory(Request $request, PrestationCategory $prestationCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);
        $validated['order'] = $validated['order'] ?? 0;
        $prestationCategory->update($validated);
        return redirect()->route('prestations.index')->with('success', 'Catégorie mise à jour.');
    }

    public function destroyCategory(PrestationCategory $prestationCategory): RedirectResponse
    {
        $prestationCategory->delete();
        return redirect()->route('prestations.index')->with('success', 'Catégorie supprimée.');
    }

    public function createPrestation(Request $request): View
    {
        $categoryId = $request->get('category');
        $categories = PrestationCategory::orderBy('order')->orderBy('name')->get();
        return view('prestations.prestation-form', [
            'prestation' => null,
            'categories' => $categories,
            'selectedCategoryId' => $categoryId ? (int) $categoryId : ($categories->first()?->id),
        ]);
    }

    public function storePrestation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prestation_category_id' => ['required', 'exists:prestation_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);
        $validated['price'] = $request->filled('price') ? $validated['price'] : null;
        $validated['order'] = $validated['order'] ?? 0;
        Prestation::create($validated);
        return redirect()->route('prestations.index')->with('success', 'Prestation créée.');
    }

    public function editPrestation(Prestation $prestation): View
    {
        $categories = PrestationCategory::orderBy('order')->orderBy('name')->get();
        return view('prestations.prestation-form', [
            'prestation' => $prestation,
            'categories' => $categories,
            'selectedCategoryId' => $prestation->prestation_category_id,
        ]);
    }

    public function updatePrestation(Request $request, Prestation $prestation): RedirectResponse
    {
        $validated = $request->validate([
            'prestation_category_id' => ['required', 'exists:prestation_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);
        $validated['price'] = $request->filled('price') ? $validated['price'] : null;
        $validated['order'] = $validated['order'] ?? 0;
        $prestation->update($validated);
        return redirect()->route('prestations.index')->with('success', 'Prestation mise à jour.');
    }

    public function destroyPrestation(Prestation $prestation): RedirectResponse
    {
        $prestation->delete();
        return redirect()->route('prestations.index')->with('success', 'Prestation supprimée.');
    }
}
