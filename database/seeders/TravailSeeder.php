<?php

namespace Database\Seeders;

use App\Models\Travail;
use Illuminate\Database\Seeder;

class TravailSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            ['dentiste' => 'Dr. Idrissi El Alami', 'patient' => 'Karim B.', 'type_travail' => 'Couronne Céramique (E-Max)', 'date_entree' => '2023-10-15', 'date_livraison' => '2023-10-22', 'date_essiage' => '2023-10-20', 'prix_dhs' => 1200, 'statut' => Travail::STATUT_TERMINE],
            ['dentiste' => 'Dr. Sofia Mernissi', 'patient' => 'Youssef T.', 'type_travail' => 'Facette Porcelaine (Zircone)', 'date_entree' => '2023-10-18', 'date_livraison' => '2023-10-25', 'date_essiage' => null, 'prix_dhs' => 1500, 'statut' => Travail::STATUT_EN_COURS],
            ['dentiste' => 'Dr. Ahmed Tazi', 'patient' => 'Leila Z.', 'type_travail' => 'Bridge Métallo-Céramique', 'date_entree' => '2023-10-20', 'date_livraison' => '2023-10-28', 'date_essiage' => null, 'prix_dhs' => 2100, 'statut' => Travail::STATUT_EN_ATTENTE],
            ['dentiste' => 'Dr. Rachid Berrada', 'patient' => 'Omar F.', 'type_travail' => 'Implants Dentaires', 'date_entree' => '2023-10-21', 'date_livraison' => '2023-11-05', 'date_essiage' => null, 'prix_dhs' => 4500, 'statut' => Travail::STATUT_EN_COURS],
            ['dentiste' => 'Dr. Fatima Zahra', 'patient' => 'Hajar M.', 'type_travail' => 'Couronne Tout Céramique', 'date_entree' => '2023-10-23', 'date_livraison' => '2023-10-30', 'date_essiage' => '2023-10-28', 'prix_dhs' => 1350, 'statut' => Travail::STATUT_TERMINE],
        ];

        foreach ($samples as $row) {
            Travail::firstOrCreate(
                [
                    'dentiste' => $row['dentiste'],
                    'patient' => $row['patient'],
                    'date_entree' => $row['date_entree'],
                ],
                $row
            );
        }
    }
}
