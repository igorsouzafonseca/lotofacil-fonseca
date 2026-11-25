<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Geral
            ['key' => 'site.name',        'value' => 'Meu Sistema',      'group' => 'general',  'type' => 'string'],
            ['key' => 'site.description', 'value' => 'Painel administrativo', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site.favicon',     'value' => null,               'group' => 'general',  'type' => 'string'],
            ['key' => 'locale',           'value' => 'pt_BR',            'group' => 'general',  'type' => 'string'],
            ['key' => 'timezone',         'value' => 'America/Sao_Paulo','group' => 'general',  'type' => 'string'],
            ['key' => 'date_format',      'value' => 'd/m/Y',            'group' => 'general',  'type' => 'string'],

            // Aparência
            ['key' => 'theme.primary',   'value' => '#4f46e5',           'group' => 'appearance', 'type' => 'string'],
            ['key' => 'theme.mode',      'value' => 'light',             'group' => 'appearance', 'type' => 'string'],
            ['key' => 'theme.footer',    'value' => '© ' . date('Y') . ' Meu Sistema', 'group' => 'appearance', 'type' => 'string'],

            // Segurança
            ['key' => 'security.session_lifetime',   'value' => '120',  'group' => 'security', 'type' => 'int'],
            ['key' => 'security.max_login_attempts', 'value' => '5',    'group' => 'security', 'type' => 'int'],
            ['key' => 'security.password_min',       'value' => '8',    'group' => 'security', 'type' => 'int'],
            ['key' => 'security.require_2fa',        'value' => '0',    'group' => 'security', 'type' => 'bool'],
            ['key' => 'security.allow_signup',       'value' => '0',    'group' => 'security', 'type' => 'bool'],
        ];

        foreach ($defaults as $d) {
            Setting::updateOrCreate(['key' => $d['key']], $d);
        }

        \Illuminate\Support\Facades\Cache::forget('settings.all');
    }
}
