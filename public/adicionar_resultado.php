<?php
session_start();
require_once realpath(__DIR__ . '/../includes/db.php');
require_once realpath(__DIR__ . '/../includes/auth.php');

if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST' || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: login.php');
    exit;
}

$concurso = $_POST['concurso'] ?? '';
$numeros = array_map('intval', explode(',', $_POST['numeros'] ?? ''));

if (count($numeros) !== 15 || !ctype_digit($concurso)) {
    echo "Erro: Dados inválidos.";
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("INSERT INTO resultados (concurso, numeros) VALUES (?, ?) ON DUPLICATE KEY UPDATE numeros = VALUES(numeros)");
$stmt->execute([$concurso, json_encode($numeros)]);
header('Location: index.php');
exit;