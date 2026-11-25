@extends('layouts.admin')

@section('title', 'Configurações')
@section('content')

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Configurações</h2>
        <p class="text-sm text-slate-500 mt-1">Ajustes gerais do sistema.</p>
    </div>

    {{-- ABAS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="border-b border-slate-200 flex overflow-x-auto">
            @foreach($tabs as $key => $label)
                <a href="{{ route('admin.configuracoes', ['tab' => $key]) }}"
                   class="px-6 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                          {{ $tabAtiva === $key
                              ? 'border-brand-600 text-brand-700 bg-brand-50/40'
                              : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <form method="POST"
              action="{{ route('admin.configuracoes.update', $tabAtiva) }}"
              class="p-6 space-y-5">
            @csrf
            @method('PUT')

            {{-- ============== GERAL ============== --}}
            @if($tabAtiva === 'general')
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nome do sistema</label>
                    <input type="text" name="site[name]" required
                        value="{{ old('site.name', $settings['site.name'] ?? '') }}"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Descrição</label>
                    <input type="text" name="site[description]"
                        value="{{ old('site.description', $settings['site.description'] ?? '') }}"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Idioma</label>
                        <select name="locale" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach(['pt_BR' => 'Português (BR)', 'en' => 'Inglês', 'es' => 'Espanhol'] as $v => $l)
                                <option value="{{ $v }}" @selected(($settings['locale'] ?? '') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Fuso horário</label>
                        <select name="timezone" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach(timezone_identifiers_list() as $tz)
                                <option value="{{ $tz }}" @selected(($settings['timezone'] ?? '') === $tz)>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Formato de data</label>
                    <select name="date_format" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach(['d/m/Y' => 'dd/mm/aaaa', 'Y-m-d' => 'aaaa-mm-dd', 'm/d/Y' => 'mm/dd/aaaa'] as $v => $l)
                            <option value="{{ $v }}" @selected(($settings['date_format'] ?? '') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- ============== APARÊNCIA ============== --}}
            @if($tabAtiva === 'appearance')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Cor primária</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="theme.primary"
                                value="{{ old('theme.primary', $settings['theme.primary'] ?? '#4f46e5') }}"
                                class="w-12 h-10 rounded border border-slate-300 cursor-pointer">
                            <input type="text" readonly
                                value="{{ old('theme.primary', $settings['theme.primary'] ?? '#4f46e5') }}"
                                class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm bg-slate-50">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Modo</label>
                        <select name="theme.mode" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach(['light' => 'Claro', 'dark' => 'Escuro', 'system' => 'Sistema'] as $v => $l)
                                <option value="{{ $v }}" @selected(($settings['theme.mode'] ?? '') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Texto do rodapé</label>
                    <input type="text" name="theme.footer"
                        value="{{ old('theme.footer', $settings['theme.footer'] ?? '') }}"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            @endif

            {{-- ============== SEGURANÇA ============== --}}
            @if($tabAtiva === 'security')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tempo de sessão (min)</label>
                        <input type="number" name="security.session_lifetime" min="5" max="1440" required
                            value="{{ old('security.session_lifetime', $settings['security.session_lifetime'] ?? 120) }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tentativas de login</label>
                        <input type="number" name="security.max_login_attempts" min="3" max="20" required
                            value="{{ old('security.max_login_attempts', $settings['security.max_login_attempts'] ?? 5) }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tamanho mínimo da senha</label>
                    <input type="number" name="security.password_min" min="6" max="32" required
                        value="{{ old('security.password_min', $settings['security.password_min'] ?? 8) }}"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>

                <div class="space-y-3 pt-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="security.require_2fa" value="1"
                            @checked(!empty($settings['security.require_2fa']))
                            class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-slate-700">Exigir 2FA para administradores</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="security.allow_signup" value="1"
                            @checked(!empty($settings['security.allow_signup']))
                            class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-slate-700">Permitir cadastro público</span>
                    </label>
                </div>
            @endif

            {{-- BOTÕES --}}
            <div class="flex justify-end gap-2 pt-4 border-t border-slate-200">
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition">
                    Salvar alterações
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
