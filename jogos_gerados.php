<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM jogos_gerados WHERE usuario_id = ? ORDER BY data_geracao DESC");
$stmt->execute([$_SESSION['user_id']]);
$jogos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Jogos Gerados - Lotofácil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Jogos Gerados</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Lote</th>
                    <th>Data</th>
                    <th>Concurso</th>
                    <th>Jogos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jogos as $jogo): ?>
                    <tr>
                        <td><?= $jogo['lote_id'] ?></td>
                        <td><?= $jogo['data_geracao'] ?></td>
                        <td><?= $jogo['concurso'] ?></td>
                        <td><?= implode(', ', json_decode($jogo['jogos'], true)[0]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-secondary">Voltar</a>
    </div>
</body>
</html>