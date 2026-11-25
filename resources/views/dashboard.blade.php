@extends('layouts.admin')

@section('title', 'Dashboard')
@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Dashboard Estatístico - Lotofácil</h1>

        {{-- Cards Superiores --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- Dezenas Quentes --}}
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                <h2 class="text-lg font-semibold text-red-600 mb-3">🔥 Dezenas Quentes (50 concursos)</h2>
                <div class="flex flex-wrap gap-2">
                    @forelse($hotCold['hot'] as $d)
                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-bold">
                            {{ str_pad($d, 2, '0', STR_PAD_LEFT) }}
                        </span>
                    @empty
                        <span class="text-gray-500">Nenhuma dezena acima da média.</span>
                    @endforelse
                </div>
            </div>

            {{-- Dezenas Frias --}}
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <h2 class="text-lg font-semibold text-blue-600 mb-3">❄️ Dezenas Frias (50 concursos)</h2>
                <div class="flex flex-wrap gap-2">
                    @forelse($hotCold['cold'] as $d)
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-bold">
                            {{ str_pad($d, 2, '0', STR_PAD_LEFT) }}
                        </span>
                    @empty
                        <span class="text-gray-500">Nenhuma dezena abaixo da média.</span>
                    @endforelse
                </div>
            </div>

            {{-- Repetições --}}
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                <h2 class="text-lg font-semibold text-purple-600 mb-3">🔄 Repetições Médias</h2>
                <p class="text-5xl font-bold text-gray-800">{{ $mediaRepeticoes }}</p>
                <p class="text-gray-500 text-sm mt-1">dezenas repetidas do concurso anterior</p>
            </div>
        </div>

        {{-- Atraso por Dezena --}}
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">⏱️ Atraso Atual (concursos sem sair)</h2>
            <p class="text-sm text-gray-500 mb-4">Dezenas em amarelo estão com atraso acima de 5 concursos.</p>
            <div class="grid grid-cols-5 md:grid-cols-10 gap-3">
                @foreach($atraso as $dezena => $dias)
                    <div class="text-center p-2 rounded-lg {{ $dias > 5 ? 'bg-yellow-100 border border-yellow-300' : 'bg-gray-50 border border-gray-200' }}">
                        <div class="font-bold text-gray-800">{{ str_pad($dezena, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="text-xs text-gray-600 font-mono">{{ $dias }}x</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('generator.index') }}" class="inline-block bg-green-600 text-white font-bold px-8 py-3 rounded-lg hover:bg-green-700 transition shadow-lg">
                Ir para o Gerador de Jogos →
            </a>
        </div>
    </div>
@endsection
