<?php

use App\Models\Facture;
use App\Models\Setting;
use App\Models\Travail;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Create one facture per travail that has no factures (so all travaux appear in factures table).
     */
    public function up(): void
    {
        $travauxSansFacture = Travail::whereDoesntHave('factures')->with('doc')->get();
        if ($travauxSansFacture->isEmpty()) {
            return;
        }

        foreach ($travauxSansFacture as $travail) {
            DB::transaction(function () use ($travail) {
                $cap = (float) ($travail->montant_comptabilise ?? $travail->prix_dhs);
                $facture = Cache::lock('facture_numero', 10)->block(10, function () use ($travail, $cap) {
                    $prefix = trim(Setting::get('facture_prefix', 'FAC')) ?: 'FAC';
                    $nextNum = max(1, (int) Setting::get('facture_prochain_numero', '1'));
                    $numero = $prefix . '-' . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
                    $facture = Facture::create([
                        'doc_id' => $travail->doc_id,
                        'numero' => $numero,
                        'date_facture' => $travail->date_entree,
                        'montant_comptabilise' => $cap,
                    ]);
                    Setting::set('facture_prochain_numero', (string) ($nextNum + 1));
                    return $facture;
                });
                $facture->travaux()->attach($travail->id, ['prix_comptabilise' => $cap]);
                $travail->update(['prix_comptabilise' => $cap]);
            });
        }
    }

    public function down(): void
    {
        // Optional: remove backfilled factures (those with exactly one travail and prix_comptabilise = 0).
        // We don't have a reliable way to mark "backfilled" factures, so down() is a no-op.
    }
};
