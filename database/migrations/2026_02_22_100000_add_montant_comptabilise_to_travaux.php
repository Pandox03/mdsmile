<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travaux', function (Blueprint $table) {
            $table->decimal('montant_comptabilise', 12, 2)->nullable()->after('prix_dhs');
        });
    }

    public function down(): void
    {
        Schema::table('travaux', function (Blueprint $table) {
            $table->dropColumn('montant_comptabilise');
        });
    }
};
