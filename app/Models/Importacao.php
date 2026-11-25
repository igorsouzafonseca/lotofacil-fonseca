<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Importacao extends Model
{
    protected $table = 'importacaos';

    protected $fillable = [
        'arquivo_original',
        'arquivo_path',
        'status',
        'linhas_processadas',
        'linhas_importadas',
        'mensagem_erro',
        'user_id',
        'iniciado_em',
        'finalizado_em',
    ];

    protected $casts = [
        'iniciado_em'   => 'datetime',
        'finalizado_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function estaEmAndamento(): bool
    {
        return in_array($this->status, ['pendente', 'processando']);
    }

    public function badgeClass(): string
    {
        return match ($this->status) {
            'pendente'     => 'bg-slate-100 text-slate-700',
            'processando'  => 'bg-amber-50 text-amber-700',
            'concluido'    => 'bg-emerald-50 text-emerald-700',
            'falhou'       => 'bg-rose-50 text-rose-700',
            default        => 'bg-slate-100 text-slate-700',
        };
    }
}
