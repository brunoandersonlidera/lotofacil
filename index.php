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

function get_ultimo_concurso($pdo)
{
    try {
        $stmt = $pdo->query("SELECT concurso, numeros FROM resultados ORDER BY concurso DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? [$row['concurso'], json_decode($row['numeros'])] : [0, []];
    } catch (Exception $e) {
        error_log("Erro em get_ultimo_concurso: " . $e->getMessage(), 3, "erros.log");
        return [0, []];
    }
}

list($ultimo_concurso, $ultimo_sorteio) = get_ultimo_concurso($pdo);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador Lotofácil</title>
    <style>
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tab {
            cursor: pointer;
            padding: 10px;
            background-color: #f0f0f0;
        }

        .tab.active {
            background-color: #007bff;
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .numero {
            cursor: pointer;
            display: inline-block;
            margin: 5px;
            padding: 5px;
            border: 1px solid #ccc;
        }

        .fixo {
            background-color: red;
            color: white;
        }

        .excluido {
            background-color: blue;
            color: white;
        }

        .toggle-btn {
            cursor: pointer;
            padding: 5px 10px;
            border: none;
        }

        .toggle-btn.off {
            background-color: #ccc;
        }

        .toggle-btn.on {
            background-color: #28a745;
            color: white;
        }

        .estrategias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }

        .submit-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        .result-btn {
            padding: 5px 10px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
        }

        .result-btn:disabled {
            cursor: not-allowed;
            opacity: 0.5;
            background-color: #ccc;
        }
    </style>
    <script defer>
        console.log('Script carregado no head');

        function showTab(tabId) {
            console.log('Tentando abrir aba: ' + tabId);
            try {
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                    console.log('Removendo active de: ' + tab.id);
                });
                document.querySelectorAll('.tab').forEach(tab => {
                    tab.classList.remove('active');
                });
                const tabContent = document.getElementById(tabId);
                const tabButton = document.querySelector(`[onclick="showTab('${tabId}')"]`);
                if (tabContent && tabButton) {
                    tabContent.classList.add('active');
                    tabButton.classList.add('active');
                    console.log('Aba ' + tabId + ' ativada');
                } else {
                    console.error('Elemento não encontrado para aba: ' + tabId);
                }
            } catch (e) {
                console.error('Erro em showTab: ' + e.message);
            }
        }

        function toggleFixo(numero) {
            console.log('Toggle fixo: ' + numero);
            try {
                const el = document.getElementById('fixo-' + numero);
                if (el && !el.classList.contains('excluido')) {
                    el.classList.toggle('fixo');
                    updateNumeros('numeros_fixos', '.fixo');
                } else {
                    console.error('Elemento fixo-' + numero + ' não encontrado ou já excluído');
                }
            } catch (e) {
                console.error('Erro em toggleFixo: ' + e.message);
            }
        }

        function toggleExcluido(numero) {
            console.log('Toggle excluido: ' + numero);
            try {
                const el = document.getElementById('excluido-' + numero);
                if (el && !el.classList.contains('fixo')) {
                    el.classList.toggle('excluido');
                    updateNumeros('numeros_excluidos', '.excluido');
                } else {
                    console.error('Elemento excluido-' + numero + ' não encontrado ou já fixo');
                }
            } catch (e) {
                console.error('Erro em toggleExcluido: ' + e.message);
            }
        }

        function updateNumeros(inputId, selector) {
            console.log('Atualizando ' + inputId);
            try {
                const nums = Array.from(document.querySelectorAll(selector)).map(el => el.textContent.trim());
                const input = document.getElementById(inputId);
                if (input) {
                    input.value = nums.join(', ');
                    console.log(inputId + ' atualizado para: ' + input.value);
                } else {
                    console.error('Input ' + inputId + ' não encontrado');
                }
            } catch (e) {
                console.error('Erro em updateNumeros: ' + e.message);
            }
        }

        function toggleEstrategia(estrategia) {
            console.log('Toggle estrategia: ' + estrategia);
            try {
                const btn = document.getElementById('btn-' + estrategia);
                const input = document.getElementById('estrategia-' + estrategia);
                if (btn && input) {
                    if (btn.classList.contains('off')) {
                        btn.classList.remove('off');
                        btn.classList.add('on');
                        input.value = estrategia;
                    } else {
                        btn.classList.remove('on');
                        btn.classList.add('off');
                        input.value = '';
                    }
                    console.log('Estrategia ' + estrategia + ' agora é: ' + input.value);
                } else {
                    console.error('Botão ou input para ' + estrategia + ' não encontrado');
                }
            } catch (e) {
                console.error('Erro em toggleEstrategia: ' + e.message);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM carregado');
            try {
                showTab('gerar');
                toggleEstrategia('frequencia');
                toggleEstrategia('sequencias');
                toggleEstrategia('soma');
            } catch (e) {
                console.error('Erro no DOMContentLoaded: ' + e.message);
            }
        });
    </script>
