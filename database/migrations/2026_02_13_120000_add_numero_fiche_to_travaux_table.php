<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('travaux') || Schema::hasColumn('travaux', 'numero_fiche')) {
            return;
        }

        Schema::table('travaux', function (Blueprint $table) {
            $table->string('numero_fiche', 255)->nullable()->after('patient');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('travaux') || !Schema::hasColumn('travaux', 'numero_fiche')) {
            return;
        }

        Schema::table('travaux', function (Blueprint $table) {
            $table->dropColumn('numero_fiche');
        });
    }
};
