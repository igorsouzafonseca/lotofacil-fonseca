<?php

namespace App\Jobs;

use App\Models\Concurso;
use App\Services\GameGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class RunBacktestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(
        public string $jobId,
        public int $janela,
        public int $rodadas,
        public int $jogosPorRodada,
        public int $candidatosBase = 5000,
        public ?int $inicio = null
    ) {}

    public function handle(GameGeneratorService $service): void
    {
        $this->atualizarStatus('running', 0);

        $todos = Concurso::orderBy('numero_concurso')->get()->values();
        $inicioIndice = $this->janela;

        if ($this->inicio !== null) {
            $idx = $todos->search(fn($c) => (int) $c->numero_concurso === $this->inicio);
            if ($idx === false) {
                $this->atualizarStatus('error', 0, "Concurso {$this->inicio} não encontrado.");
                return;
            }
            $inicioIndice = $idx;
        }

        $fimIndice = min($inicioIndice + $this->rodadas, $todos->count());
        $realRodadas = max(0, $fimIndice - $inicioIndice);

        if ($realRodadas <= 0) {
            $this->atualizarStatus('error', 0, 'Nenhuma rodada a executar.');
            return;
        }

        $sistema = array_fill(0, 6, 0);
        $aleatorio = array_fill(0, 6, 0);
        $total = 0;

        for ($i = $inicioIndice; $i < $fimIndice; $i++) {
            $alvo = $todos[$i];
            $sorteadas = array_values(array_map('intval', (array) $alvo->dezenas));
            $numeroAlvo = (int) $alvo->numero_concurso;

            foreach ($service->generateDiverseForTarget($this->jogosPorRodada, $this->candidatosBase, $numeroAlvo) as $jogo) {
                $dezenasJogo = array_values(array_map('intval', (array) $jogo['dezenas']));
                $acertos = count(array_intersect($dezenasJogo, $sorteadas));
                if ($acertos >= 10 && $acertos <= 15) {
                    $sistema[$acertos - 10]++;
                }
            }

            for ($k = 0; $k < $this->jogosPorRodada; $k++) {
                $pool = range(1, 25);
                shuffle($pool);
                $jogoAleatorio = array_slice($pool, 0, 15);
                $acertos = count(array_intersect($jogoAleatorio, $sorteadas));
                if ($acertos >= 10 && $acertos <= 15) {
                    $aleatorio[$acertos - 10]++;
                }
            }

            $total += $this->jogosPorRodada;

            $progresso = (int) round(($i - $inicioIndice + 1) / $realRodadas * 100);
            if ($progresso % 5 === 0) {
                $this->atualizarStatus('running', $progresso);
            }
        }

        $probs = [
            10 => 0.2315, 11 => 1 / 11, 12 => 1 / 60,
            13 => 1 / 692, 14 => 1 / 21786, 15 => 1 / 3268760,
        ];

        $faixas = [];
        foreach (range(10, 15) as $f) {
            $idx = $f - 10;
            $esp = $total * $probs[$f];
            $faixas[] = [
                'acertos' => $f,
                'sistema' => $sistema[$idx],
                'aleatorio' => $aleatorio[$idx],
                'esperado' => round($esp, 2),
                'ratio_sistema' => $esp > 0 ? round($sistema[$idx] / $esp, 2) : null,
                'ratio_aleatorio' => $esp > 0 ? round($aleatorio[$idx] / $esp, 2) : null,
            ];
        }

        $resultado = [
            'params' => [
                'janela' => $this->janela,
                'rodadas' => $realRodadas,
                'jogos_por_rodada' => $this->jogosPorRodada,
                'inicio' => $todos[$inicioIndice]->numero_concurso,
                'fim' => $todos[$fimIndice - 1]->numero_concurso,
            ],
            'total_jogos' => $total,
            'faixas' => $faixas,
            'resumo' => [
                'sistema_maior_ou_igual_11' => array_sum(array_slice($sistema, 1)),
                'aleatorio_maior_ou_igual_11' => array_sum(array_slice($aleatorio, 1)),
            ],
        ];

        Cache::put("backtest:{$this->jobId}:resultado", $resultado, now()->addHours(2));
        $this->atualizarStatus('done', 100);
    }

    private function atualizarStatus(string $status, int $progresso, ?string $erro = null): void
    {
        Cache::put("backtest:{$this->jobId}:status", [
            'status' => $status,
            'progresso' => $progresso,
            'erro' => $erro,
        ], now()->addHours(2));
    }
}
