<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_prestation_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_id')->constrained('docs')->cascadeOnDelete();
            $table->foreignId('prestation_id')->constrained('prestations')->cascadeOnDelete();
            $table->decimal('price', 12, 2)->nullable()->comment('Null = Sur devis for this client');
            $table->timestamps();
            $table->unique(['doc_id', 'prestation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_prestation_prices');
    }
};
