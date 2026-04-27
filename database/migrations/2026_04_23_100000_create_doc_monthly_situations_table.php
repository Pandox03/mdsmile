<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_monthly_situations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_id')->constrained('docs')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('montant_recu', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['doc_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_monthly_situations');
    }
};
