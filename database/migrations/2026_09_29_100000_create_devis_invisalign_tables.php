<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devis_invisalign', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 32)->unique();
            $table->date('date_devis');
            $table->foreignId('doc_id')->nullable()->constrained('docs')->nullOnDelete();
            $table->string('client_nom', 255);
            $table->string('patient_nom', 255);
            $table->boolean('opt_maxillaire')->default(false);
            $table->boolean('opt_mandibulaire')->default(false);
            $table->boolean('taquets')->default(false);
            $table->boolean('stripping')->default(false);
            $table->text('conditions_paiement')->nullable();
            $table->text('banque_ligne')->nullable();
            $table->string('beneficiaire', 255)->nullable();
            $table->string('rib', 255)->nullable();
            $table->string('commandes_email', 255)->nullable();
            $table->text('societe_footer')->nullable();
            $table->decimal('total_ttc', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('devis_invisalign_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devis_invisalign_id')->constrained('devis_invisalign')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('designation', 500);
            $table->string('arcade', 255)->nullable();
            $table->decimal('prix_unitaire', 14, 2)->default(0);
            $table->decimal('total_ttc', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devis_invisalign_lignes');
        Schema::dropIfExists('devis_invisalign');
    }
};
