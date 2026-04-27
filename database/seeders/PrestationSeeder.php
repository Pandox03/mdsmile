<?php

namespace Database\Seeders;

use App\Models\Prestation;
use App\Models\PrestationCategory;
use Illuminate\Database\Seeder;

class PrestationSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Couronnes', 'order' => 1],
            ['name' => 'Bridges', 'order' => 2],
            ['name' => 'Prothèses amovibles', 'order' => 3],
            ['name' => 'Châssis', 'order' => 4],
            ['name' => 'Implants', 'order' => 5],
            ['name' => 'Divers', 'order' => 6],
        ];

        foreach ($categories as $cat) {
            PrestationCategory::firstOrCreate(
                ['name' => $cat['name']],
                ['order' => $cat['order']]
            );
        }

        $prestations = [
            // Couronnes
            ['Couronnes', 'Couronne céramique', 2500],
            ['Couronnes', 'Couronne métal-céramique', 2000],
            ['Couronnes', 'Couronne Zircone', 3000],
            ['Couronnes', 'Couronne E-max', 2800],
            ['Couronnes', 'Inlay-core', 800],
            // Bridges
            ['Bridges', 'Bridge 3 éléments céramique', 7500],
            ['Bridges', 'Bridge 3 éléments métal-céramique', 6000],
            ['Bridges', 'Bridge 4 éléments', 10000],
            ['Bridges', 'Bridge Zircone', 9000],
            // Prothèses amovibles
            ['Prothèses amovibles', 'Prothèse complète résine', 3500],
            ['Prothèses amovibles', 'Prothèse partielle résine', 2500],
            ['Prothèses amovibles', 'Prothèse squelettique', 4500],
            ['Prothèses amovibles', 'Prothèse complète avec crochets', 4000],
            // Châssis
            ['Châssis', 'Châssis métallique', 1500],
            ['Châssis', 'Châssis Co-Cr', 2000],
            ['Châssis', 'Essayage châssis', null],
            ['Châssis', 'Finition prothèse', 500],
            // Implants
            ['Implants', 'Pilon implantaire', 1200],
            ['Implants', 'Couronne sur implant', 3500],
            ['Implants', 'Bridge sur implants', null],
            // Divers
            ['Divers', 'Divers travaux de prothèse dentaire', null],
            ['Divers', 'Réparation prothèse', 400],
            ['Divers', 'Régénération', 300],
        ];

        $orderByCategory = [];
        foreach ($prestations as $i => $row) {
            [$catName, $name, $price] = $row;
            $category = PrestationCategory::where('name', $catName)->first();
            if (! $category) {
                continue;
            }
            if (! isset($orderByCategory[$catName])) {
                $orderByCategory[$catName] = 0;
            }
            $orderByCategory[$catName]++;

            Prestation::firstOrCreate(
                [
                    'prestation_category_id' => $category->id,
                    'name' => $name,
                ],
                [
                    'price' => $price,
                    'order' => $orderByCategory[$catName],
                ]
            );
        }
    }
}
