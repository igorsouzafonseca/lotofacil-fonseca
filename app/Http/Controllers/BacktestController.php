<?php

namespace App\Http\Controllers;

use App\Jobs\RunBacktestJob;
use App\Models\Concurso;
use App\Services\GameGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BacktestController extends Controller
{
    public function __construct(private GameGeneratorService $service) {}

    public function page(): View
    {
        return view('backtest.index', [
            'totalConcursos' => Concurso::count(),
            'primeiroConcurso' => Concurso::orderBy('numero_concurso')->value('numero_concurso'),
            'ultimoConcurso' => Concurso::orderByDesc('numero_concurso')->value('numero_concurso'),
        ]);
    }

    public function honesto(Request $request): JsonResponse
    {
        $data = $request->validate([
            'janela'  => ['nullable', 'integer', 'min:10', 'max:500'],
            'rodadas' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'jogos'   => ['nullable', 'integer', 'min:1', 'max:50'],
            'inicio'  => ['nullable', 'integer', 'min:1'],
            'candidatos_base' => ['nullable', 'integer', 'min:100', 'max:20000'],
        ]);

        $candidatosBase = (int) ($data['candidatos_base'] ?? 5000);
        $janela = $data['janela'] ?? 100;
        $rodadas = $data['rodadas'] ?? 100;
        $jogosPorRodada = $data['jogos'] ?? 10;

        $todos = Concurso::orderBy('numero_concurso')->get()->values();

        if ($todos->count() < $janela + 1) {
            return response()->json([
                'erro' => "Banco tem {$todos->count()} concursos; precisa de pelo menos " . ($janela + 1) . ".",
            ], 422);
        }

        $inicioIndice = $janela;
        if (!empty($data['inicio'])) {
            $idx = $todos->search(fn($c) => (int) $c->numero_concurso === (int) $data['inicio']);
            if ($idx === false) {
                return response()->json(['erro' => "Concurso {$data['inicio']} não encontrado."], 422);
            }
            $inicioIndice = $idx;
        }

        $fimIndice = min($inicioIndice + $rodadas, $todos->count());
        $realRodadas = $fimIndice - $inicioIndice;

        if ($realRodadas <= 0) {
            return response()->json(['erro' => 'Nenhuma rodada a executar com os parâmetros informados.'], 422);
        }

        $sistema = array_fill(0, 6, 0);
        $aleatorio = array_fill(0, 6, 0);
        $total = 0;

        for ($i = $inicioIndice; $i < $fimIndice; $i++) {
            $alvo = $todos[$i];
            $sorteadas = array_values(array_map('intval', (array) $alvo->dezenas));
            $numeroAlvo = (int) $alvo->numero_concurso;

            foreach ($this->service->generateDiverseForTarget($jogosPorRodada, $candidatosBase, $numeroAlvo) as $jogo) {
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
        }

        $probsTeoricas = [
            10 => 0.0325,
            11 => 1 / 11,
            12 => 1 / 60,
            13 => 1 / 692,
            14 => 1 / 21786,
            15 => 1 / 3268760,
        ];

        $faixas = [];
        foreach (range(10, 15) as $faixa) {
            $idx = $faixa - 10;
            $esperado = $total * $probsTeoricas[$faixa];
            $faixas[] = [
                'acertos' => $faixa,
                'sistema' => $sistema[$idx],
                'aleatorio' => $aleatorio[$idx],
                'esperado' => round($esperado, 2),
                'ratio_sistema' => $esperado > 0 ? round($sistema[$idx] / $esperado, 2) : null,
                'ratio_aleatorio' => $esperado > 0 ? round($aleatorio[$idx] / $esperado, 2) : null,
            ];
        }

        return response()->json([
            'aviso' => 'Backtest honesto: sem vazamento de dados.',
            'params' => [
                'janela' => $janela,
                'rodadas' => $realRodadas,
                'jogos_por_rodada' => $jogosPorRodada,
                'inicio' => $todos[$inicioIndice]->numero_concurso,
                'fim' => $todos[$fimIndice - 1]->numero_concurso,
            ],
            'total_jogos' => $total,
            'faixas' => $faixas,
            'resumo' => [
                'sistema_maior_ou_igual_11' => array_sum(array_slice($sistema, 1)),
                'aleatorio_maior_ou_igual_11' => array_sum(array_slice($aleatorio, 1)),
            ],
        ]);
    }

    public function rapido(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rodadas' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'jogos'   => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $rodadas = $data['rodadas'] ?? 50;
        $jogosPorRodada = $data['jogos'] ?? 10;

        $concursos = Concurso::orderByDesc('numero_concurso')->limit($rodadas)->get();

        if ($concursos->count() < $rodadas) {
            return response()->json([
                'erro' => "Só há {$concursos->count()} concursos; pedido {$rodadas}.",
            ], 422);
        }

        $sistema = array_fill(0, 6, 0);
        $aleatorio = array_fill(0, 6, 0);
        $total = 0;

        foreach ($concursos as $alvo) {
            $sorteadas = array_values(array_map('intval', (array) $alvo->dezenas));

            foreach ($this->service->generateDiverse($jogosPorRodada) as $jogo) {
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
        }

        $faixas = [];
        foreach (range(10, 15) as $faixa) {
            $idx = $faixa - 10;
            $faixas[] = [
                'acertos' => $faixa,
                'sistema' => $sistema[$idx],
                'aleatorio' => $aleatorio[$idx],
            ];
        }

        return response()->json([
            'aviso' => 'Backtest rápido tem vazamento de dados; use apenas para smoke test.',
            'params' => [
                'rodadas' => $concursos->count(),
                'jogos_por_rodada' => $jogosPorRodada,
            ],
            'total_jogos' => $total,
            'faixas' => $faixas,
            'resumo' => [
                'sistema_maior_ou_igual_11' => array_sum(array_slice($sistema, 1)),
                'aleatorio_maior_ou_igual_11' => array_sum(array_slice($aleatorio, 1)),
            ],
        ]);
    }

    public function enqueue(Request $request): JsonResponse
    {
        $data = $request->validate([
            'janela'  => ['nullable', 'integer', 'min:10', 'max:500'],
            'rodadas' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'jogos'   => ['nullable', 'integer', 'min:1', 'max:50'],
            'inicio'  => ['nullable', 'integer', 'min:1'],
            'candidatos_base' => ['nullable', 'integer', 'min:100', 'max:20000'],
        ]);

        $jobId = (string) Str::uuid();

        RunBacktestJob::dispatch(
            $jobId,
            $data['janela'] ?? 100,
            $data['rodadas'] ?? 100,
            $data['jogos'] ?? 10,
            $data['candidatos_base'] ?? 5000,
            $data['inicio'] ?? null
        );

        Cache::put("backtest:{$jobId}:status", [
            'status' => 'queued', 'progresso' => 0, 'erro' => null,
        ], now()->addHours(2));

        return response()->json(['job_id' => $jobId]);
    }

    public function status(string $jobId): JsonResponse
    {
        $st = Cache::get("backtest:{$jobId}:status");
        if (!$st) {
            return response()->json(['erro' => 'Job não encontrado ou expirado.'], 404);
        }
        return response()->json($st);
    }

    public function resultado(string $jobId): JsonResponse
    {
        $res = Cache::get("backtest:{$jobId}:resultado");
        if (!$res) {
            return response()->json(['erro' => 'Resultado ainda não disponível.'], 404);
        }
        return response()->json($res);
    }
}
