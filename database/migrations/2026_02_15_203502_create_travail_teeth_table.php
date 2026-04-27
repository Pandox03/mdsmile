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
        Schema::create('travail_teeth', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travail_id')->constrained('travaux')->cascadeOnDelete();
            $table->unsignedTinyInteger('tooth_number'); // 1-32 (upper 1-16, lower 17-32)
            $table->foreignId('stock_id')->constrained('stock')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->timestamps();
            $table->unique(['travail_id', 'tooth_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travail_teeth');
    }
};
