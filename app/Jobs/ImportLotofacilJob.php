<?php

namespace App\Jobs;

use App\Models\Importacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportLotofacilJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 300;

    public function __construct(public int $importacaoId)
    {
    }

    public function handle(): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $importacao = Importacao::find($this->importacaoId);
        if (!$importacao) {
            Log::warning('ImportLotofacilJob: importação não encontrada', ['id' => $this->importacaoId]);
            return;
        }

        $importacao->update([
            'status'        => 'processando',
            'iniciado_em'   => now(),
            'mensagem_erro' => null,
        ]);

        // ---- CORREÇÃO: disco 'local' do Laravel 11+ = storage/app/private ----
        $absolute = Storage::disk('local')->path($importacao->arquivo_path);

        if (!is_file($absolute)) {
            $importacao->update([
                'status'        => 'falhou',
                'mensagem_erro' => "Arquivo não encontrado: {$absolute}",
                'finalizado_em' => now(),
            ]);
            return;
        }

        if (filesize($absolute) < 1024) {
            $importacao->update([
                'status'        => 'falhou',
                'mensagem_erro' => "Arquivo suspeito (tamanho " . filesize($absolute) . " bytes): {$absolute}",
                'finalizado_em' => now(),
            ]);
            return;
        }

        $mtime = filemtime($absolute);
        $diffSegundos = abs($mtime - $importacao->created_at->getTimestamp());
        if ($diffSegundos > 300) {
            Log::warning('ImportLotofacilJob: arquivo com mtime distante do upload', [
                'importacao_id' => $importacao->id,
                'arquivo'       => $absolute,
                'mtime'         => date('c', $mtime),
                'upload'        => $importacao->created_at->toIso8601String(),
                'diff_segundos' => $diffSegundos,
            ]);
        }

        try {
            $exit = Artisan::call('lotofacil:import', [
                '--file'  => $absolute,
                '--fresh' => true,
            ]);
            $output = Artisan::output();

            $resultado = $this->parseResumo($output);
            $sucesso   = $exit === 0;

            if ($sucesso && $resultado['processadas'] === 0 && $resultado['importadas'] === 0) {
                $sucesso = false;
                $output .= "\n[Job] Comando não emitiu RESULTADO válido. Possível falha silenciosa.";
            }

            Log::info('ImportLotofacilJob: resultado', [
                'importacao_id' => $importacao->id,
                'exit'          => $exit,
                'resultado'     => $resultado,
                'arquivo'       => $absolute,
                'mtime'         => date('c', $mtime),
            ]);

            $importacao->update([
                'status'             => $sucesso ? 'concluido' : 'falhou',
                'linhas_processadas' => $resultado['processadas'],
                'linhas_importadas'  => $resultado['importadas'],
                'mensagem_erro'      => $sucesso ? null : $this->extrairErro($output),
                'finalizado_em'      => now(),
            ]);
        } catch (\Throwable $e) {
            $importacao->update([
                'status'        => 'falhou',
                'mensagem_erro' => mb_substr($e->getMessage(), 0, 500),
                'finalizado_em' => now(),
            ]);

            Log::error('ImportLotofacilJob: exceção', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function parseResumo(string $output): array
    {
        $default = [
            'processadas' => 0,
            'importadas'  => 0,
            'ignoradas'   => 0,
            'inseridas'   => 0,
            'atualizadas' => 0,
        ];

        $output = preg_replace('/\x1b\[[0-9;]*m/', '', $output);

        if (preg_match('/###RESULTADO_INICIO###\s*(\{.*?\})\s*###RESULTADO_FIM###/s', $output, $m)) {
            $json = json_decode(trim($m[1]), true);
            if (is_array($json)) {
                return array_merge($default, $json);
            }
        }

        if (preg_match('/RESULTADO=(\{[^\n]*\})/', $output, $m)) {
            $json = json_decode(trim($m[1]), true);
            if (is_array($json)) {
                return array_merge($default, $json);
            }
        }

        return $default;
    }

    private function extrairErro(string $output): string
    {
        $linhas = array_filter(array_map('trim', explode("\n", $output)));

        foreach ($linhas as $linha) {
            if (stripos($linha, 'error') !== false
                || stripos($linha, 'erro') !== false
                || stripos($linha, 'exception') !== false
                || stripos($linha, 'não encontrado') !== false
                || stripos($linha, 'ausente') !== false) {
                return mb_substr($linha, 0, 500);
            }
        }

        return mb_substr(trim($output), 0, 500) ?: 'Falha não especificada.';
    }
}
