<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportLotofacilJob;
use App\Models\Importacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function index()
    {
        $importacoes = Importacao::with('user')
            ->latest()
            ->paginate(15);

        return view('admin.importacoes', compact('importacoes'));
    }

    public function store(Request $request)
    {
        // --- 1. Validação ---
        // Aceita .csv, .txt e .xlsx (a Caixa só fornece .xlsx)
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ], [
            'csv_file.required' => 'Selecione um arquivo.',
            'csv_file.mimes'    => 'O arquivo precisa ser .csv, .txt, .xlsx ou .xls.',
            'csv_file.max'      => 'O arquivo não pode passar de 10MB.',
        ]);

        // --- 2. Impede importações simultâneas (com lock para evitar corrida) ---
        $emAndamento = Importacao::whereIn('status', ['pendente', 'processando'])->exists();
        if ($emAndamento) {
            return back()->withErrors([
                'csv_file' => 'Já existe uma importação em andamento. Aguarde finalizar.',
            ]);
        }

        $file = $request->file('csv_file');

        if (!$file->isValid()) {
            return back()->withErrors([
                'csv_file' => 'Falha no upload do arquivo. Tente novamente.',
            ]);
        }

        // --- 3. Grava o arquivo de forma atômica e verificável ---
        // O storeAs retorna false em falha; antes o código ignorava isso.
        $stored = $file->storeAs('', 'lotofacil.csv', 'local');

        if ($stored === false) {
            Log::error('ImportController: falha ao salvar arquivo', [
                'original' => $file->getClientOriginalName(),
                'erro'     => $file->getErrorMessage(),
            ]);
            return back()->withErrors([
                'csv_file' => 'Não foi possível salvar o arquivo no servidor. Verifique permissões de storage/app.',
            ]);
        }

        // --- 4. Confirma que o arquivo realmente foi sobrescrito ---
        $absolute = Storage::disk('local')->path($stored);

        if (!is_file($absolute) || filesize($absolute) === 0) {
            Log::error('ImportController: arquivo salvo está vazio ou ausente', [
                'path' => $absolute,
            ]);
            return back()->withErrors([
                'csv_file' => 'O arquivo salvo está vazio ou inacessível. Tente novamente.',
            ]);
        }

        // --- 5. Cria o registro e dispara o Job ---
        $importacao = Importacao::create([
            'arquivo_original' => $file->getClientOriginalName(),
            'arquivo_path'     => $stored, // 'lotofacil.csv'
            'status'           => 'pendente',
            'user_id'          => auth()->id(),
        ]);

        ImportLotofacilJob::dispatch($importacao->id);

        Log::info('ImportController: importação agendada', [
            'importacao_id' => $importacao->id,
            'arquivo'       => $absolute,
            'tamanho'       => filesize($absolute),
            'mtime'         => date('c', filemtime($absolute)),
        ]);

        return redirect()
            ->route('admin.importacoes')
            ->with('sucesso', 'Arquivo enviado. A importação foi agendada.');
    }

    /** Endpoint usado pelo polling da tela para atualizar status */
    public function status(Importacao $importacao)
    {
        return response()->json([
            'id'                 => $importacao->id,
            'status'             => $importacao->status,
            'linhas_processadas' => $importacao->linhas_processadas,
            'linhas_importadas'  => $importacao->linhas_importadas,
            'mensagem_erro'      => $importacao->mensagem_erro,
            'iniciado_em'        => $importacao->iniciado_em?->format('d/m/Y H:i'),
            'finalizado_em'      => $importacao->finalizado_em?->format('d/m/Y H:i'),
        ]);
    }
}
