<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('devis_invisalign_paiements')) {
            return;
        }

        Schema::create('devis_invisalign_paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devis_invisalign_id')->constrained('devis_invisalign')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->date('date_paiement');
            $table->decimal('montant', 14, 2);
            $table->string('type_paiement', 32)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devis_invisalign_paiements');
    }
};
