<?php
ini_set('display_errors', 0); // Desativar exibição de erros em produção
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

session_start();
require_once realpath(__DIR__ . '/../includes/db.php');
require_once realpath(__DIR__ . '/../includes/auth.php');
require_once realpath(__DIR__ . '/../includes/functions.php');

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pdo = getDB();
list($ultimo_concurso, $ultimo_sorteio) = getUltimoConcurso($pdo);

include realpath(__DIR__ . '/../templates/header.php');
?>

<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="gerar-tab" data-bs-toggle="tab" data-bs-target="#gerar" type="button" role="tab" aria-controls="gerar" aria-selected="true">Gerar Jogos</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="temperatura-tab" data-bs-toggle="tab" data-bs-target="#temperatura" type="button" role="tab" aria-controls="temperatura" aria-selected="false">Temperatura dos Números</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="adicionar-tab" data-bs-toggle="tab" data-bs-target="#adicionar" type="button" role="tab" aria-controls="adicionar" aria-selected="false">Adicionar Resultado</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="jogos-gerados-tab" data-bs-toggle="tab" data-bs-target="#jogos_gerados" type="button" role="tab" aria-controls="jogos_gerados" aria-selected="false">Jogos Gerados</button>
    </li>
</ul>

<div class="tab-content" id="myTabContent">
    <!-- Tab: Gerar Jogos -->
    <div class="tab-pane fade show active" id="gerar" role="tabpanel" aria-labelledby="gerar-tab">
        <h2 class="mt-3">Gerar Jogos</h2>
        <form id="gerarForm" action="processar_jogos.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="mb-3">
                <label for="quantidade_numeros" class="form-label">Quantidade de Números por Jogo (15-20):</label>
                <input type="number" class="form-control" id="quantidade_numeros" name="quantidade_numeros" min="15" max="20" value="15" required>
            </div>
            <div class="mb-3">
                <label for="quantidade_jogos" class="form-label">Quantidade de Jogos:</label>
                <input type="number" class="form-control" id="quantidade_jogos" name="quantidade_jogos" min="1" value="10" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Números Fixos (vermelho):</label>
                <div class="volante">
                    <?php for ($i = 1; $i <= 25; $i++): ?>
                        <div id="fixo-<?= $i ?>" class="numero" onclick="toggleFixo(<?= $i ?>)"><?= $i ?></div>
                    <?php endfor; ?>
                </div>
                <input type="hidden" id="numeros_fixos" name="numeros_fixos">
            </div>
            <div class="mb-3">
                <label class="form-label">Números Excluídos (azul):</label>
                <div class="volante">
                    <?php for ($i = 1; $i <= 25; $i++): ?>
                        <div id="excluido-<?= $i ?>" class="numero" onclick="toggleExcluido(<?= $i ?>)"><?= $i ?></div>
                    <?php endfor; ?>
                </div>
                <input type="hidden" id="numeros_excluidos" name="numeros_excluidos">
            </div>
            <div class="mb-3">
                <label class="form-label">Estratégias:</label>
                <div class="estrategias-grid">
                    <?php
                    $estrategias = [
                        'frequencia' => 'Usa os 10-12 números mais sorteados nos últimos 50 concursos.',
                        'primos' => 'Inclui 3 a 5 números primos no jogo.',
                        'repeticao' => 'Reutiliza 6 a 9 números do último sorteio.',
                        'sequencias' => 'Inclui trio comum (20-21-22 ou 23-24-25).',
                        'atrasados' => 'Inclui 1 a 2 números não sorteados nos últimos 50 concursos.',
                        'soma' => 'Ajusta os números para soma entre 180 e 220.',
                        'desdobramento' => 'Gera jogos a partir de 18 dezenas baseadas em frequência.',
                        'clustering' => 'Prioriza números de zonas quentes baseadas nos últimos sorteios.'
                    ];
                    foreach ($estrategias as $estrategia => $tooltip): ?>
                        <div>
                            <button type="button" id="btn-<?= $estrategia ?>" class="toggle-btn off" onclick="toggleEstrategia('<?= $estrategia ?>')" title="<?= htmlspecialchars($tooltip) ?>">
                                <?= ucfirst($estrategia) ?>
                            </button>
                            <input type="hidden" id="estrategia-<?= $estrategia ?>" name="estrategias[]" value="">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Gerar Apostas</button>
        </form>
    </div>

    <!-- Tab: Temperatura dos Números -->
    <div class="tab-pane fade" id="temperatura" role="tabpanel" aria-labelledby="temperatura-tab">
        <?php include realpath(__DIR__ . '/temperatura.php'); ?>
    </div>

    <!-- Tab: Adicionar Resultado -->
    <div class="tab-pane fade" id="adicionar" role="tabpanel" aria-labelledby="adicionar-tab">
        <h2 class="mt-3">Adicionar Resultado</h2>
        <form action="adicionar_resultado.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="mb-3">
                <label for="concurso" class="form-label">Concurso:</label>
                <input type="number" class="form-control" id="concurso" name="concurso" required>
            </div>
            <div class="mb-3">
                <label for="numeros" class="form-label">Números (15, separados por vírgula):</label>
                <input type="text" class="form-control" id="numeros" name="numeros" required>
            </div>
            <button type="submit" class="btn btn-primary">Adicionar</button>
        </form>
    </div>

    <!-- Tab: Jogos Gerados -->
    <div class="tab-pane fade" id="jogos_gerados" role="tabpanel" aria-labelledby="jogos-gerados-tab">
        <h2 class="mt-3">Jogos Gerados</h2>
        <?php
        try {
            $stmt = $pdo->prepare("SELECT lote_id, data_geracao, concurso, pdf_path, txt_path FROM jogos_gerados WHERE user_id = ? ORDER BY data_geracao DESC");
            $stmt->execute([$_SESSION['user_id']]);
            $jogos_gerados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($jogos_gerados) {
                echo '<div class="table-responsive">';
                echo '<table class="table table-striped table-bordered">';
                echo '<thead class="table-dark"><tr>';
                echo '<th>ID do Lote</th><th>Data e Hora</th><th>Concurso</th><th>Download PDF</th><th>Download TXT</th><th>Resultados</th>';
                echo '</tr></thead>';
                echo '<tbody>';
                foreach ($jogos_gerados as $jogo) {
                    $data_geracao = date('d/m/Y H:i:s', strtotime($jogo['data_geracao']));
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM resultados WHERE concurso = ?");
                    $stmt->execute([$jogo['concurso']]);
                    $resultado_existe = $stmt->fetchColumn() > 0;
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($jogo['lote_id']) . '</td>';
                    echo '<td>' . htmlspecialchars($data_geracao) . '</td>';
                    echo '<td>' . htmlspecialchars($jogo['concurso']) . '</td>';
                    echo '<td>';
                    if (file_exists($jogo['pdf_path'])) {
                        echo '<a href="/downloads/lotofacil_' . htmlspecialchars($jogo['lote_id']) . '.pdf" download class="btn btn-sm btn-outline-primary">PDF</a>';
                    }
                    echo '</td>';
                    echo '<td>';
                    if (file_exists($jogo['txt_path'])) {
                        echo '<a href="/downloads/lotofacil_' . htmlspecialchars($jogo['lote_id']) . '.txt" download class="btn btn-sm btn-outline-primary">TXT</a>';
                    }
                    echo '</td>';
                    echo '<td>';
                    echo '<button class="btn btn-sm btn-primary result-btn" onclick="verResultados(\'' . htmlspecialchars($jogo['lote_id']) . '\', ' . $jogo['concurso'] . ')"' . ($resultado_existe ? '' : ' disabled') . '>Ver Resultados</button>';
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
                echo '</div>';
            } else {
                echo '<div class="alert alert-info">Nenhum jogo gerado ainda.</div>';
            }
        } catch (Exception $e) {
            echo '<div class="alert alert-danger">Erro ao carregar Jogos Gerados: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
</div>

<script>
    let fixos = [];
    let excluidos = [];

    function toggleFixo(numero) {
        const elemento = document.getElementById(`fixo-${numero}`);
        if (fixos.includes(numero)) {
            fixos = fixos.filter(n => n !== numero);
            elemento.style.backgroundColor = '';
        } else {
            fixos.push(numero);
            elemento.style.backgroundColor = 'red';
        }
        document.getElementById('numeros_fixos').value = fixos.join(',');
    }

    function toggleExcluido(numero) {
        const elemento = document.getElementById(`excluido-${numero}`);
        if (excluidos.includes(numero)) {
            excluidos = excluidos.filter(n => n !== numero);
            elemento.style.backgroundColor = '';
        } else {
            excluidos.push(numero);
            elemento.style.backgroundColor = 'blue';
        }
        document.getElementById('numeros_excluidos').value = excluidos.join(',');
    }

    function toggleEstrategia(estrategia) {
        const btn = document.getElementById(`btn-${estrategia}`);
        const input = document.getElementById(`estrategia-${estrategia}`);
        if (btn.classList.contains('off')) {
            btn.classList.remove('off');
            btn.classList.add('on');
            btn.style.backgroundColor = 'green';
            input.value = estrategia;
        } else {
            btn.classList.remove('on');
            btn.classList.add('off');
            btn.style.backgroundColor = '';
            input.value = '';
        }
    }

    function verResultados(lote_id, concurso) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', `ver_resultados.php?lote_id=${encodeURIComponent(lote_id)}&concurso=${encodeURIComponent(concurso)}`, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                let acertos = JSON.parse(xhr.responseText);
                alert('Acertos por jogo:\n' + acertos.join('\n'));
            } else {
                alert('Erro ao conferir resultados: ' + xhr.statusText);
            }
        };
        xhr.onerror = function() {
            alert('Erro na requisição');
        };
        xhr.send();
    }
</script>

<?php include realpath(__DIR__ . '/../templates/footer.php'); ?>