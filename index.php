<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = getDB();

function get_ultimo_concurso($pdo) {
    try {
        $stmt = $pdo->query("SELECT concurso, numeros FROM resultados ORDER BY concurso DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? [$row['concurso'], json_decode($row['numeros'])] : [0, []];
    } catch (Exception $e) {
        error_log("Erro em get_ultimo_concurso: " . $e->getMessage(), 3, "erros.log");
        return [0, []];
    }
}

function analisar_frequencia_ultimos_n($pdo, $n = 50) {
    try {
        $stmt = $pdo->prepare("SELECT numeros FROM resultados ORDER BY concurso DESC LIMIT ?");
        $stmt->execute([$n]);
        $numeros = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $todos_numeros = [];
        foreach ($numeros as $n) {
            $todos_numeros = array_merge($todos_numeros, json_decode($n));
        }
        return array_count_values($todos_numeros);
    } catch (Exception $e) {
        error_log("Erro em analisar_frequencia_ultimos_n: " . $e->getMessage(), 3, "erros.log");
        return [];
    }
}

function analisar_frequencia_ate_concurso($pdo, $concurso, $n = 50) {
    try {
        $stmt = $pdo->prepare("SELECT numeros FROM resultados WHERE concurso < ? ORDER BY concurso DESC LIMIT ?");
        $stmt->execute([$concurso, $n]);
        $numeros = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $todos_numeros = [];
        foreach ($numeros as $n) {
            $todos_numeros = array_merge($todos_numeros, json_decode($n));
        }
        return array_count_values($todos_numeros);
    } catch (Exception $e) {
        error_log("Erro em analisar_frequencia_ate_concurso: " . $e->getMessage(), 3, "erros.log");
        return [];
    }
}

function get_temperatura_numeros($pdo) {
    $freq = analisar_frequencia_ultimos_n($pdo, 50);
    if (empty($freq)) return ['quentes' => [], 'mornos' => [], 'frios' => [], 'congelados' => range(1, 25), 'quatro_mais_quentes' => [], 'frequencias' => array_fill(1, 25, 0)];
    arsort($freq);
    $sorted_freq = array_keys($freq);
    $quentes = array_slice($sorted_freq, 0, 12);
    $mornos = array_slice($sorted_freq, 12, 6);
    $frios = array_slice($sorted_freq, 18, 5);
    $congelados = array_diff(range(1, 25), array_keys($freq));
    $quatro_mais_quentes = array_slice($sorted_freq, 0, 4);
    return [
        'quentes' => $quentes,
        'mornos' => $mornos,
        'frios' => $frios,
        'congelados' => $congelados,
        'quatro_mais_quentes' => $quatro_mais_quentes,
        'frequencias' => array_merge(array_fill(1, 25, 0), $freq)
    ];
}

function simular_previsoes_ultimos_20($pdo) {
    try {
        $stmt = $pdo->query("SELECT concurso, numeros FROM resultados ORDER BY concurso DESC LIMIT 20");
        $ultimos_20 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $simulacao = [];
        foreach ($ultimos_20 as $row) {
            $concurso = $row['concurso'];
            $resultado = json_decode($row['numeros']);
            $freq = analisar_frequencia_ate_concurso($pdo, $concurso);
            arsort($freq);
            $previsao = array_slice(array_keys($freq), 0, 15);
            $acertos = count(array_intersect($previsao, $resultado));
            $simulacao[] = [
                'concurso' => $concurso,
                'previsao' => $previsao,
                'resultado' => $resultado,
                'acertos' => $acertos
            ];
        }
        return array_reverse($simulacao);
    } catch (Exception $e) {
        error_log("Erro em simular_previsoes_ultimos_20: " . $e->getMessage(), 3, "erros.log");
        return [];
    }
}

list($ultimo_concurso, $ultimo_sorteio) = get_ultimo_concurso($pdo);
$temperatura = get_temperatura_numeros($pdo);
$simulacao = simular_previsoes_ultimos_20($pdo);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador Lotofácil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="tabs">
            <div class="tab active" onclick="showTab('gerar')">Gerar Jogos</div>
            <div class="tab" onclick="showTab('temperatura')">Temperatura dos Números</div>
            <div class="tab" onclick="showTab('adicionar')">Adicionar Resultado</div>
            <div class="tab" onclick="window.location.href='jogos_gerados.php'">Jogos Gerados</div>
        </div>

        <!-- Tab: Gerar Jogos -->
        <div id="gerar" class="tab-content active">
            <h2>Gerar Jogos</h2>
            <form action="processar_jogos.php" method="POST">
                <label>Quantidade de Números por Jogo (15-20):</label>
                <input type="number" name="quantidade_numeros" min="15" max="20" value="15" required>
                <label>Quantidade de Jogos:</label>
                <input type="number" name="quantidade_jogos" min="1" value="10" required>
                <label>Números Fixos (vermelho):</label>
                <div class="volante">
                    <?php for ($i = 1; $i <= 25; $i++): ?>
                        <div id="fixo-<?= $i ?>" class="numero" onclick="toggleFixo(<?= $i ?>)"><?= $i ?></div>
                    <?php endfor; ?>
                </div>
                <input type="hidden" id="numeros_fixos" name="numeros_fixos">
                <label>Números Excluídos (azul):</label>
                <div class="volante">
                    <?php for ($i = 1; $i <= 25; $i++): ?>
                        <div id="excluido-<?= $i ?>" class="numero" onclick="toggleExcluido(<?= $i ?>)"><?= $i ?></div>
                    <?php endfor; ?>
                </div>
                <input type="hidden" id="numeros_excluidos" name="numeros_excluidos">
                <label>Estratégias:</label>
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
                            <button type="button" id="btn-<?= $estrategia ?>" class="toggle-btn off" onclick="toggleEstrategia('<?= $estrategia ?>')">
                                <?= ucfirst($estrategia) ?>
                                <span class="tooltip"><?= $tooltip ?></span>
                            </button>
                            <input type="hidden" id="estrategia-<?= $estrategia ?>" name="estrategias[]" value="">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="submit-btn">Gerar Apostas</button>
            </form>
        </div>

        <!-- Tab: Temperatura dos Números -->
        <div id="temperatura" class="tab-content">
            <h2>Temperatura dos Números</h2>
            <div class="temp-section">
                <div class="temp-title">🔥 Quentes (12 mais frequentes)</div>
                <div class="temp-numbers">
                    <?php foreach ($temperatura['quentes'] as $num): ?>
                        <span class="temp-number quente <?= in_array($num, $temperatura['quatro_mais_quentes']) ? 'top-4' : '' ?>" onclick="showFreq(<?= $num ?>)"><?= $num ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="temp-section">
                <div class="temp-title">🌞 Mornos (13º ao 18º)</div>
                <div class="temp-numbers">
                    <?php foreach ($temperatura['mornos'] as $num): ?>
                        <span class="temp-number morno" onclick="showFreq(<?= $num ?>)"><?= $num ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="temp-section">
                <div class="temp-title">❄️ Frios (19º ao 23º)</div>
                <div class="temp-numbers">
                    <?php foreach ($temperatura['frios'] as $num): ?>
                        <span class="temp-number frio" onclick="showFreq(<?= $num ?>)"><?= $num ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="temp-section">
                <div class="temp-title">🧊 Congelados (menos frequentes)</div>
                <div class="temp-numbers">
                    <?php foreach ($temperatura['congelados'] as $num): ?>
                        <span class="temp-number congelado" onclick="showFreq(<?= $num ?>)"><?= $num ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if (!empty($simulacao)): ?>
                <h3 style="color: #FF4444; text-align: center; margin-top: 30px;">Simulação dos Últimos 20 Concursos</h3>
                <table class="temp-table">
                    <tr>
                        <th>Concurso</th>
                        <th>Previsão</th>
                        <th>Resultado</th>
                        <th>Acertos</th>
                    </tr>
                    <?php foreach ($simulacao as $sim): ?>
                        <tr>
                            <td><?= $sim['concurso'] ?></td>
                            <td>
                                <?php foreach ($sim['previsao'] as $num): ?>
                                    <span class="prediction-span"><?= $num ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php foreach ($sim['resultado'] as $num): ?>
                                    <span class="result-span"><?= $num ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td><?= $sim['acertos'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <h3 style="color: #FF4444; text-align: center; margin-top: 30px;">Estatísticas de Acertos</h3>
                <canvas id="acertosChart" width="400" height="200"></canvas>
            <?php else: ?>
                <p style="text-align: center; color: #FF4444;">Nenhum dado disponível para simulação.</p>
            <?php endif; ?>
        </div>

        <!-- Tab: Adicionar Resultado -->
        <div id="adicionar" class="tab-content">
            <h2>Adicionar Resultado</h2>
            <form action="adicionar_resultado.php" method="POST">
                <label>Concurso:</label>
                <input type="number" name="concurso" required>
                <label>Números (15, separados por vírgula):</label>
                <input type="text" name="numeros" required>
                <button type="submit" class="submit-btn">Adicionar</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            document.querySelector(`[onclick="showTab('${tabId}')"]`).classList.add('active');
        }

        function toggleFixo(numero) {
            const el = document.getElementById('fixo-' + numero);
            if (!el.classList.contains('excluido')) {
                el.classList.toggle('fixo');
                updateNumeros('numeros_fixos', '.fixo');
            }
        }

        function toggleExcluido(numero) {
            const el = document.getElementById('excluido-' + numero);
            if (!el.classList.contains('fixo')) {
                el.classList.toggle('excluido');
                updateNumeros('numeros_excluidos', '.excluido');
            }
        }

        function updateNumeros(inputId, selector) {
            const nums = Array.from(document.querySelectorAll(selector)).map(el => el.textContent.trim());
            document.getElementById(inputId).value = nums.join(', ');
        }

        function toggleEstrategia(estrategia) {
            const btn = document.getElementById('btn-' + estrategia);
            const input = document.getElementById('estrategia-' + estrategia);
            if (btn.classList.contains('off')) {
                btn.classList.remove('off');
                btn.classList.add('on');
                input.value = estrategia;
            } else {
                btn.classList.remove('on');
                btn.classList.add('off');
                input.value = '';
            }
        }

        function showFreq(numero) {
            const freqs = <?php echo json_encode($temperatura['frequencias']); ?>;
            alert(`Frequência do número ${numero}: ${freqs[numero]} vezes nos últimos 50 concursos`);
        }

        window.onload = () => {
            showTab('gerar');
            toggleEstrategia('frequencia');
            toggleEstrategia('sequencias');
            toggleEstrategia('soma');

            <?php if (!empty($simulacao)): ?>
            const ctx = document.getElementById('acertosChart').getContext('2d');
            const simulacao = <?php echo json_encode($simulacao); ?>;
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: simulacao.map(s => s.concurso),
                    datasets: [{
                        label: 'Número de Acertos',
                        data: simulacao.map(s => s.acertos),
                        borderColor: '#FF4444',
                        backgroundColor: 'rgba(255, 68, 68, 0.2)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true, max: 15, title: { display: true, text: 'Acertos' } },
                        x: { title: { display: true, text: 'Concurso' } }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Acertos: ${context.raw}`;
                                }
                            }
                        }
                    }
                }
            });
            <?php endif; ?>
        };
    </script>
</body>
</html>