<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facture_travail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->cascadeOnDelete();
            $table->foreignId('travail_id')->constrained('travaux')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['facture_id', 'travail_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facture_travail');
    }
};
