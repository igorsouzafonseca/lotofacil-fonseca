@extends('layouts.admin')

@section('title', 'Gerador de Jogos')
@push('styles')
        <style>
            .btn_number.selected {
                background-color: yellow;
            }
            .btn_number{
                cursor: pointer;
            }
        </style>
@endpush
@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Gerador de Jogos - Lotofácil</h1>

        {{-- Mensagem de Sucesso --}}
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        {{-- Formulário de Geração --}}
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <form method="POST" action="{{ route('generator.generate') }}">
                @csrf
                <div class="flex flex-col sm:flex-row items-end gap-4">
                    <div class="flex-1">
                        <label for="quantidade" class="block text-sm font-medium text-gray-700 mb-1">
                            Quantidade de jogos a gerar:
                        </label>
                        <input type="number" name="quantidade" id="quantidade" value="10" min="1" max="50"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 px-4 py-2 border">
                        <p class="text-xs text-gray-500 mt-1">Máximo de 50 jogos por vez.</p>
                    </div>
                    <button type="submit" class="my-auto bg-green-600 text-white font-bold px-8 py-2.5 rounded-lg hover:bg-green-700 transition shadow">
                        Gerar Jogos
                    </button>
                </div>
            </form>
        </div>

        {{-- Lista de Jogos Gerados --}}
        @if(isset($jogos) && count($jogos) > 0)
            <div class="space-y-3">
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Jogos Sugeridos (ordenados por Score)</h2>
                @foreach($jogos as $index => $jogo)
                    <div class="bg-white rounded-lg shadow p-4 flex flex-col sm:flex-row items-center justify-between border-l-4 border-green-500 item" style="position: relative;">
                        <div class="flex items-center gap-3 mb-3 sm:mb-0 w-full sm:w-auto">
                            <span class="text-gray-400 font-mono text-sm w-8">#{{ $index + 1 }}</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($jogo['dezenas'] as $d)
                                    <span class="btn_number bg-green-100 text-green-800 w-9 h-9 flex items-center justify-center rounded-full text-sm font-bold shadow-sm">
                                        {{ str_pad($d, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <div class="total absolute top-[5px] right-[14%] w-[12%] border border-gray-400 rounded-[25px] text-xs text-gray-600 font-bold p-[3px]">
                            <span class="selecionados text-gray-600">0</span> selecionados
                        </div>
                        <div class="text-right sm:text-right w-full sm:w-auto border-t sm:border-t-0 pt-3 sm:pt-0">
                            <div class="text-xl font-bold text-green-700">{{ number_format($jogo['score_total'], 3) }}</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Score Total</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-6 text-center">
                * Estes jogos são gerados com base em filtros estatísticos descritivos. A Lotofácil é um jogo de azar e não há garantia de premiação.
            </p>
        @elseif(request()->isMethod('post'))
            <div class="text-center text-gray-500 py-10">
                Nenhum jogo gerado. Verifique se o banco de dados possui concursos importados.
            </div>
        @endif
    </div>
    @push('scripts')
        <script>
            $(document).ready(function(){
                $('.btn_number').on('click', function(){
                    $(this).toggleClass('selected');

                    let item = $(this).closest('.item');
                    let total = item.find('.btn_number.selected').length;

                    item.find('.selecionados')
                        .text(total)
                        .toggleClass('text-green-600', total > 10)
                        .toggleClass('text-gray-600', total <= 10);

                    item.find('.total')
                        .toggleClass('text-green-600 border-green-400', total > 10)
                        .toggleClass('text-gray-600 border-gray-400', total <= 10);
                })
            });
        </script>
    @endpush
@endsection
