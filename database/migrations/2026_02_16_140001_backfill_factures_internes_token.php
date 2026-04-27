<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $factures = DB::table('factures')->whereNull('internes_token')->get();
        foreach ($factures as $f) {
            DB::table('factures')->where('id', $f->id)->update([
                'internes_token' => Str::random(48),
            ]);
        }
    }

    public function down(): void
    {
        // no-op
    }
};
