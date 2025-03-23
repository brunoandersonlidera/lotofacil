<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = getDB();
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
                <div class="mb-3">
                    <label for="quantidade_numeros" class="form-label">Quantidade de Números (15-20):</label>
                    <input type="number" class="form-control" id="quantidade_numeros" name="quantidade_numeros" min="15" max="20" value="15" required>
                </div>
                <div class="mb-3">
                    <label for="quantidade_jogos" class="form-label">Quantidade de Jogos:</label>
                    <input type="number" class="form-control" id="quantidade_jogos" name="quantidade_jogos" min="1" value="1" required>
                </div>
                <div class="mb-3">
                    <label>Números Fixos (vermelho):</label>
                    <div class="volante">
                        <?php for ($i = 1; $i <= 25; $i++): ?>
                            <div id="fixo-<?= $i ?>" class="numero" onclick="toggleFixo(<?= $i ?>)"><?= $i ?></div>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" id="numeros_fixos" name="numeros_fixos">
                </div>
                <div class="mb-3">
                    <label>Números Excluídos (azul):</label>
                    <div class="volante">
                        <?php for ($i = 1; $i <= 25; $i++): ?>
                            <div id="excluido-<?= $i ?>" class="numero" onclick="toggleExcluido(<?= $i ?>)"><?= $i ?></div>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" id="numeros_excluidos" name="numeros_excluidos">
                </div>
                <div class="mb-3">
                    <label>Estratégias:</label><br>
                    <?php $estrategias = ['frequencia', 'primos', 'repeticao', 'sequencias', 'atrasados', 'soma', 'desdobramento']; ?>
                    <?php foreach ($estrategias as $estrategia): ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="estrategias[]" value="<?= $estrategia ?>" id="<?= $estrategia ?>" <?= $estrategia === 'frequencia' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= $estrategia ?>"><?= ucfirst($estrategia) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-primary">Gerar Apostas</button>
            </form>
        </div>

        <!-- Tab: Temperatura dos Números -->
        <div id="temperatura" class="tab-content">
            <h2>Temperatura dos Números</h2>
            <?php
            $stmt = $pdo->query("SELECT numeros FROM resultados ORDER BY concurso DESC LIMIT 50");
            $numeros = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $todos_numeros = [];
            foreach ($numeros as $n) {
                $todos_numeros = array_merge($todos_numeros, json_decode($n));
            }
            $freq = array_count_values($todos_numeros);
            arsort($freq);
            $quentes = array_slice(array_keys($freq), 0, 12, true);
            $mornos = array_slice(array_keys($freq), 12, 6, true);
            $frios = array_slice(array_keys($freq), 18, 5, true);
            $congelados = array_diff(range(1, 25), array_keys($freq));
            ?>
            <div class="temp-section">
                <div class="temp-title">🔥 Quentes (12 mais frequentes)</div>
                <div class="temp-numbers">
                    <?php foreach ($quentes as $n): ?>
                        <span class="temp-number quente" onclick="alert('Frequência do número <?= $n ?>: <?= $freq[$n] ?> vezes')"><?= $n ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="temp-section">
                <div class="temp-title">🌞 Mornos (13º ao 18º)</div>
                <div class="temp-numbers">
                    <?php foreach ($mornos as $n): ?>
                        <span class="temp-number morno" onclick="alert('Frequência do número <?= $n ?>: <?= $freq[$n] ?> vezes')"><?= $n ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="temp-section">
                <div class="temp-title">❄️ Frios (19º ao 23º)</div>
                <div class="temp-numbers">
                    <?php foreach ($frios as $n): ?>
                        <span class="temp-number frio" onclick="alert('Frequência do número <?= $n ?>: <?= $freq[$n] ?> vezes')"><?= $n ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="temp-section">
                <div class="temp-title">🧊 Congelados (menos frequentes)</div>
                <div class="temp-numbers">
                    <?php foreach ($congelados as $n): ?>
                        <span class="temp-number congelado" onclick="alert('Número <?= $n ?> não apareceu nos últimos 50 concursos')"><?= $n ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tab: Adicionar Resultado -->
        <div id="adicionar" class="tab-content">
            <h2>Adicionar Resultado</h2>
            <form action="adicionar_resultado.php" method="POST">
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            document.getElementById(inputId).value = nums.join(',');
        }

        window.onload = () => {
            showTab('gerar');
        };
    </script>
</body>
</html>