<?php

// database/migrations/2026_01_01_000001_create_concursos_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('concursos', function (Blueprint $table) {
            $table->id();
            $table->integer('numero_concurso')->unique();
            $table->date('data_sorteio');
            $table->json('dezenas'); // [1, 2, 3, ..., 15]
            $table->integer('soma_dezenas');
            $table->integer('qtd_pares');
            $table->integer('qtd_impares');
            $table->integer('qtd_primos');
            $table->integer('qtd_fibonacci');
            $table->integer('repetidas_concurso_anterior')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concursos');
    }
};
