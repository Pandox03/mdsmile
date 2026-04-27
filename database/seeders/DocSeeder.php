<?php

namespace Database\Seeders;

use App\Models\Doc;
use Illuminate\Database\Seeder;

class DocSeeder extends Seeder
{
    public function run(): void
    {
        $docs = [
            ['numero_registration' => 'DR-001', 'name' => 'Dr. Idrissi El Alami', 'phone' => '+212 5 00 00 01', 'email' => 'idrissi@cabinet.ma', 'adresse' => 'Casablanca'],
            ['numero_registration' => 'DR-002', 'name' => 'Dr. Sofia Mernissi', 'phone' => '+212 5 00 00 02', 'email' => 'sofia@cabinet.ma', 'adresse' => 'Rabat'],
            ['numero_registration' => 'DR-003', 'name' => 'Dr. Ahmed Tazi', 'phone' => '+212 5 00 00 03', 'email' => 'tazi@cabinet.ma', 'adresse' => 'Fès'],
            ['numero_registration' => 'DR-004', 'name' => 'Dr. Rachid Berrada', 'phone' => '+212 5 00 00 04', 'email' => 'berrada@cabinet.ma', 'adresse' => 'Marrakech'],
            ['numero_registration' => 'DR-005', 'name' => 'Dr. Fatima Zahra', 'phone' => '+212 5 00 00 05', 'email' => 'fatima@cabinet.ma', 'adresse' => 'Tanger'],
        ];

        foreach ($docs as $row) {
            Doc::firstOrCreate(
                ['numero_registration' => $row['numero_registration']],
                $row
            );
        }
    }
}
