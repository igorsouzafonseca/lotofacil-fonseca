<?php

namespace App\Services;

class PatternFilter
{
    private const PRIMOS = [2, 3, 5, 7, 11, 13, 17, 19, 23];
    private const FIBONACCI = [1, 2, 3, 5, 8, 13, 21];

    public function generateCandidates(int $quantidade): array
    {
        $candidatos = [];
        $vistos = [];
        $tentativas = 0;
        $maxTentativas = $quantidade * 20;

        while (count($candidatos) < $quantidade && $tentativas < $maxTentativas) {
            $tentativas++;

            $pool = range(1, 25);
            shuffle($pool);
            $dezenas = array_slice($pool, 0, 15);
            sort($dezenas);

            $chave = implode('-', $dezenas);
            if (isset($vistos[$chave])) {
                continue;
            }
            $vistos[$chave] = true;
            $candidatos[] = $dezenas;
        }

        return $candidatos;
    }

    public function scoreGame(array $dezenas, array $weights): float
    {
        $score = 0.0;

        if (isset($weights['soma'])) {
            $soma = array_sum($dezenas);
            $score += $weights['soma'] * max(0, 1 - (abs($soma - 195) / 100));
        }

        if (isset($weights['pares'])) {
            $pares = count(array_filter($dezenas, fn($d) => $d % 2 === 0));
            $score += $weights['pares'] * max(0, 1 - (abs($pares - 7.5) / 5));
        }

        if (isset($weights['faixas'])) {
            $faixas = [0, 0, 0, 0, 0];
            foreach ($dezenas as $d) {
                $faixas[(int) floor(($d - 1) / 5)]++;
            }
            $desvioFaixas = array_sum(array_map(fn($c) => abs($c - 3), $faixas));
            $score += $weights['faixas'] * max(0, 1 - ($desvioFaixas / 10));
        }

        if (isset($weights['primos'])) {
            $primos = count(array_intersect($dezenas, self::PRIMOS));
            $score += $weights['primos'] * max(0, 1 - (abs($primos - 6) / 5));
        }

        if (isset($weights['fibonacci'])) {
            $fib = count(array_intersect($dezenas, self::FIBONACCI));
            $score += $weights['fibonacci'] * max(0, 1 - (abs($fib - 5) / 4));
        }

        if (isset($weights['sequencia'])) {
            $runs = 1;
            for ($i = 1; $i < count($dezenas); $i++) {
                if ($dezenas[$i] === $dezenas[$i - 1] + 1) {
                    $runs++;
                }
            }
            $score += $weights['sequencia'] * max(0, 1 - (($runs - 1) / 8));
        }

        return $score;
    }
}
