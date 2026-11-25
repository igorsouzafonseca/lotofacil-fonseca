<?php

// app/Http/Controllers/GameGeneratorController.php
namespace App\Http\Controllers;

use App\Services\GameGeneratorService;
use App\Services\FrequencyAnalyzer;
use App\Services\CycleAnalyzer;
use Illuminate\Http\Request;

class GameGeneratorController extends Controller
{
    public function __construct(
        private GameGeneratorService $generator,
        private FrequencyAnalyzer $frequency,
        private CycleAnalyzer $cycle
    ) {}

    public function index()
    {
        return view('generator');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'quantidade' => 'integer|min:1|max:50',
        ]);

        $quantidade = $request->input('quantidade', 10);
        $jogos = $this->generator->generateDiverse($quantidade);
        $this->generator->saveGames($jogos);

        return view('generator', compact('jogos'))->with('success', 'Jogos gerados com sucesso!');
    }

    public function dashboard()
    {
        $hotCold = $this->frequency->getHotCold(50);
        $lifts = $this->frequency->getThermalLift(30);
        $atraso = $this->cycle->getCurrentAtraso();
        $mediaRepeticoes = $this->cycle->getAverageRepetitions();

        return view('dashboard', compact('hotCold', 'lifts', 'atraso', 'mediaRepeticoes'));
    }
}
