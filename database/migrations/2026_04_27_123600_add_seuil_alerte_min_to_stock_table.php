<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock', function (Blueprint $table) {
            $table->decimal('seuil_alerte_min', 12, 2)->default(5)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('stock', function (Blueprint $table) {
            $table->dropColumn('seuil_alerte_min');
        });
    }
};
