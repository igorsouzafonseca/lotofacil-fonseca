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
        Schema::create('importacaos', function (Blueprint $table) {
            $table->id();
            $table->string('arquivo_original');          // nome do arquivo enviado
            $table->string('arquivo_path');              // caminho no storage
            $table->enum('status', ['pendente', 'processando', 'concluido', 'falhou'])
                ->default('pendente');
            $table->integer('linhas_processadas')->default(0);
            $table->integer('linhas_importadas')->default(0);
            $table->text('mensagem_erro')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('finalizado_em')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('importacaos');
    }
};
