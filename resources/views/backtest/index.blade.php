<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Backtest — Lotofácil</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f5f6f8;
            color: #1f2937;
            margin: 0;
            padding: 2rem 1rem;
        }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { margin: 0 0 0.25rem; font-size: 1.5rem; }
        .subtitle { color: #6b7280; margin: 0 0 2rem; font-size: 0.9rem; line-height: 1.5; }
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }
        label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.35rem; color: #374151; }
        input[type="number"], input[type="text"] {
            width: 100%;
            padding: 0.55rem 0.7rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.95rem;
            background: #fff;
        }
        input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .hint { font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem; line-height: 1.4; }
        .actions { margin-top: 1.25rem; display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; }
        button {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover:not(:disabled) { background: #1d4ed8; }
        .btn-primary:disabled { background: #93c5fd; cursor: wait; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn-secondary:hover:not(:disabled) { background: #d1d5db; }
        .btn-secondary:disabled { opacity: 0.5; cursor: wait; }
        .status { font-size: 0.85rem; color: #4b5563; }
        .progress {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 1rem;
            display: none;
        }
        .progress.active { display: block; }
        .progress-bar {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            transition: width 0.4s ease;
        }
        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: none;
            white-space: pre-line;
        }
        .error.active { display: block; }
        .resumo {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .stat {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.9rem 1rem;
        }
        .stat-label { font-size: 0.7rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.04em; font-weight: 600; }
        .stat-value { font-size: 1.5rem; font-weight: 700; margin-top: 0.2rem; }
        .stat-value.sistema { color: #2563eb; }
        .stat-value.aleatorio { color: #6b7280; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        th, td {
            padding: 0.6rem 0.75rem;
            text-align: right;
            border-bottom: 1px solid #e5e7eb;
        }
        th:first-child, td:first-child { text-align: left; }
        th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        tr:last-child td { border-bottom: none; }
        .badge {
            display: inline-block;
            padding: 0.15rem 0.45rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .badge-ok { background: #dcfce7; color: #166534; }
        .badge-warn { background: #fef3c7; color: #92400e; }
        .badge-bad { background: #fee2e2; color: #991b1b; }
        .interpretacao {
            font-size: 0.85rem;
            color: #4b5563;
            line-height: 1.6;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        .interpretacao strong { color: #1f2937; }
        .params-info {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f3f4f6;
        }
        .checkbox-row {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #4b5563;
        }
        .checkbox-row input[type="checkbox"] { margin-top: 0.2rem; }
        .checkbox-row label { font-weight: 400; margin: 0; font-size: 0.85rem; }
        .warning-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-top: 1rem;
            line-height: 1.5;
        }
        .warning-box code {
            background: #fef3c7;
            padding: 0.1rem 0.35rem;
            border-radius: 3px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Backtest — Lotofácil</h1>
    <p class="subtitle">
        Compara o gerador contra baseline aleatório. O modo "honesto" usa apenas concursos
        anteriores ao alvo (sem vazamento de dados) e roda em background para não estourar o timeout do PHP.
    </p>

    <div class="card">
        <div class="error" id="errorBox"></div>

        <form id="formBacktest" novalidate>
            <div class="grid">
                <div>
                    <label for="janela">Janela (histórico)</label>
                    <input type="number" id="janela" name="janela" value="100" min="10" max="500" required>
                    <div class="hint">Concursos anteriores que o gerador enxerga em cada rodada.</div>
                </div>
                <div>
                    <label for="rodadas">Rodadas (alvos)</label>
                    <input type="number" id="rodadas" name="rodadas" value="100" min="1" max="1000" required>
                    <div class="hint">Quantos concursos serão avaliados.</div>
                </div>
                <div>
                    <label for="jogos">Jogos por rodada</label>
                    <input type="number" id="jogos" name="jogos" value="10" min="1" max="50" required>
                    <div class="hint">Quantos jogos o sistema gera por concurso-alvo.</div>
                </div>
                <div>
                    <label for="candidatos_base">Candidatos base</label>
                    <input type="number" id="candidatos_base" name="candidatos_base" value="1000" min="100" max="20000" required>
                    <div class="hint">Menor = mais rápido. 1000 já dá resultado estável.</div>
                </div>
                <div>
                    <label for="inicio">Concurso inicial (opcional)</label>
                    <input type="number" id="inicio" name="inicio" placeholder="ex.: {{ $primeiroConcurso }}" min="1">
                    <div class="hint">Deixe vazio para começar após a janela.</div>
                </div>
            </div>

            <div class="checkbox-row">
                <input type="checkbox" id="modoSincrono" name="modoSincrono">
                <label for="modoSincrono">
                    Execução síncrona (dev). Use só com parâmetros pequenos: sem fila, o request bloqueia a aba.
                </label>
            </div>

            <div class="actions">
                <button type="submit" class="btn-primary" id="btnRodar">Rodar backtest honesto</button>
                <button type="button" class="btn-secondary" id="btnRapido">Rodar rápido (com vazamento)</button>
                <span class="status" id="status"></span>
            </div>

            <div class="progress" id="progress">
                <div class="progress-bar" id="progressBar"></div>
            </div>

            <div class="params-info">
                Banco atual: <strong>{{ $totalConcursos }}</strong> concursos
                ({{ $primeiroConcurso }} → {{ $ultimoConcurso }}).
            </div>
        </form>

        <div class="warning-box" id="workerWarning" style="display: none;">
            ⚠️ O status ficou em "queued" por mais de 10s. Provavelmente o worker da fila não está rodando.
            Em outro terminal, execute: <code>php artisan queue:work</code>
        </div>
    </div>

    <div class="card" id="resultadoCard" style="display: none;">
        <div class="resumo" id="resumo"></div>

        <table>
            <thead>
                <tr>
                    <th>Acertos (exatos)</th>
                    <th>Sistema</th>
                    <th>Aleatório</th>
                    <th>Esperado</th>
                    <th>Sist./Esp.</th>
                    <th>Aleat./Esp.</th>
                </tr>
            </thead>
            <tbody id="tabelaBody"></tbody>
        </table>

        <div class="interpretacao" id="interpretacao"></div>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('formBacktest');
    const btnRodar = document.getElementById('btnRodar');
    const btnRapido = document.getElementById('btnRapido');
    const status = document.getElementById('status');
    const progress = document.getElementById('progress');
    const progressBar = document.getElementById('progressBar');
    const errorBox = document.getElementById('errorBox');
    const workerWarning = document.getElementById('workerWarning');
    const resultadoCard = document.getElementById('resultadoCard');
    const resumo = document.getElementById('resumo');
    const tabelaBody = document.getElementById('tabelaBody');
    const interpretacao = document.getElementById('interpretacao');

    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const URLS = {
        enqueue: '{{ route('backtest.enqueue') }}',
        status: '{{ url('/backtest/status') }}',
        resultado: '{{ url('/backtest/resultado') }}',
        honesto: '{{ route('backtest.honesto') }}',
        rapido: '{{ route('backtest.rapido') }}',
    };

    function setLoading(loading, msg) {
        btnRodar.disabled = loading;
        btnRapido.disabled = loading;
        status.textContent = msg || '';
        if (loading) {
            progress.classList.add('active');
            progressBar.style.width = '5%';
        } else {
            progressBar.style.width = '100%';
            setTimeout(() => {
                progress.classList.remove('active');
                progressBar.style.width = '0';
            }, 500);
        }
    }

    function setProgress(pct) {
        progressBar.style.width = Math.min(100, Math.max(0, pct)) + '%';
    }

    function showError(msg) {
        errorBox.textContent = msg;
        errorBox.classList.add('active');
        resultadoCard.style.display = 'none';
    }

    function clearError() {
        errorBox.textContent = '';
        errorBox.classList.remove('active');
        workerWarning.style.display = 'none';
    }

    function badge(ratio) {
        if (ratio === null || ratio === undefined) return '';
        const cls = ratio >= 1.15 ? 'badge-ok' : (ratio <= 0.85 ? 'badge-bad' : 'badge-warn');
        return `<span class="badge ${cls}">${ratio.toFixed(2)}×</span>`;
    }

    function render(data) {
        const p = data.params;
        const faixas = data.faixas || [];

        resumo.innerHTML = `
            <div class="stat">
                <div class="stat-label">Total de jogos</div>
                <div class="stat-value">${data.total_jogos}</div>
            </div>
            <div class="stat">
                <div class="stat-label">Sistema ≥ 11</div>
                <div class="stat-value sistema">${data.resumo.sistema_maior_ou_igual_11}</div>
            </div>
            <div class="stat">
                <div class="stat-label">Aleatório ≥ 11</div>
                <div class="stat-value aleatorio">${data.resumo.aleatorio_maior_ou_igual_11}</div>
            </div>
            <div class="stat">
                <div class="stat-label">Janela / Rodadas</div>
                <div class="stat-value" style="font-size: 1.1rem;">${p.janela} / ${p.rodadas}</div>
            </div>
        `;

        tabelaBody.innerHTML = '';
        faixas.forEach(f => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>= ${f.acertos}</td>
                <td>${f.sistema}</td>
                <td>${f.aleatorio}</td>
                <td>${f.esperado ?? '-'}</td>
                <td>${badge(f.ratio_sistema)}</td>
                <td>${badge(f.ratio_aleatorio)}</td>
            `;
            tabelaBody.appendChild(tr);
        });

        const sist = data.resumo.sistema_maior_ou_igual_11;
        const aleat = data.resumo.aleatorio_maior_ou_igual_11;
        const total = data.total_jogos;
        const diff = Math.abs(sist - aleat);
        const pctDiff = total > 0 ? (diff / total * 100).toFixed(2) : '0';

        // σ da diferença entre duas amostras binomiais (p ≈ 0,09 para ≥ 11)
        const sigma = Math.sqrt(2 * total * 0.09 * 0.91);
        const limiteRuido = 2 * sigma;

        let texto = `<strong>Parâmetros:</strong> janela ${p.janela}, ${p.rodadas} rodadas, `;
        texto += `${p.jogos_por_rodada} jogos por rodada (concursos ${p.inicio} → ${p.fim}).<br>`;
        texto += `Diferença sistema vs. aleatório em ≥ 11: <strong>${diff}</strong> jogos (${pctDiff}% do total). `;
        texto += `Limite de ruído (2σ): <strong>${limiteRuido.toFixed(1)}</strong> jogos.<br>`;

        if (diff <= limiteRuido) {
            texto += 'Isso é <strong>ruído amostral</strong> — os pesos/bônus não agregam valor preditivo.';
        } else {
            texto += 'Diferença acima de 2σ; rode com mais rodadas para confirmar antes de concluir.';
        }
        interpretacao.innerHTML = texto;

        resultadoCard.style.display = 'block';
    }

    function coletarParams(incluirCandidatos) {
        const p = {
            janela: +document.getElementById('janela').value,
            rodadas: +document.getElementById('rodadas').value,
            jogos: +document.getElementById('jogos').value,
        };
        if (incluirCandidatos) {
            p.candidatos_base = +document.getElementById('candidatos_base').value;
        }
        const inicio = document.getElementById('inicio').value;
        if (inicio) p.inicio = +inicio;
        return p;
    }

    async function executarSincrono(params) {
        setLoading(true, 'Rodando (síncrono)...');
        try {
            const qs = new URLSearchParams(params).toString();
            const resp = await fetch(`${URLS.honesto}?${qs}`, { headers: { 'Accept': 'application/json' } });
            const data = await resp.json();
            if (!resp.ok) {
                showError(data.erro || data.message || 'Erro no backtest.');
                return;
            }
            render(data);
        } catch (e) {
            showError('Falha: ' + e.message);
        } finally {
            setLoading(false);
        }
    }

    async function executarHonesto(params) {
        clearError();
        setLoading(true, 'Enfileirando...');
        try {
            const resp = await fetch(URLS.enqueue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                },
                body: JSON.stringify(params),
            });
            const data = await resp.json();
            if (!resp.ok || !data.job_id) {
                showError(data.erro || data.message || 'Não foi possível enfileirar o job.');
                setLoading(false);
                return;
            }

            const jobId = data.job_id;
            const inicioPoll = Date.now();
            let avisouWorker = false;

            while (true) {
                await new Promise(r => setTimeout(r, 1000));

                if (Date.now() - inicioPoll > 30 * 60 * 1000) {
                    showError('Timeout no polling (30 min). Verifique o worker da fila.');
                    setLoading(false);
                    return;
                }

                let st;
                try {
                    const r = await fetch(`${URLS.status}/${jobId}`);
                    st = await r.json();
                } catch (e) {
                    continue;
                }

                if (!st || st.status === undefined) {
                    continue;
                }

                if (st.status === 'queued') {
                    status.textContent = 'Na fila...';
                    if (!avisouWorker && Date.now() - inicioPoll > 10000) {
                        workerWarning.style.display = 'block';
                        avisouWorker = true;
                    }
                } else if (st.status === 'running') {
                    setProgress(st.progresso || 5);
                    status.textContent = `Rodando... ${st.progresso || 0}%`;
                } else if (st.status === 'done') {
                    setProgress(100);
                    status.textContent = 'Concluído.';
                    const res = await (await fetch(`${URLS.resultado}/${jobId}`)).json();
                    if (res.erro) {
                        showError(res.erro);
                    } else {
                        render(res);
                    }
                    setLoading(false);
                    return;
                } else if (st.status === 'error') {
                    showError(st.erro || 'Erro no backtest.');
                    setLoading(false);
                    return;
                }
            }
        } catch (e) {
            showError('Falha: ' + e.message);
            setLoading(false);
        }
    }

    async function executarRapido() {
        clearError();
        setLoading(true, 'Rodando rápido...');
        try {
            const params = {
                rodadas: +document.getElementById('rodadas').value,
                jogos: +document.getElementById('jogos').value,
            };
            const qs = new URLSearchParams(params).toString();
            const resp = await fetch(`${URLS.rapido}?${qs}`, { headers: { 'Accept': 'application/json' } });
            const data = await resp.json();
            if (!resp.ok) {
                showError(data.erro || data.message || 'Erro no backtest rápido.');
                return;
            }
            render(data);
        } catch (e) {
            showError('Falha: ' + e.message);
        } finally {
            setLoading(false);
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const sync = document.getElementById('modoSincrono').checked;
        const params = coletarParams(true);
        if (sync) {
            executarSincrono(params);
        } else {
            executarHonesto(params);
        }
    });

    btnRapido.addEventListener('click', function () {
        executarRapido();
    });
})();
</script>
</body>
</html>
