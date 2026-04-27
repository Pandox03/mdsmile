<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facture_travail', function (Blueprint $table) {
            $table->decimal('prix_comptabilise', 12, 2)->nullable()->after('travail_id');
        });

        Schema::table('factures', function (Blueprint $table) {
            $table->string('internes_token', 64)->nullable()->unique()->after('date_facture');
        });

        Schema::table('travaux', function (Blueprint $table) {
            $table->decimal('prix_comptabilise', 12, 2)->nullable()->after('prix_dhs');
        });
    }

    public function down(): void
    {
        Schema::table('facture_travail', fn (Blueprint $t) => $t->dropColumn('prix_comptabilise'));
        Schema::table('factures', fn (Blueprint $t) => $t->dropColumn('internes_token'));
        Schema::table('travaux', fn (Blueprint $t) => $t->dropColumn('prix_comptabilise'));
    }
};
