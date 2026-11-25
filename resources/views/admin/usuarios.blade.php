@extends('layouts.admin')

@section('title', 'Usuários')
@section('content')

{{-- Cabeçalho da página --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">Gestão de usuários</h2>
        <p class="text-sm text-slate-500 mt-1">Crie, visualize e remova usuários do sistema.</p>
    </div>
    <button type="button" onclick="abrirModal()"
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Novo usuário
    </button>
</div>

{{-- Card com tabela --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 uppercase text-xs tracking-wide">
                <tr>
                    <th class="text-left font-semibold px-6 py-3">Usuário</th>
                    <th class="text-left font-semibold px-6 py-3">Email</th>
                    <th class="text-left font-semibold px-6 py-3">Perfil</th>
                    <th class="text-left font-semibold px-6 py-3">Criado em</th>
                    <th class="text-right font-semibold px-6 py-3">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($usuarios as $u)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 grid place-items-center font-semibold text-sm shrink-0">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <span class="font-medium text-slate-900">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $u->email }}</td>
                        <td class="px-6 py-4">
                            @if($u->is_admin)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 text-xs font-semibold">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    Administrador
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-medium">
                                    Usuário
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $u->created_at?->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.usuarios.destroy', $u) }}"
                                      onsubmit="return confirm('Excluir {{ $u->name }}?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50 text-xs font-semibold transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                        Excluir
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400 italic">você</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            Nenhum usuário cadastrado ainda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===================== MODAL NOVO USUÁRIO ===================== --}}
<div id="modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="fecharModal()"></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Novo usuário</h3>
                <button onclick="fecharModal()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.usuarios.store') }}" class="p-6 space-y-4">
                @csrf

                @if($errors->any())
                    <div class="rounded-lg bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm">
                        <p class="font-semibold mb-1">Corrija os erros abaixo:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                        </ul>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nome completo</label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                        class="w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm px-3 py-2 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                        class="w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm px-3 py-2 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Senha</label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm px-3 py-2 border">
                    <p class="text-xs text-slate-500 mt-1">Mínimo de 8 caracteres.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Perfil</label>
                    <select name="is_admin" class="w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm px-3 py-2 border">
                        <option value="0">Usuário comum</option>
                        <option value="1">Administrador</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="fecharModal()"
                        class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm font-medium hover:bg-slate-50">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm">
                        Criar usuário
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function abrirModal() {
        document.getElementById('modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function fecharModal() {
        document.getElementById('modal').classList.add('hidden');
        document.body.style.overflow = '';
    }
    // Abre o modal automaticamente se houver erro de validação
    @if($errors->any()) abrirModal(); @endif
    // Fecha com ESC
    document.addEventListener('keydown', e => { if (e.key === 'Escape') fecharModal(); });
</script>
@endpush
