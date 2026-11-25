<?php

// database/migrations/2026_01_01_000002_create_generated_games_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_games', function (Blueprint $table) {
            $table->id();
            $table->json('dezenas');
            $table->decimal('score_total', 8, 4);
            $table->decimal('score_frequencia', 8, 4)->nullable();
            $table->decimal('score_ciclo', 8, 4)->nullable();
            $table->decimal('score_repeticao', 8, 4)->nullable();
            $table->decimal('score_soma', 8, 4)->nullable();
            $table->decimal('score_distribuicao', 8, 4)->nullable();
            $table->string('estrategia')->default('hibrida');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_games');
    }
};
