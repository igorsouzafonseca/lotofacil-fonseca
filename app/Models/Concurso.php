<?php

// app/Models/Concurso.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class Concurso extends Model
{
    protected $table = 'concursos';

    protected $casts = [
        'dezenas' => 'array',
        'data_sorteio' => 'date',
    ];

    protected $fillable = [
        'numero_concurso', 'data_sorteio', 'dezenas',
        'soma_dezenas', 'qtd_pares', 'qtd_impares',
        'qtd_primos', 'qtd_fibonacci', 'repetidas_concurso_anterior',
    ];
}
