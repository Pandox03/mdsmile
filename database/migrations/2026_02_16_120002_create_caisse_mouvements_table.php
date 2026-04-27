<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('caisse_mouvements')) {
            return;
        }
        Schema::create('caisse_mouvements', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16); // entree | sortie
            $table->decimal('montant', 12, 2);
            $table->date('date_mouvement');
            $table->string('description')->nullable();
            $table->foreignId('facture_id')->nullable()->constrained('factures')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caisse_mouvements');
    }
};
