<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('travail_teeth', 'phase')) {
            Schema::table('travail_teeth', function (Blueprint $table) {
                $table->unsignedTinyInteger('phase')->default(1)->after('prestation_id');
            });
        }

        $indexes = Schema::getIndexes('travail_teeth');
        $indexNames = collect($indexes)->pluck('name')->all();
        $hasNewUnique = in_array('travail_teeth_travail_id_tooth_number_phase_unique', $indexNames);
        $hasOldUnique = in_array('travail_teeth_travail_id_tooth_number_unique', $indexNames);

        if (! $hasNewUnique) {
            Schema::table('travail_teeth', function (Blueprint $table) {
                $table->unique(['travail_id', 'tooth_number', 'phase']);
            });
        }
        if ($hasOldUnique) {
            Schema::table('travail_teeth', function (Blueprint $table) {
                $table->dropUnique(['travail_id', 'tooth_number']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('travail_teeth', function (Blueprint $table) {
            $table->dropUnique(['travail_id', 'tooth_number', 'phase']);
            $table->unique(['travail_id', 'tooth_number']);
        });
        Schema::table('travail_teeth', function (Blueprint $table) {
            $table->dropColumn('phase');
        });
    }
};
