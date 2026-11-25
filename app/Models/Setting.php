<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type', 'encrypted'];

    protected $casts = ['encrypted' => 'boolean'];

    /**
     * Retorna o valor já tipado (bool, int, json ou string).
     * Usa cache global para performance.
     */
    public static function get(string $key, $default = null)
    {
        $all = Cache::rememberForever('settings.all', function () {
            return static::all()->mapWithKeys(function ($s) {
                return [$s->key => $s->castValue()];
            })->toArray();
        });

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    /**
     * Salva/atualiza uma config e invalida o cache.
     */
    public static function set(string $key, $value, string $type = 'string', string $group = 'general', bool $encrypted = false)
    {
        $stored = is_array($value) ? json_encode($value) : (string) $value;
        if ($encrypted) {
            $stored = Crypt::encryptString($stored);
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $stored, 'type' => $type, 'group' => $group, 'encrypted' => $encrypted]
        );

        Cache::forget('settings.all');
    }

    /**
     * Retorna todas as configs de um grupo.
     */
    public static function grupo(string $group): array
    {
        return static::where('group', $group)->get()->mapWithKeys(function ($s) {
            return [$s->key => $s->castValue()];
        })->toArray();
    }

    /**
     * Converte o valor bruto no tipo correto.
     */
    public function castValue()
    {
        $v = $this->encrypted ? Crypt::decryptString($this->value) : $this->value;

        return match ($this->type) {
            'bool' => (bool) $v,
            'int'  => (int) $v,
            'json' => json_decode($v, true),
            default => $v,
        };
    }

    /**
     * Helper para o Blade: {{ setting('site.name') }}
     */
    public static function helper(string $key, $default = null)
    {
        return static::get($key, $default);
    }
}
