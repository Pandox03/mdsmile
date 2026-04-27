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
        Schema::create('travaux', function (Blueprint $table) {
            $table->id();
            $table->string('dentiste');           // nom du dentiste (doc)
            $table->string('patient');
            $table->string('type_travail');       // e.g. Couronne Céramique, Bridge...
            $table->date('date_entree');
            $table->date('date_livraison');
            $table->date('date_essiage')->nullable(); // date d'essayage
            $table->decimal('prix_dhs', 12, 2)->default(0);
            $table->string('statut', 32)->default('en_attente'); // en_attente, en_cours, termine
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travaux');
    }
};
