<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /** Abas disponíveis */
    public const TABS = [
        'general'    => 'Geral',
        'appearance' => 'Aparência',
        'security'   => 'Segurança',
    ];

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'general');
        if (! array_key_exists($tab, self::TABS)) {
            $tab = 'general';
        }

        $settings = Setting::grupo($tab);

        return view('admin.configuracoes', [
            'tabs'     => self::TABS,
            'tabAtiva' => $tab,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request, string $tab)
{
    Log::info('UPDATE INICIO', [
        'tab' => $tab,
        'dados_recebidos' => $request->all(),
    ]);

    if (! array_key_exists($tab, self::TABS)) {
        abort(404);
    }

    $dados = match ($tab) {
        'general'    => $this->validarGeral($request),
        'appearance' => $this->validarAparencia($request),
        'security'   => $this->validarSeguranca($request),
    };

    Log::info('UPDATE VALIDOU', ['dados' => $dados]);

    $before = Setting::grupo($tab);

    foreach ($dados as $key => $value) {
        $type = $this->inferirTipo($value);
        Setting::set($key, $value, $type, $tab);
    }

    Log::info('UPDATE SALVOU', [
        'antes' => $before,
        'depois' => Setting::grupo($tab),
    ]);

    return redirect()
        ->route('admin.configuracoes', ['tab' => $tab])
        ->with('sucesso', 'Configurações de ' . self::TABS[$tab] . ' salvas.');
}

    // ---------- VALIDAÇÃO POR GRUPO ----------

    private function validarGeral(Request $r): array
    {
        $v = $r->validate([
            'site.name'        => ['required', 'string', 'max:100'],
            'site.description' => ['nullable', 'string', 'max:255'],
            'locale'           => ['required', 'in:pt_BR,en,es'],
            'timezone'         => ['required', 'timezone'],
            'date_format'      => ['required', 'in:d/m/Y,Y-m-d,m/d/Y'],
        ]);

        return [
            'site.name'        => $v['site']['name'],
            'site.description' => $v['site']['description'] ?? '',
            'locale'           => $v['locale'],
            'timezone'         => $v['timezone'],
            'date_format'      => $v['date_format'],
        ];
    }

    private function validarAparencia(Request $r): array
    {
        $v = $r->validate([
            'theme.primary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.mode'    => ['required', 'in:light,dark,system'],
            'theme.footer'  => ['nullable', 'string', 'max:255'],
        ]);

        return [
            'theme.primary' => $v['theme.primary'],
            'theme.mode'    => $v['theme.mode'],
            'theme.footer'  => $v['theme.footer'] ?? '',
        ];
    }

    private function validarSeguranca(Request $r): array
    {
        $v = $r->validate([
            'security.session_lifetime'   => ['required', 'integer', 'min:5', 'max:1440'],
            'security.max_login_attempts' => ['required', 'integer', 'min:3', 'max:20'],
            'security.password_min'       => ['required', 'integer', 'min:6', 'max:32'],
        ]);

        return [
            'security.session_lifetime'   => (int) $v['security.session_lifetime'],
            'security.max_login_attempts' => (int) $v['security.max_login_attempts'],
            'security.password_min'       => (int) $v['security.password_min'],
            'security.require_2fa'        => $r->boolean('security.require_2fa'),
            'security.allow_signup'       => $r->boolean('security.allow_signup'),
        ];
    }

    private function inferirTipo($value): string
    {
        return match (true) {
            is_bool($value)  => 'bool',
            is_int($value)   => 'int',
            is_array($value) => 'json',
            default          => 'string',
        };
    }
}
