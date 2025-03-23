<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar Jogos - Lotofácil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
    </script>
</body>
</html>