<?php

namespace Database\Seeders;

use App\Models\Stock;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            ['name' => 'Zircone E-max', 'reference' => 'ZIR-EMAX', 'quantity' => 50, 'unite' => 'pce'],
            ['name' => 'Céramique feldspathique', 'reference' => 'CER-FELD', 'quantity' => 30, 'unite' => 'pce'],
            ['name' => 'Métal coulé', 'reference' => 'MET-COU', 'quantity' => 20, 'unite' => 'pce'],
            ['name' => 'Bridge 3 unités zircone', 'reference' => 'BRI-ZIR3', 'quantity' => 15, 'unite' => 'pce'],
            ['name' => 'Inlay/Onlay composite', 'reference' => 'INL-COM', 'quantity' => 40, 'unite' => 'pce'],
            ['name' => 'Prothèse complète acrylique', 'reference' => 'PRO-ACR', 'quantity' => 25, 'unite' => 'pce'],
        ];

        foreach ($materials as $row) {
            Stock::firstOrCreate(
                ['reference' => $row['reference']],
                $row
            );
        }
    }
}
