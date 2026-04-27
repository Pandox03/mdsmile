<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParametresController extends Controller
{
    public const KEYS = [
        'lab_name',
        'lab_adresse',
        'lab_phone',
        'lab_email',
        'lab_siege_social',
        'lab_ice',
        'lab_tp',
        'lab_if',
        'facture_prefix',
        'facture_devise',
        'facture_prochain_numero',
        'facture_tva_rate',
    ];

    public function index(): View
    {
        $settings = Setting::getMany(self::KEYS);
        return view('parametres.index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lab_name' => ['nullable', 'string', 'max:255'],
            'lab_adresse' => ['nullable', 'string'],
            'lab_phone' => ['nullable', 'string', 'max:255'],
            'lab_email' => ['nullable', 'email', 'max:255'],
            'lab_siege_social' => ['nullable', 'string'],
            'lab_ice' => ['nullable', 'string', 'max:64'],
            'lab_tp' => ['nullable', 'string', 'max:64'],
            'lab_if' => ['nullable', 'string', 'max:64'],
            'facture_prefix' => ['nullable', 'string', 'max:32'],
            'facture_devise' => ['nullable', 'string', 'max:16'],
            'facture_prochain_numero' => ['nullable', 'integer', 'min:1'],
            'facture_tva_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        Setting::setMany($validated);

        return redirect()->route('parametres.index')->with('success', 'Paramètres enregistrés.');
    }
}
