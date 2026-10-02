<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travaux', function (Blueprint $table) {
            if (! Schema::hasColumn('travaux', 'date_fait')) {
                $table->date('date_fait')->nullable()->after('statut');
            }
        });

        // Existing finished cases: best available stand-in for the day they were marked done.
        if (Schema::hasColumn('travaux', 'date_fait')) {
            DB::table('travaux')
                ->whereIn('statut', ['termine', 'livrer'])
                ->whereNull('date_fait')
                ->update([
                    'date_fait' => DB::raw('DATE(updated_at)'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('travaux', function (Blueprint $table) {
            if (Schema::hasColumn('travaux', 'date_fait')) {
                $table->dropColumn('date_fait');
            }
        });
    }
};
