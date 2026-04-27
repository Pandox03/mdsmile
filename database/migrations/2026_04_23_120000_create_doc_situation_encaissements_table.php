<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_situation_encaissements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_id')->constrained('docs')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('montant', 12, 2);
            $table->date('paid_on');
            $table->timestamps();

            $table->index(['doc_id', 'year', 'month']);
        });

        if (Schema::hasTable('doc_monthly_situations')) {
            $rows = DB::table('doc_monthly_situations')->get();
            foreach ($rows as $row) {
                $amount = (float) ($row->montant_recu ?? 0);
                if ($amount <= 0) {
                    continue;
                }
                $y = (int) $row->year;
                $m = (int) $row->month;
                $paidOn = \Carbon\Carbon::create($y, $m, 1)->endOfMonth()->toDateString();
                DB::table('doc_situation_encaissements')->insert([
                    'doc_id' => $row->doc_id,
                    'year' => $y,
                    'month' => $m,
                    'montant' => $amount,
                    'paid_on' => $paidOn,
                    'created_at' => $row->updated_at ?? $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? $row->created_at ?? now(),
                ]);
            }
            Schema::drop('doc_monthly_situations');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_situation_encaissements');
    }
};
