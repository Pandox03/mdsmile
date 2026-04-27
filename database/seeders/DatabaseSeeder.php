<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(PrestationSeeder::class);
        $this->call(DocSeeder::class);
        $this->call(StockSeeder::class);
        $this->call(TravailSeeder::class);
        $this->call(MonthlySituationTestSeeder::class);

        if (! User::where('email', 'manager@mdsmile.com')->exists()) {
            $manager = User::factory()->create([
                'name' => 'Manager Admin',
                'email' => 'manager@mdsmile.com',
                'password' => Hash::make('password'),
            ]);
            $manager->assignRole('manager');
        }
    }
}
