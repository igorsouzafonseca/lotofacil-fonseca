<?php

namespace App\Services;

use App\Models\Concurso;

class FrequencyAnalyzer
{
    public function getFrequency(int $lastN = 100, ?int $ateConcurso = null): array
    {
        $query = Concurso::orderByDesc('numero_concurso');

        if ($ateConcurso !== null) {
            $query->where('numero_concurso', '<', $ateConcurso);
        }

        $concursos = $query->limit($lastN)->pluck('dezenas');

        $frequency = array_fill(1, 25, 0);

        foreach ($concursos as $dezenas) {
            foreach ((array) $dezenas as $dezena) {
                $frequency[(int) $dezena]++;
            }
        }

        return $frequency;
    }

    public function getHotCold(int $lastN = 50, ?int $ateConcurso = null): array
    {
        $freq = $this->getFrequency($lastN, $ateConcurso);
        $media = array_sum($freq) / 25;

        $hot = [];
        $cold = [];

        foreach ($freq as $dezena => $count) {
            if ($count > $media * 1.1) {
                $hot[] = $dezena;
            } elseif ($count < $media * 0.9) {
                $cold[] = $dezena;
            }
        }

        return compact('hot', 'cold', 'freq', 'media');
    }

    public function getThermalLift(int $lastN = 30, ?int $ateConcurso = null): array
    {
        $freq = $this->getFrequency($lastN, $ateConcurso);
        $expected = ($lastN * 15) / 25;

        $lifts = [];
        foreach ($freq as $dezena => $count) {
            $lifts[$dezena] = $expected > 0 ? round($count / $expected, 3) : 1.0;
        }

        return $lifts;
    }
}
