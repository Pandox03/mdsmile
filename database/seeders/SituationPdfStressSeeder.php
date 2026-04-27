<?php

namespace Database\Seeders;

use App\Models\Doc;
use App\Models\Prestation;
use App\Models\Travail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed a heavy month of travaux to stress-test situation PDF pagination/styles.
 *
 * Usage:
 * php artisan db:seed --class=SituationPdfStressSeeder
 */
class SituationPdfStressSeeder extends Seeder
{
    public function run(): void
    {
        $doc = Doc::firstOrCreate(
            ['numero_registration' => 'DR-PDF-STRESS'],
            [
                'name' => 'Dr. PDF Stress Test',
                'phone' => '+212 5 00 77 66',
                'email' => 'pdf-stress@mdsmile.local',
                'adresse' => 'Casablanca',
            ]
        );

        if (Travail::where('numero_fiche', 'SPDF-2026-03-001')->exists()) {
            $this->command?->info('SituationPdfStressSeeder: stress data already exists.');

            return;
        }

        $prestationId = (int) (Prestation::query()->orderBy('id')->value('id') ?? 0);

        DB::transaction(function () use ($doc, $prestationId) {
            // 65 travaux in March 2026 => forces multi-page PDF.
            for ($i = 1; $i <= 65; $i++) {
                $day = (($i - 1) % 28) + 1;
                $dateEntree = sprintf('2026-03-%02d', $day);
                $dateLivraison = sprintf('2026-03-%02d', min(28, $day + 6));

                // Mix statuses to verify "annule/a_refaire => 0 DHS" rule.
                $statut = Travail::STATUT_TERMINE;
                if ($i % 10 === 0) {
                    $statut = Travail::STATUT_ANNULE;
                } elseif ($i % 9 === 0) {
                    $statut = Travail::STATUT_A_REFAIRE;
                } elseif ($i % 4 === 0) {
                    $statut = Travail::STATUT_EN_COURS;
                }

                $amount = 900 + (($i % 7) * 150); // between ~900 and 1800

                Travail::create([
                    'doc_id' => $doc->id,
                    'prestation_id' => $prestationId ?: null,
                    'dentiste' => $doc->name,
                    'patient' => 'Patient Stress ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'numero_fiche' => 'SPDF-2026-03-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'type_travail' => 'Test pagination PDF',
                    'date_entree' => $dateEntree,
                    'date_livraison' => $dateLivraison,
                    'date_essiage' => null,
                    'prix_dhs' => $amount,
                    'statut' => $statut,
                ]);
            }
        });

        $this->command?->info('SituationPdfStressSeeder done.');
        $this->command?->info('Use doc DR-PDF-STRESS with month=3, year=2026 to test multi-page PDF.');
    }
}

