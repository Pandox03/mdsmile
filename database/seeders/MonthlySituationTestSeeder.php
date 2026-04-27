<?php

namespace Database\Seeders;

use App\Models\Doc;
use App\Models\DocSituationEncaissement;
use App\Models\Prestation;
use App\Models\Travail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed data to test monthly situations with carryover (sans factures) :
 * - Jan travaux total: 5 000
 * - Jan encaissement situation: 3 000 -> report Fév: 2 000
 * - Feb travaux total (comptés): 2 500 (annule + a_refaire = 0)
 * - Feb encaissement situation: 1 000
 * - Feb situation: 2 000 + 2 500 = 4 500 ; reste fin: 3 500
 */
class MonthlySituationTestSeeder extends Seeder
{
    public function run(): void
    {
        $doc = Doc::firstOrCreate(
            ['numero_registration' => 'DR-MONTH-TEST'],
            [
                'name' => 'Dr. Monthly Situation Test',
                'phone' => '+212 5 00 88 77',
                'email' => 'monthly-test@mdsmile.local',
                'adresse' => 'Casablanca',
            ]
        );

        if (Travail::where('numero_fiche', 'MS-2026-01-A')->exists()) {
            $this->command?->info('MonthlySituationTestSeeder: test data already exists.');

            return;
        }

        $prestationId = (int) (Prestation::query()->orderBy('id')->value('id') ?? 0);

        DB::transaction(function () use ($doc, $prestationId) {
            Travail::create([
                'doc_id' => $doc->id,
                'prestation_id' => $prestationId ?: null,
                'dentiste' => $doc->name,
                'patient' => 'Patient Jan A',
                'numero_fiche' => 'MS-2026-01-A',
                'type_travail' => 'Couronne',
                'date_entree' => '2026-01-10',
                'date_livraison' => '2026-01-18',
                'prix_dhs' => 3000,
                'statut' => Travail::STATUT_TERMINE,
            ]);

            Travail::create([
                'doc_id' => $doc->id,
                'prestation_id' => $prestationId ?: null,
                'dentiste' => $doc->name,
                'patient' => 'Patient Jan B',
                'numero_fiche' => 'MS-2026-01-B',
                'type_travail' => 'Bridge',
                'date_entree' => '2026-01-15',
                'date_livraison' => '2026-01-22',
                'prix_dhs' => 2000,
                'statut' => Travail::STATUT_EN_COURS,
            ]);

            Travail::create([
                'doc_id' => $doc->id,
                'prestation_id' => $prestationId ?: null,
                'dentiste' => $doc->name,
                'patient' => 'Patient Fev A',
                'numero_fiche' => 'MS-2026-02-A',
                'type_travail' => 'Facette',
                'date_entree' => '2026-02-09',
                'date_livraison' => '2026-02-17',
                'prix_dhs' => 2500,
                'statut' => Travail::STATUT_TERMINE,
            ]);

            Travail::create([
                'doc_id' => $doc->id,
                'prestation_id' => $prestationId ?: null,
                'dentiste' => $doc->name,
                'patient' => 'Patient Fev B',
                'numero_fiche' => 'MS-2026-02-B',
                'type_travail' => 'Inlay',
                'date_entree' => '2026-02-11',
                'date_livraison' => '2026-02-20',
                'prix_dhs' => 1200,
                'statut' => Travail::STATUT_A_REFAIRE,
            ]);

            Travail::create([
                'doc_id' => $doc->id,
                'prestation_id' => $prestationId ?: null,
                'dentiste' => $doc->name,
                'patient' => 'Patient Fev C',
                'numero_fiche' => 'MS-2026-02-C',
                'type_travail' => 'Onlay',
                'date_entree' => '2026-02-13',
                'date_livraison' => '2026-02-21',
                'prix_dhs' => 800,
                'statut' => Travail::STATUT_ANNULE,
            ]);

            DocSituationEncaissement::create([
                'doc_id' => $doc->id,
                'year' => 2026,
                'month' => 1,
                'montant' => 3000,
                'paid_on' => '2026-01-28',
            ]);
            DocSituationEncaissement::create([
                'doc_id' => $doc->id,
                'year' => 2026,
                'month' => 2,
                'montant' => 1000,
                'paid_on' => '2026-02-26',
            ]);
        });

        $this->command?->info('MonthlySituationTestSeeder done.');
        $this->command?->info('Test with DR-MONTH-TEST on 02/2026: situation=4 500, reçu=1 000, reste fin=3 500.');
    }
}
