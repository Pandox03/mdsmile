<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travaux', function (Blueprint $table) {
            $table->foreignId('prestation_id')->nullable()->after('doc_id')->constrained('prestations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('travaux', function (Blueprint $table) {
            $table->dropForeign(['prestation_id']);
        });
    }
};
