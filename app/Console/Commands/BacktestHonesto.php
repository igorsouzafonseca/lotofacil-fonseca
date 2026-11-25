<?php

namespace App\Console\Commands;

use App\Models\Concurso;
use App\Services\GameGeneratorService;
use Illuminate\Console\Command;

class BacktestHonesto extends Command
{
    protected $signature = 'lotofacil:backtest-honesto
                            {--janela=100}
                            {--rodadas=200}
                            {--jogos=10}
                            {--candidatos=5000}
                            {--inicio=}';

    protected $description = 'Backtest honesto (sem vazamento de dados)';

    private const PROBS_TEORICAS = [
        10 => 0.0325,
        11 => 1 / 11,
        12 => 1 / 60,
        13 => 1 / 692,
        14 => 1 / 21786,
        15 => 1 / 3268760,
    ];

    public function handle(GameGeneratorService $service): int
    {
        $janela = (int) $this->option('janela');
        $rodadas = (int) $this->option('rodadas');
        $jogosPorRodada = (int) $this->option('jogos');
        $candidatosBase = (int) $this->option('candidatos');

        $todos = Concurso::orderBy('numero_concurso')->get()->values();

        if ($todos->count() < $janela + 1) {
            $this->error("Banco tem {$todos->count()} concursos; precisa de pelo menos " . ($janela + 1) . ".");
            return self::FAILURE;
        }

        $inicioIndice = $janela;
        if ($this->option('inicio')) {
            $inicio = (int) $this->option('inicio');
            $idx = $todos->search(fn($c) => (int) $c->numero_concurso === $inicio);
            if ($idx === false) {
                $this->error("Concurso {$inicio} não encontrado.");
                return self::FAILURE;
            }
            $inicioIndice = $idx;
        }

        $fimIndice = min($inicioIndice + $rodadas, $todos->count());
        $realRodadas = $fimIndice - $inicioIndice;

        if ($realRodadas <= 0) {
            $this->error('Nenhuma rodada a executar.');
            return self::FAILURE;
        }

        $this->info("Backtest honesto: {$realRodadas} rodadas × {$jogosPorRodada} jogos (janela={$janela}, candidatos={$candidatosBase})");

        $sistema = array_fill(0, 6, 0);
        $aleatorio = array_fill(0, 6, 0);
        $total = 0;

        $bar = $this->output->createProgressBar($realRodadas);
        $bar->start();

        for ($i = $inicioIndice; $i < $fimIndice; $i++) {
            $alvo = $todos[$i];
            $sorteadas = array_values(array_map('intval', (array) $alvo->dezenas));
            $numeroAlvo = (int) $alvo->numero_concurso;

            foreach ($service->generateDiverseForTarget($jogosPorRodada, $candidatosBase, $numeroAlvo) as $jogo) {
                $dezenasJogo = array_values(array_map('intval', (array) $jogo['dezenas']));
                $acertos = count(array_intersect($dezenasJogo, $sorteadas));
                if ($acertos >= 10 && $acertos <= 15) $sistema[$acertos - 10]++;
            }

            for ($k = 0; $k < $jogosPorRodada; $k++) {
                $pool = range(1, 25);
                shuffle($pool);
                $acertos = count(array_intersect(array_slice($pool, 0, 15), $sorteadas));
                if ($acertos >= 10 && $acertos <= 15) $aleatorio[$acertos - 10]++;
            }

            $total += $jogosPorRodada;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $linhas = [];
        foreach (range(10, 15) as $faixa) {
            $idx = $faixa - 10;
            $s = $sistema[$idx];
            $a = $aleatorio[$idx];
            $esp = round($total * self::PROBS_TEORICAS[$faixa], 2);

            $linhas[] = [
                "= {$faixa}",
                $s,
                $a,
                $esp,
                $esp > 0 ? number_format($s / $esp, 2) . '×' : '-',
                $esp > 0 ? number_format($a / $esp, 2) . '×' : '-',
            ];
        }

        $this->table(
            ['Acertos', 'Sistema', 'Aleatório', 'Esperado', 'Sist./Esp.', 'Aleat./Esp.'],
            $linhas
        );

        $somaSist = array_sum(array_slice($sistema, 1));
        $somaAle = array_sum(array_slice($aleatorio, 1));
        $this->newLine();
        $this->line("Total de jogos: {$total}");
        $this->line("Jogos com ≥ 11 — Sistema: {$somaSist} | Aleatório: {$somaAle}");

        return self::SUCCESS;
    }
}
