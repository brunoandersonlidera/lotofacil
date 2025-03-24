<?php
session_start();
require_once realpath(__DIR__ . '/../../includes/auth.php');
require_once realpath(__DIR__ . '/../../includes/db.php');

if (!isLoggedIn()) {
    header('Location: /public/login.php');
    exit;
}

$pdo = getDB();
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT nome, perfil FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_name = $user['nome'] ?? 'Usuário';
$user_role = $user['perfil'] ?? 'user';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lotofácil - Sistema de Ganhos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/public/index.php">Lotofácil</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="/public/index.php">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/public/gerar_jogos.php">Gerar Jogos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/public/jogos_gerados.php">Jogos Gerados</a>
                    </li>
                    <?php if ($user_role === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/public/admin.php">Admin</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/public/download_resultados.php">Baixar Resultados</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Bem-vindo, <?= htmlspecialchars($user_name) ?> (<?= $user_role ?>)</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/public/logout.php">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">