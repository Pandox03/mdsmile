<?php

namespace Database\Seeders;

use App\Models\Doc;
use App\Models\Facture;
use App\Models\Prestation;
use App\Models\Setting;
use App\Models\Travail;
use App\Models\TravailTooth;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Données de test pour factures ventilées + situations (reste après paiements).
 *
 * Scénario : 3 travaux terminés (10 000 DHS au total) pour un doc dédié.
 * Une facture PAYÉE de 2 000 DHS imputée au 1er travail seulement
 * → reste situation total attendu : 8 000 DHS (2000 + 3500 + 2500).
 *
 * Usage : php artisan db:seed --class=FactureVentilationTestSeeder
 */
class FactureVentilationTestSeeder extends Seeder
{
    private const DOC_REGISTRATION = 'DR-TEST-VENTIL';

    public function run(): void
    {
        if (Travail::where('numero_fiche', 'F-VENTIL-001')->exists()) {
            $this->command?->info('FactureVentilationTestSeeder : données déjà présentes (F-VENTIL-001). Rien à faire.');

            return;
        }

        $prestation = Prestation::query()->orderBy('id')->first();
        if (! $prestation) {
            $this->command?->error('Aucune prestation en base. Lancez d’abord PrestationSeeder.');

            return;
        }

        DB::transaction(function () use ($prestation) {
            $doc = Doc::firstOrCreate(
                ['numero_registration' => self::DOC_REGISTRATION],
                [
                    'name' => 'Dr. Test Ventilation Factures',
                    'phone' => '+212 5 00 99 01',
                    'email' => 'test-ventil@mdsmile.local',
                    'adresse' => 'Casablanca (données de test)',
                ]
            );

            $rows = [
                [
                    'patient' => 'Patient Test A',
                    'numero_fiche' => 'F-VENTIL-001',
                    'prix_dhs' => 4000,
                    'date_entree' => '2026-03-05',
                    'date_livraison' => '2026-03-20',
                ],
                [
                    'patient' => 'Patient Test B',
                    'numero_fiche' => 'F-VENTIL-002',
                    'prix_dhs' => 3500,
                    'date_entree' => '2026-03-08',
                    'date_livraison' => '2026-03-22',
                ],
                [
                    'patient' => 'Patient Test C',
                    'numero_fiche' => 'F-VENTIL-003',
                    'prix_dhs' => 2500,
                    'date_entree' => '2026-03-12',
                    'date_livraison' => '2026-03-25',
                ],
            ];

            $travaux = [];
            foreach ($rows as $row) {
                $t = Travail::create([
                    'doc_id' => $doc->id,
                    'prestation_id' => $prestation->id,
                    'dentiste' => $doc->name,
                    'patient' => $row['patient'],
                    'numero_fiche' => $row['numero_fiche'],
                    'type_travail' => $prestation->name,
                    'date_entree' => $row['date_entree'],
                    'date_livraison' => $row['date_livraison'],
                    'date_essiage' => null,
                    'prix_dhs' => $row['prix_dhs'],
                    'statut' => Travail::STATUT_TERMINE,
                ]);
                TravailTooth::create([
                    'travail_id' => $t->id,
                    'tooth_number' => 11,
                    'prestation_id' => $prestation->id,
                    'phase' => 1,
                    'stock_id' => null,
                    'quantity' => 1,
                ]);
                $travaux[] = $t;
            }

            $t1 = $travaux[0];

            $montantFacture = 2000.0;

            $facture = Cache::lock('facture_numero', 10)->block(10, function () use ($doc, $montantFacture, $t1) {
                $prefix = trim(Setting::get('facture_prefix', 'FAC')) ?: 'FAC';
                $nextNum = max(1, (int) Setting::get('facture_prochain_numero', '1'));
                $numero = $prefix . '-' . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);

                $f = Facture::create([
                    'doc_id' => $doc->id,
                    'numero' => $numero,
                    'date_facture' => '2026-03-15',
                    'montant_comptabilise' => $montantFacture,
                    'comptabilise_dans_restant' => true,
                ]);
                $f->travaux()->attach($t1->id, ['prix_comptabilise' => $montantFacture]);

                Setting::set('facture_prochain_numero', (string) ($nextNum + 1));

                return $f;
            });

            $this->command?->info(sprintf(
                'FactureVentilationTestSeeder : doc %s (id %d), 3 travaux, facture %s payée de %s DHS sur %s.',
                $doc->name,
                $doc->id,
                $facture->numero,
                number_format($montantFacture, 0, ',', ' '),
                $t1->reference
            ));
            $this->command?->info('Situation (mars 2026) : filtre ce doc + dates 2026-03-01 au 2026-03-31 → total reste attendu ≈ 8 000 DHS.');
        });
    }
}
