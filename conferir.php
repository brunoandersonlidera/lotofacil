<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    http_response_code(403);
    echo 'Não autorizado';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método não permitido';
    exit;
}

$lote_id = $_POST['lote_id'] ?? null;
$concurso = $_POST['concurso'] ?? null;

if (!$lote_id || !$concurso) {
    http_response_code(400);
    echo 'Parâmetros inválidos';
    exit;
}

$pdo = getDB();

// Recuperar os jogos do lote
$stmt = $pdo->prepare("SELECT jogos FROM jogos_gerados WHERE lote_id = ? AND user_id = ?");
$stmt->execute([$lote_id, $_SESSION['user_id']]);
$jogo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jogo) {
    http_response_code(404);
    echo 'Jogo não encontrado';
    exit;
}

$jogos = json_decode($jogo['jogos'], true);

// Recuperar o resultado do concurso
$stmt = $pdo->prepare("SELECT numeros FROM resultados WHERE concurso = ?");
$stmt->execute([$concurso]);
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resultado) {
    http_response_code(404);
    echo 'Resultado do concurso não encontrado';
    exit;
}

$numeros_sorteados = json_decode($resultado['numeros'], true);

// Calcular acertos para cada jogo
$acertos = [];
foreach ($jogos as $index => $numeros) {
    $acertos_jogo = count(array_intersect($numeros, $numeros_sorteados));
    $acertos[] = sprintf("Jogo %02d: %d acertos", $index + 1, $acertos_jogo);
}

echo implode('<br>', $acertos);