</head>

<body>
    <div class="container">
        <div class="tabs">
            <div class="tab active" onclick="showTab('gerar')">Gerar Jogos</div>
            <div class="tab" onclick="showTab('temperatura')">Temperatura dos Números</div>
            <div class="tab" onclick="showTab('adicionar')">Adicionar Resultado</div>
            <div class="tab" onclick="showTab('jogos_gerados')">Jogos Gerados</div>
        </div>

        <!-- Tab: Gerar Jogos -->
        <div id="gerar" class="tab-content active">
            <h2>Gerar Jogos</h2>
            <form id="gerarForm" action="processar_jogos.php" method="POST">
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
            <?php
            try {
                include 'temperatura.php';
            } catch (Exception $e) {
                echo '<p>Erro ao carregar Temperatura dos Números: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
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

        <!-- Tab: Jogos Gerados -->
        <div id="jogos_gerados" class="tab-content">
            <h2>Jogos Gerados</h2>
            <?php
            try {
                $stmt = $pdo->prepare("SELECT lote_id, data_geracao, concurso, pdf_path, txt_path FROM jogos_gerados WHERE user_id = ? ORDER BY data_geracao DESC");
                $stmt->execute([$_SESSION['user_id']]);
                $jogos_gerados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($jogos_gerados) {
                    echo '<table style="width: 100%; border-collapse: collapse;">';
                    echo '<thead><tr>';
                    echo '<th style="border: 1px solid #ccc; padding: 5px;">ID do Lote</th>';
                    echo '<th style="border: 1px solid #ccc; padding: 5px;">Data e Hora</th>';
                    echo '<th style="border: 1px solid #ccc; padding: 5px;">Concurso</th>';
                    echo '<th style="border: 1px solid #ccc; padding: 5px;">Download PDF</th>';
                    echo '<th style="border: 1px solid #ccc; padding: 5px;">Download TXT</th>';
                    echo '<th style="border: 1px solid #ccc; padding: 5px;">Resultados</th>';
                    echo '</tr></thead>';
                    echo '<tbody>';
                    foreach ($jogos_gerados as $jogo) {
                        $data_geracao = date('d/m/Y H:i:s', strtotime($jogo['data_geracao']));
                        // Verificar se o resultado do concurso existe
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM resultados WHERE concurso = ?");
                        $stmt->execute([$jogo['concurso']]);
                        $resultado_existe = $stmt->fetchColumn() > 0;
                        echo '<tr>';
                        echo '<td style="border: 1px solid #ccc; padding: 5px;">' . htmlspecialchars($jogo['lote_id']) . '</td>';
                        echo '<td style="border: 1px solid #ccc; padding: 5px;">' . htmlspecialchars($data_geracao) . '</td>';
                        echo '<td style="border: 1px solid #ccc; padding: 5px;">' . htmlspecialchars($jogo['concurso']) . '</td>';
                        echo '<td style="border: 1px solid #ccc; padding: 5px;">';
                        if (file_exists($jogo['pdf_path'])) {
                            echo '<a href="' . htmlspecialchars($jogo['pdf_path']) . '" download>PDF</a>';
                        }
                        echo '</td>';
                        echo '<td style="border: 1px solid #ccc; padding: 5px;">';
                        if (file_exists($jogo['txt_path'])) {
                            echo '<a href="' . htmlspecialchars($jogo['txt_path']) . '" download>TXT</a>';
                        }
                        echo '</td>';
                        echo '<td style="border: 1px solid #ccc; padding: 5px;">';
                        echo '<button class="result-btn" onclick="verResultados(\'' . $jogo['lote_id'] . '\', ' . $jogo['concurso'] . ')"' . ($resultado_existe ? '' : ' disabled') . '>Ver Resultados</button>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                } else {
                    echo '<p>Nenhum jogo gerado ainda.</p>';
                }
            } catch (Exception $e) {
                echo '<p>Erro ao carregar Jogos Gerados: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>

        <script>
            function verResultados(lote_id, concurso) {
                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'conferir.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        alert('Acertos:\n' + xhr.responseText.replace(/<br>/g, '\n'));
                    } else {
                        alert('Erro ao conferir resultados: ' + xhr.statusText);
                    }
                };
                xhr.onerror = function() {
                    alert('Erro na requisição');
                };
                xhr.send('lote_id=' + encodeURIComponent(lote_id) + '&concurso=' + encodeURIComponent(concurso));
            }
        </script>

</body>

</html>