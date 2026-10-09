<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devis_invisalign_lignes', function (Blueprint $table) {
            if (! Schema::hasColumn('devis_invisalign_lignes', 'quantite')) {
                $table->decimal('quantite', 10, 2)->default(1)->after('designation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('devis_invisalign_lignes', function (Blueprint $table) {
            if (Schema::hasColumn('devis_invisalign_lignes', 'quantite')) {
                $table->dropColumn('quantite');
            }
        });
    }
};
