<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Normalize empty strings so they don't block unique index creation.
        DB::table('travaux')->where('numero_fiche', '')->update(['numero_fiche' => null]);

        // Resolve existing duplicates by keeping the oldest row unchanged
        // and suffixing the others with their ID (e.g. 234-57).
        $duplicates = DB::table('travaux')
            ->select('numero_fiche')
            ->whereNotNull('numero_fiche')
            ->groupBy('numero_fiche')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('numero_fiche');

        foreach ($duplicates as $numeroFiche) {
            $rows = DB::table('travaux')
                ->where('numero_fiche', $numeroFiche)
                ->orderBy('id')
                ->get(['id']);

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue;
                }

                $newNumero = mb_substr($numeroFiche . '-' . $row->id, 0, 255);
                DB::table('travaux')->where('id', $row->id)->update(['numero_fiche' => $newNumero]);
            }
        }

        Schema::table('travaux', function (Blueprint $table) {
            $table->unique('numero_fiche', 'travaux_numero_fiche_unique');
        });
    }

    public function down(): void
    {
        Schema::table('travaux', function (Blueprint $table) {
            $table->dropUnique('travaux_numero_fiche_unique');
        });
    }
};
