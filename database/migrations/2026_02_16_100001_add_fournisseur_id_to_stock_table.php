<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock', function (Blueprint $table) {
            $table->foreignId('fournisseur_id')->nullable()->after('unite')->constrained('fournisseurs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock', function (Blueprint $table) {
            $table->dropForeign(['fournisseur_id']);
        });
    }
};
