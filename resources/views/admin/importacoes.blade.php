@extends('layouts.admin')

@section('title', 'Importações')
@section('content')

<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Importação de resultados</h2>
        <p class="text-sm text-slate-500 mt-1">Envie o CSV da Lotofácil. O processamento roda em segundo plano.</p>
    </div>

    {{-- Erros --}}
    @if($errors->any())
        <div class="mb-6 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- Card de upload --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Novo arquivo</h3>

        <form method="POST" action="{{ route('admin.importacoes.store') }}" enctype="multipart/form-data" id="uploadForm">
            @csrf

            <label for="csv_file"
                class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-brand-500 hover:bg-brand-50/40 transition">
                <svg class="w-10 h-10 text-slate-400 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.9 5 5 0 019.9 1.1 4 4 0 01-.9 7.8M12 12v9m0-9l-3 3m3-3l3 3"/>
                </svg>
                <span class="text-sm font-medium text-slate-600" id="fileLabel">Clique para selecionar o CSV</span>
                <span class="text-xs text-slate-400 mt-1">.csv ou .txt · máx. 10MB</span>
                <input id="csv_file" name="csv_file" type="file" class="hidden" accept=".csv,.txt" required>
            </label>

            <div class="flex justify-end gap-2 mt-4">
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition">
                    Importar
                </button>
            </div>
        </form>
    </div>

    {{-- Histórico --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-sm font-semibold text-slate-700">Histórico</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 uppercase text-xs tracking-wide">
                    <tr>
                        <th class="text-left font-semibold px-6 py-3">Arquivo</th>
                        <th class="text-left font-semibold px-6 py-3">Status</th>
                        <th class="text-left font-semibold px-6 py-3">Importadas</th>
                        <th class="text-left font-semibold px-6 py-3">Enviado por</th>
                        <th class="text-left font-semibold px-6 py-3">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($importacoes as $imp)
                        <tr class="hover:bg-slate-50/70 transition" data-id="{{ $imp->id }}">
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $imp->arquivo_original }}</td>
                            <td class="px-6 py-4">
                                <span class="status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $imp->badgeClass() }}">
                                    {{ ucfirst($imp->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $imp->linhas_importadas ?: '—' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $imp->user?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $imp->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                Nenhuma importação realizada ainda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200">
            {{ $importacoes->links() }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Mostra o nome do arquivo escolhido
    document.getElementById('csv_file')?.addEventListener('change', (e) => {
        const label = document.getElementById('fileLabel');
        const nome = e.target.files[0]?.name ?? 'Clique para selecionar o CSV';
        label.textContent = nome;
    });

    // Polling de status para itens em andamento
    const emAndamento = [...document.querySelectorAll('[data-id]')]
        .filter(tr => {
            const badge = tr.querySelector('.status-badge');
            return badge && ['Pendente', 'Processando'].includes(badge.textContent.trim());
        });

    if (emAndamento.length) {
        const ids = emAndamento.map(tr => tr.dataset.id);
        const intervalo = setInterval(async () => {
            let aindaRodando = false;
            for (const id of ids) {
                const r = await fetch(`/admin/importacoes/${id}/status`);
                if (!r.ok) continue;
                const d = await r.json();
                const tr = document.querySelector(`[data-id="${id}"]`);
                if (!tr) continue;

                const badge = tr.querySelector('.status-badge');
                badge.textContent = d.status.charAt(0).toUpperCase() + d.status.slice(1);
                badge.className = 'status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ' + ({
                    pendente:    'bg-slate-100 text-slate-700',
                    processando: 'bg-amber-50 text-amber-700',
                    concluido:   'bg-emerald-50 text-emerald-700',
                    falhou:      'bg-rose-50 text-rose-700',
                }[d.status] || 'bg-slate-100 text-slate-700');

                if (d.linhas_importadas) {
                    tr.children[2].textContent = d.linhas_importadas;
                }

                if (['pendente', 'processando'].includes(d.status)) {
                    aindaRodando = true;
                }
            }
            if (!aindaRodando) {
                clearInterval(intervalo);
                setTimeout(() => location.reload(), 800);
            }
        }, 3000);
    }
</script>
@endpush
