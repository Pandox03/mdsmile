<?php

namespace App\Http\Controllers;

use App\Models\DevisInvisalign;
use App\Models\Doc;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DevisInvisalignController extends Controller
{
    public function index(Request $request): View
    {
        $docs = Doc::orderBy('name')->get();
        $query = DevisInvisalign::query()->with('doc')->orderByDesc('date_devis')->orderByDesc('id');

        if ($request->filled('doc_id')) {
            $query->where('doc_id', $request->integer('doc_id'));
        }
        if ($request->filled('q')) {
            $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->get('q')) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('numero', 'like', $term)
                    ->orWhere('client_nom', 'like', $term)
                    ->orWhere('patient_nom', 'like', $term);
            });
        }

        return view('devis-invisalign.index', [
            'docs' => $docs,
            'devisList' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        $docs = Doc::orderBy('name')->get();
        $prefix = trim(Setting::get('devis_invisalign_prefix', 'DI')) ?: 'DI';
        $nextNum = max(1, (int) Setting::get('devis_invisalign_prochain_numero', '1'));
        $previewNumero = $prefix . '-' . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);

        return view('devis-invisalign.create', [
            'docs' => $docs,
            'previewNumero' => $previewNumero,
            'defaults' => $this->defaultFields(),
            'selectedDocId' => $request->get('doc_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->withCalculatedTotals($this->validateDevis($request));

        $devis = Cache::lock('devis_invisalign_numero', 10)->block(10, function () use ($validated, $request) {
            $prefix = trim(Setting::get('devis_invisalign_prefix', 'DI')) ?: 'DI';
            $nextNum = max(1, (int) Setting::get('devis_invisalign_prochain_numero', '1'));
            $numero = $prefix . '-' . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);

            $devis = DevisInvisalign::create([
                'numero' => $numero,
                'date_devis' => $validated['date_devis'],
                'doc_id' => $validated['doc_id'] ?? null,
                'client_nom' => $validated['client_nom'],
                'patient_nom' => $validated['patient_nom'],
                'opt_maxillaire' => $request->boolean('opt_maxillaire'),
                'opt_mandibulaire' => $request->boolean('opt_mandibulaire'),
                'taquets' => $request->boolean('taquets'),
                'stripping' => $request->boolean('stripping'),
                'conditions_paiement' => $validated['conditions_paiement'] ?? null,
                'banque_ligne' => $validated['banque_ligne'] ?? null,
                'beneficiaire' => $validated['beneficiaire'] ?? null,
                'rib' => $validated['rib'] ?? null,
                'commandes_email' => $validated['commandes_email'] ?? null,
                'societe_footer' => $validated['societe_footer'] ?? null,
                'total_ttc' => $this->sumLignesTtc($validated['lignes']),
            ]);

            foreach ($validated['lignes'] as $i => $row) {
                $devis->lignes()->create([
                    'sort_order' => $i,
                    'designation' => $row['designation'],
                    'quantite' => (float) ($row['quantite'] ?? 1),
                    'arcade' => $row['arcade'] ?? null,
                    'prix_unitaire' => (float) ($row['prix_unitaire'] ?? 0),
                    'total_ttc' => (float) ($row['total_ttc'] ?? 0),
                ]);
            }

            Setting::set('devis_invisalign_prochain_numero', (string) ($nextNum + 1));

            return $devis;
        });

        return redirect()->route('devis-invisalign.edit', $devis)->with('success', 'Devis Invisalign enregistré.');
    }

    public function edit(DevisInvisalign $devisInvisalign): View
    {
        $devisInvisalign->load('lignes');

        return view('devis-invisalign.edit', [
            'devis' => $devisInvisalign,
            'docs' => Doc::orderBy('name')->get(),
            'defaults' => $this->defaultFields(),
        ]);
    }

    public function update(Request $request, DevisInvisalign $devisInvisalign): RedirectResponse
    {
        $validated = $this->withCalculatedTotals($this->validateDevis($request));

        $devisInvisalign->update([
            'date_devis' => $validated['date_devis'],
            'doc_id' => $validated['doc_id'] ?? null,
            'client_nom' => $validated['client_nom'],
            'patient_nom' => $validated['patient_nom'],
            'opt_maxillaire' => $request->boolean('opt_maxillaire'),
            'opt_mandibulaire' => $request->boolean('opt_mandibulaire'),
            'taquets' => $request->boolean('taquets'),
            'stripping' => $request->boolean('stripping'),
            'conditions_paiement' => $validated['conditions_paiement'] ?? null,
            'banque_ligne' => $validated['banque_ligne'] ?? null,
            'beneficiaire' => $validated['beneficiaire'] ?? null,
            'rib' => $validated['rib'] ?? null,
            'commandes_email' => $validated['commandes_email'] ?? null,
            'societe_footer' => $validated['societe_footer'] ?? null,
        ]);

        $devisInvisalign->lignes()->delete();
        foreach ($validated['lignes'] as $i => $row) {
            $devisInvisalign->lignes()->create([
                'sort_order' => $i,
                'designation' => $row['designation'],
                'quantite' => (float) ($row['quantite'] ?? 1),
                'arcade' => $row['arcade'] ?? null,
                'prix_unitaire' => (float) ($row['prix_unitaire'] ?? 0),
                'total_ttc' => (float) ($row['total_ttc'] ?? 0),
            ]);
        }
        $devisInvisalign->recalculeTotal();

        return redirect()->route('devis-invisalign.edit', $devisInvisalign)->with('success', 'Devis Invisalign mis à jour.');
    }

    public function destroy(DevisInvisalign $devisInvisalign): RedirectResponse
    {
        $devisInvisalign->delete();

        return redirect()->route('devis-invisalign.index')->with('success', 'Devis Invisalign supprimé.');
    }

    public function pdf(DevisInvisalign $devisInvisalign): Response
    {
        $devisInvisalign->load(['doc', 'lignes']);
        $labName = Setting::get('lab_name', 'MD Smile');
        $logoPath = public_path('images/mdsmile-logo.png');
        $devise = Setting::get('facture_devise', 'DHS');
        $footer = $devisInvisalign->societe_footer ?: $this->defaultSocieteFooter();

        $pdf = Pdf::loadView('devis-invisalign.pdf', [
            'devis' => $devisInvisalign,
            'labName' => $labName,
            'logoPath' => $logoPath,
            'labAdresse' => Setting::get('lab_adresse'),
            'labPhone' => Setting::get('lab_phone'),
            'labEmail' => Setting::get('lab_email'),
            'devise' => $devise,
            'footerSociete' => $footer,
        ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->stream('devis-invisalign-' . $devisInvisalign->numero . '.pdf');
    }

    private function validateDevis(Request $request): array
    {
        return $request->validate([
            'doc_id' => ['nullable', 'integer', 'exists:docs,id'],
            'date_devis' => ['required', 'date'],
            'client_nom' => ['required', 'string', 'max:255'],
            'patient_nom' => ['required', 'string', 'max:255'],
            'conditions_paiement' => ['nullable', 'string', 'max:5000'],
            'banque_ligne' => ['nullable', 'string', 'max:500'],
            'beneficiaire' => ['nullable', 'string', 'max:255'],
            'rib' => ['nullable', 'string', 'max:255'],
            'commandes_email' => ['nullable', 'string', 'max:255'],
            'societe_footer' => ['nullable', 'string', 'max:5000'],
            'lignes' => ['required', 'array', 'min:1'],
            'lignes.*.designation' => ['required', 'string', 'max:500'],
            'lignes.*.quantite' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.arcade' => ['nullable', 'string', 'max:255'],
            'lignes.*.prix_unitaire' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.total_ttc' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    /** Line total HT is quantity × unit price. The quote total is the sum of those lines. */
    private function withCalculatedTotals(array $validated): array
    {
        foreach ($validated['lignes'] as $i => $row) {
            $qty = ($row['quantite'] ?? null) === null || $row['quantite'] === ''
                ? 1.0
                : (float) $row['quantite'];
            $validated['lignes'][$i]['quantite'] = $qty;
            $validated['lignes'][$i]['total_ttc'] = round($qty * (float) ($row['prix_unitaire'] ?? 0), 2);
        }

        return $validated;
    }

    /** @param array<int, array<string, mixed>> $lignes */
    private function sumLignesTtc(array $lignes): float
    {
        $sum = 0.0;
        foreach ($lignes as $row) {
            $sum += (float) ($row['total_ttc'] ?? 0);
        }

        return round($sum, 2);
    }

    /** @return array<string, string|null> */
    private function defaultFields(): array
    {
        return [
            'conditions_paiement' => Setting::get('devis_invisalign_conditions_paiement', "50% à la commande\n50% à la livraison"),
            'banque_ligne' => Setting::get('devis_invisalign_banque_ligne'),
            'beneficiaire' => Setting::get('devis_invisalign_beneficiaire'),
            'rib' => Setting::get('devis_invisalign_rib'),
            'commandes_email' => Setting::get('devis_invisalign_commandes_email'),
            'societe_footer' => Setting::get('devis_invisalign_societe_footer') ?: $this->defaultSocieteFooter(),
        ];
    }

    private function defaultSocieteFooter(): string
    {
        $parts = array_filter([
            Setting::get('lab_name'),
            Setting::get('lab_siege_social') ?: Setting::get('lab_adresse'),
            Setting::get('lab_phone') ? 'Tél. ' . Setting::get('lab_phone') : null,
            Setting::get('lab_email'),
            Setting::get('lab_ice') ? 'ICE : ' . Setting::get('lab_ice') : null,
        ]);

        return implode(' — ', $parts);
    }
}
