<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$concurso = $_GET['concurso'];
$lote_id = $_GET['lote_id'];

$pdo = getDB();
$stmt = $pdo->prepare("SELECT jogos FROM jogos_gerados WHERE lote_id = ? AND user_id = ?");
$stmt->execute([$lote_id, $_SESSION['user_id']]);
$jogos = json_decode($stmt->fetchColumn(), true);

$stmt = $pdo->prepare("SELECT numeros FROM resultados WHERE concurso = ?");
$stmt->execute([$concurso]);
$resultado = json_decode($stmt->fetchColumn(), true);

$acertos = [];
if ($resultado) {
    foreach ($jogos as $jogo) {
        $acertos[] = count(array_intersect($jogo, $resultado));
    }
} else {
    $acertos = array_fill(0, count($jogos), 'Resultado não disponível');
}

header('Content-Type: application/json');
echo json_encode($acertos);
exit;