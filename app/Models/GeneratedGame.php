<?php

// app/Models/GeneratedGame.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedGame extends Model
{
    protected $table = 'generated_games';

    protected $casts = [
        'dezenas' => 'array',
    ];

    protected $fillable = [
        'dezenas', 'score_total', 'score_frequencia', 'score_ciclo',
        'score_repeticao', 'score_soma', 'score_distribuicao', 'estrategia',
    ];
}
