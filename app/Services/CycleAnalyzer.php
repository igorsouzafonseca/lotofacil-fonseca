<?php

namespace App\Services;

use App\Models\Concurso;

class CycleAnalyzer
{
    public function getCurrentAtraso(?int $ateConcurso = null): array
    {
        $query = Concurso::orderByDesc('numero_concurso');

        if ($ateConcurso !== null) {
            $query->where('numero_concurso', '<', $ateConcurso);
        }

        $concursos = $query->limit(50)->get();

        $encontrados = [];
        foreach ($concursos as $index => $concurso) {
            foreach ((array) $concurso->dezenas as $dezena) {
                $d = (int) $dezena;
                if (!isset($encontrados[$d])) {
                    $encontrados[$d] = $index;
                }
            }
        }

        $atraso = [];
        for ($i = 1; $i <= 25; $i++) {
            $atraso[$i] = $encontrados[$i] ?? 50;
        }

        return $atraso;
    }

    public function getAverageRepetitions(int $lastN = 100, ?int $ateConcurso = null): float
    {
        $query = Concurso::orderByDesc('numero_concurso');

        if ($ateConcurso !== null) {
            $query->where('numero_concurso', '<', $ateConcurso);
        }

        $concursos = $query->limit($lastN)->get()->reverse()->values();

        if ($concursos->count() < 2) {
            return 9.0;
        }

        $total = 0;
        $pares = 0;

        for ($i = 1; $i < $concursos->count(); $i++) {
            $total += count(array_intersect(
                (array) $concursos[$i - 1]->dezenas,
                (array) $concursos[$i]->dezenas
            ));
            $pares++;
        }

        return $pares > 0 ? round($total / $pares, 2) : 9.0;
    }

    public function getDezenasEmRetorno(?int $ateConcurso = null): array
    {
        $atraso = $this->getCurrentAtraso($ateConcurso);
        $media = array_sum($atraso) / 25;
        $desvio = sqrt(
            array_sum(array_map(fn($x) => ($x - $media) ** 2, $atraso)) / 25
        );

        $emRetorno = [];
        foreach ($atraso as $dezena => $dias) {
            if ($dias > $media + 2 * $desvio) {
                $emRetorno[] = $dezena;
            }
        }

        return $emRetorno;
    }
}
