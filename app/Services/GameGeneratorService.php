<?php

namespace App\Services;

use App\Models\Concurso;
use App\Models\GeneratedGame;

class GameGeneratorService
{
    public function __construct(
        private FrequencyAnalyzer $frequency,
        private CycleAnalyzer $cycle,
        private PatternFilter $filter
    ) {}

    private function weights(): array
    {
        return [
            'soma'       => 0.20,
            'pares'      => 0.15,
            'faixas'     => 0.15,
            'primos'     => 0.10,
            'fibonacci'  => 0.10,
            'sequencia'  => 0.10,
            'frequencia' => 0.10,
            'ciclo'      => 0.10,
        ];
    }

    public function generateDiverse(int $quantidade = 10, int $candidatosBase = 5000): array
    {
        return $this->generateDiverseForTarget($quantidade, $candidatosBase, null);
    }

    public function generateDiverseForTarget(
        int $quantidade = 10,
        int $candidatosBase = 5000,
        ?int $ateConcurso = null
    ): array {
        $weights = $this->weights();

        $lifts = $this->frequency->getThermalLift(30, $ateConcurso);
        $emRetorno = $this->cycle->getDezenasEmRetorno($ateConcurso);

        $ultimoQuery = Concurso::orderByDesc('numero_concurso');
        if ($ateConcurso !== null) {
            $ultimoQuery->where('numero_concurso', '<', $ateConcurso);
        }
        $ultimoConcurso = $ultimoQuery->first();

        $candidatos = $this->filter->generateCandidates($candidatosBase);

        $pontuados = [];
        foreach ($candidatos as $dezenas) {
            $scoreBase = $this->filter->scoreGame($dezenas, $weights);

            $bonusHot = 0.0;
            foreach ($dezenas as $d) {
                if (($lifts[$d] ?? 1) > 1.2) {
                    $bonusHot += 0.02;
                }
            }

            $bonusCiclo = 0.0;
            foreach ($dezenas as $d) {
                if (in_array($d, $emRetorno, true)) {
                    $bonusCiclo += 0.03;
                }
            }

            $bonusRepeticao = 0.0;
            if ($ultimoConcurso) {
                $repetidas = count(array_intersect($dezenas, (array) $ultimoConcurso->dezenas));
                $bonusRepeticao = max(0, 1 - (abs($repetidas - 9) / 6)) * 0.10;
            }

            $scoreTotal = $scoreBase + $bonusHot + $bonusCiclo + $bonusRepeticao;

            $pontuados[] = [
                'dezenas'          => $dezenas,
                'score_total'      => round($scoreTotal, 4),
                'score_frequencia' => round($bonusHot, 4),
                'score_ciclo'      => round($bonusCiclo, 4),
                'score_repeticao'  => round($bonusRepeticao, 4),
                'score_soma'       => round(
                    $weights['soma'] * max(0, 1 - (abs(array_sum($dezenas) - 195) / 100)),
                    4
                ),
            ];
        }

        usort($pontuados, fn($a, $b) => $b['score_total'] <=> $a['score_total']);

        return $this->selecionarDiversos($pontuados, $quantidade);
    }

    private function selecionarDiversos(array $pontuados, int $quantidade): array
    {
        $selecionados = [];
        $pool = $pontuados;

        while (count($selecionados) < $quantidade && !empty($pool)) {
            $melhorIdx = null;
            $melhorScoreAjustado = -INF;

            foreach ($pool as $i => $cand) {
                $penalidade = 0.0;
                foreach ($selecionados as $sel) {
                    $overlap = count(array_intersect($cand['dezenas'], $sel['dezenas']));
                    if ($overlap > 10) {
                        $penalidade += ($overlap - 10) * 0.5;
                    }
                }
                $scoreAjustado = $cand['score_total'] - $penalidade;

                if ($scoreAjustado > $melhorScoreAjustado) {
                    $melhorScoreAjustado = $scoreAjustado;
                    $melhorIdx = $i;
                }
            }

            $selecionados[] = $pool[$melhorIdx];
            unset($pool[$melhorIdx]);
        }

        return $selecionados;
    }

    public function saveGames(array $jogos, string $estrategia = 'hibrida'): void
    {
        foreach ($jogos as $jogo) {
            GeneratedGame::create([
                'dezenas'          => $jogo['dezenas'],
                'score_total'      => $jogo['score_total'],
                'score_frequencia' => $jogo['score_frequencia'],
                'score_ciclo'      => $jogo['score_ciclo'],
                'score_repeticao'  => $jogo['score_repeticao'],
                'score_soma'       => $jogo['score_soma'],
                'estrategia'       => $estrategia,
            ]);
        }
    }
}
