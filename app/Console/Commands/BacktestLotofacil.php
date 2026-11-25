<?php

namespace App\Console\Commands;

use App\Models\Concurso;
use App\Services\GameGeneratorService;
use Illuminate\Console\Command;

class BacktestLotofacil extends Command
{
    protected $signature = 'lotofacil:backtest
                            {--rodadas=100}
                            {--jogos=10}';

    protected $description = 'Smoke test rápido (COM vazamento — use só para checagem de sanidade)';

    public function handle(GameGeneratorService $service): int
    {
        $rodadas = (int) $this->option('rodadas');
        $jogosPorRodada = (int) $this->option('jogos');

        $concursos = Concurso::orderByDesc('numero_concurso')->limit($rodadas)->get();

        if ($concursos->count() < $rodadas) {
            $this->error("Só há {$concursos->count()} concursos; pedido {$rodadas}.");
            return self::FAILURE;
        }

        $this->warn('ATENÇÃO: este comando tem vazamento de dados. Use apenas para smoke test.');
        $bar = $this->output->createProgressBar($concursos->count());
        $bar->start();

        $sistema = array_fill(0, 6, 0);
        $aleatorio = array_fill(0, 6, 0);
        $total = 0;

        foreach ($concursos as $alvo) {
            $sorteadas = $alvo->dezenas;

            foreach ($service->generateDiverse($jogosPorRodada) as $jogo) {
                $acertos = count(array_intersect($jogo['dezenas'], $sorteadas));
                if ($acertos >= 10) $sistema[$acertos - 10]++;
            }

            for ($k = 0; $k < $jogosPorRodada; $k++) {
                $pool = range(1, 25);
                shuffle($pool);
                $acertos = count(array_intersect(array_slice($pool, 0, 15), $sorteadas));
                if ($acertos >= 10) $aleatorio[$acertos - 10]++;
            }

            $total += $jogosPorRodada;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $linhas = [];
        foreach (range(10, 15) as $faixa) {
            $idx = $faixa - 10;
            $linhas[] = [$faixa, $sistema[$idx], $aleatorio[$idx]];
        }

        $this->table(['Acertos exatos', 'Sistema', 'Aleatório'], $linhas);
        $this->line("Total de jogos: {$total}");

        return self::SUCCESS;
    }
}
