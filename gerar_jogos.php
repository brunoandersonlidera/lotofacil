<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

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
            <button type="submit" class="btn btn-primary">Gerar Apostas</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>