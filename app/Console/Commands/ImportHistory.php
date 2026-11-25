<?php

namespace App\Console\Commands;

use App\Models\Concurso;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportHistory extends Command
{
    protected $signature = 'lotofacil:import
                            {--file= : Caminho do CSV exportado do Excel}
                            {--delimiter= : Delimitador do CSV (detecta automaticamente se omitido)}
                            {--fresh : Limpa a tabela antes de importar}';

    protected $description = 'Importa histórico da Lotofácil a partir do CSV exportado do Excel';

    private int $processadas = 0;
    private int $importadas  = 0;
    private int $ignoradas   = 0;
    private int $inseridas   = 0;
    private int $atualizadas = 0;

    public function handle(): int
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $file = $this->option('file');

        if (!$file || !is_file($file)) {
            $this->error("Arquivo não encontrado: {$file}");
            $this->emitResumo(); // emite com zeros
            return self::FAILURE;
        }

        $delimiter = $this->option('delimiter') ?: $this->detectDelimiter($file);

        if ($this->option('fresh')) {
            DB::table('concursos')->truncate();
            $this->warn('Tabela concursos limpa (--fresh).');
        }

        $handle = fopen($file, 'r');
        if ($handle === false) {
            $this->error("Não foi possível abrir o arquivo: {$file}");
            $this->emitResumo();
            return self::FAILURE;
        }

        $header = fgetcsv($handle, 0, $delimiter);
        if ($header === false) {
            fclose($handle);
            $this->error('Arquivo vazio.');
            $this->emitResumo();
            return self::FAILURE;
        }

        $header = array_map([$this, 'normalizeHeader'], $header);
        $index  = array_flip($header);

        $expected = array_merge(
            ['concurso', 'data'],
            array_map(fn($n) => "bola{$n}", range(1, 15))
        );

        foreach ($expected as $col) {
            if (!isset($index[$col])) {
                fclose($handle);
                $this->error("Coluna ausente no CSV: {$col}");
                $this->emitResumo();
                return self::FAILURE;
            }
        }

        $bar = $this->output->createProgressBar();
        $bar->start();

        $rows = [];
        $now  = now();

        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Linha totalmente vazia (fgetcsv devolve [null])
            if ($line === [null] || count($line) < count($expected)) {
                $this->ignoradas++;
                continue;
            }

            $this->processadas++;

            try {
                $data = $this->parseDate($line[$index['data']]);
            } catch (\InvalidArgumentException $e) {
                $this->newLine();
                $this->warn("Linha ignorada: {$e->getMessage()}");
                $this->ignoradas++;
                continue;
            }

            $dezenas = [];
            for ($i = 1; $i <= 15; $i++) {
                $dezenas[] = (int) $line[$index["bola{$i}"]];
            }
            sort($dezenas);

            $soma      = array_sum($dezenas);
            $pares     = count(array_filter($dezenas, fn($n) => $n % 2 === 0));
            $impares   = 15 - $pares;
            $primos    = count(array_filter($dezenas, fn($n) => in_array($n, [2,3,5,7,11,13,17,19,23])));
            $fibonacci = count(array_filter($dezenas, fn($n) => in_array($n, [1,2,3,5,8,13,21])));

            $rows[] = [
                'numero_concurso'             => (int) $line[$index['concurso']],
                'data_sorteio'                => $data,
                'dezenas'                     => json_encode($dezenas),
                'soma_dezenas'                => $soma,
                'qtd_pares'                   => $pares,
                'qtd_impares'                 => $impares,
                'qtd_primos'                  => $primos,
                'qtd_fibonacci'               => $fibonacci,
                'repetidas_concurso_anterior' => 0, // recalculado depois
                'created_at'                  => $now,
                'updated_at'                  => $now,
            ];

            $this->importadas++;
            $bar->advance();

            if (count($rows) >= 500) {
                $this->insertBatch($rows);
                $rows = [];
            }
        }

        if (!empty($rows)) {
            $this->insertBatch($rows);
        }

        fclose($handle);
        $bar->finish();
        $this->newLine();

        $this->recalculateRepetitions();

        $this->info("Importação concluída: {$this->importadas} concurso(s) importado(s).");
        if ($this->ignoradas > 0) {
            $this->warn("Linhas ignoradas: {$this->ignoradas}");
        }

        $this->emitResumo();

        return self::SUCCESS;
    }

    /**
     * Emite as métricas em formato parseável pelo Job.
     * Ex.: RESULTADO={"processadas":3000,"importadas":3000,...}
     */
    private function emitResumo(): void
    {
        $payload = [
            'processadas' => $this->processadas,
            'importadas'  => $this->importadas,
            'ignoradas'   => $this->ignoradas,
            'inseridas'   => $this->inseridas,
            'atualizadas' => $this->atualizadas,
        ];

        // Marcadores fortes + newline explícito
        $this->output->writeln('###RESULTADO_INICIO###');
        $this->output->writeln(json_encode($payload));
        $this->output->writeln('###RESULTADO_FIM###');
    }

    private function insertBatch(array $rows): void
    {
        $numeros = array_column($rows, 'numero_concurso');

        $existentes = DB::table('concursos')
            ->whereIn('numero_concurso', $numeros)
            ->count();

        $total = count($numeros);
        $this->atualizadas += $existentes;
        $this->inseridas   += $total - $existentes;

        DB::table('concursos')->upsert(
            $rows,
            ['numero_concurso'],
            [
                'data_sorteio',
                'dezenas',
                'soma_dezenas',
                'qtd_pares',
                'qtd_impares',
                'qtd_primos',
                'qtd_fibonacci',
                'repetidas_concurso_anterior',
                'updated_at',
            ]
        );
    }

    private function detectDelimiter(string $file): string
    {
        $handle    = fopen($file, 'r');
        $firstLine = fgets($handle);
        fclose($handle);

        if ($firstLine === false) {
            return ',';
        }

        $candidates = [',', ';', "\t", '|'];
        $best       = ',';
        $bestCount  = 0;

        foreach ($candidates as $delim) {
            $count = substr_count($firstLine, $delim);
            if ($count > $bestCount) {
                $bestCount = $count;
                $best      = $delim;
            }
        }

        return $best;
    }

    private function normalizeHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
        $header = mb_strtolower(trim($header));

        $trans = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c',
        ];
        $header = strtr($header, $trans);

        return preg_replace('/[^a-z0-9]/', '', $header);
    }

    private function parseDate(string $value): string
    {
        $value = trim($value);

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y'] as $format) {
            $dt = \DateTime::createFromFormat($format, $value);
            if ($dt !== false && $dt->format($format) === $value) {
                return $dt->format('Y-m-d');
            }
        }

        $ts = strtotime($value);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        throw new \InvalidArgumentException("Data inválida: {$value}");
    }

    private function recalculateRepetitions(): void
    {
        $this->line('Calculando repetições entre concursos consecutivos...');

        $concursos = Concurso::orderBy('numero_concurso')
            ->get(['id', 'dezenas', 'numero_concurso']);

        $previousDezenas = null;
        $updates         = [];

        foreach ($concursos as $concurso) {
            if ($previousDezenas !== null) {
                $updates[$concurso->id] = count(array_intersect($previousDezenas, $concurso->dezenas));
            }
            $previousDezenas = $concurso->dezenas;
        }

        foreach (array_chunk($updates, 200, true) as $chunk) {
            $cases    = [];
            $bindings = [];

            foreach ($chunk as $id => $rep) {
                $cases[]    = 'WHEN ? THEN ?';
                $bindings[] = $id;
                $bindings[] = $rep;
            }

            $ids          = array_keys($chunk);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            DB::update(
                'UPDATE concursos SET repetidas_concurso_anterior = CASE id '
                . implode(' ', $cases)
                . " END WHERE id IN ({$placeholders})",
                array_merge($bindings, $ids)
            );
        }
    }
}
