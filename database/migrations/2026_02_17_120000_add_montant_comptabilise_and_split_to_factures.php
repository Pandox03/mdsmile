<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->decimal('montant_comptabilise', 12, 2)->nullable()->after('statut_paiement');
            $table->json('split_montants')->nullable()->after('montant_comptabilise');
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['montant_comptabilise', 'split_montants']);
        });
    }
};
