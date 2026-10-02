<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bons_livraison', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 32)->unique();
            $table->date('date_livraison');
            $table->foreignId('doc_id')->nullable()->constrained('docs')->nullOnDelete();
            $table->foreignId('travail_id')->nullable()->constrained('travaux')->nullOnDelete();
            $table->string('dentiste_nom', 255);
            $table->string('patient_nom', 255);
            $table->string('numero_fiche', 255)->nullable();
            $table->string('reference_travail', 64)->nullable();
            $table->text('details_travail')->nullable();
            $table->string('dents', 500)->nullable();
            $table->decimal('montant', 14, 2)->nullable();
            /** non_paye | partiel | paye */
            $table->string('paiement_statut', 32)->default('non_paye');
            $table->text('observations')->nullable();
            $table->string('recu_par', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bons_livraison');
    }
};
