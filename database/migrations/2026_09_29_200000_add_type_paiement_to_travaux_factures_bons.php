<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travaux', function (Blueprint $table) {
            if (! Schema::hasColumn('travaux', 'paiement_statut')) {
                $table->string('paiement_statut', 32)->default('non_paye')->after('statut');
            }
            if (! Schema::hasColumn('travaux', 'type_paiement')) {
                $table->string('type_paiement', 32)->nullable()->after('paiement_statut');
            }
        });

        Schema::table('factures', function (Blueprint $table) {
            if (! Schema::hasColumn('factures', 'statut_paiement')) {
                $table->string('statut_paiement', 32)->default('non_payee')->after('date_facture');
            }
            if (! Schema::hasColumn('factures', 'type_paiement')) {
                $after = Schema::hasColumn('factures', 'statut_paiement') ? 'statut_paiement' : 'date_facture';
                $table->string('type_paiement', 32)->nullable()->after($after);
            }
        });

        Schema::table('bons_livraison', function (Blueprint $table) {
            if (! Schema::hasColumn('bons_livraison', 'type_paiement')) {
                $table->string('type_paiement', 32)->nullable()->after('paiement_statut');
            }
        });
    }

    public function down(): void
    {
        Schema::table('travaux', function (Blueprint $table) {
            if (Schema::hasColumn('travaux', 'type_paiement')) {
                $table->dropColumn('type_paiement');
            }
            if (Schema::hasColumn('travaux', 'paiement_statut')) {
                $table->dropColumn('paiement_statut');
            }
        });

        Schema::table('factures', function (Blueprint $table) {
            if (Schema::hasColumn('factures', 'type_paiement')) {
                $table->dropColumn('type_paiement');
            }
        });

        Schema::table('bons_livraison', function (Blueprint $table) {
            if (Schema::hasColumn('bons_livraison', 'type_paiement')) {
                $table->dropColumn('type_paiement');
            }
        });
    }
};
