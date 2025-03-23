<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $concurso = intval($_POST['concurso']);
    $numeros = array_map('intval', array_filter(explode(',', $_POST['numeros'])));
    if (count($numeros) !== 15 || max($numeros) > 25 || min($numeros) < 1) {
        $error = "Deve conter exatamente 15 números entre 1 e 25.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO resultados (concurso, numeros) VALUES (?, ?) ON DUPLICATE KEY UPDATE numeros = ?");
        $stmt->execute([$concurso, json_encode($numeros), json_encode($numeros)]);
        $success = "Resultado do concurso $concurso adicionado com sucesso!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Resultado - Lotofácil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Lotofácil</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="gerar_jogos.php">Gerar Jogos</a></li>
                    <li class="nav-item"><a class="nav-link" href="temperatura.php">Temperatura dos Números</a></li>
                    <li class="nav-item"><a class="nav-link active" href="adicionar_resultado.php">Adicionar Resultado</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin.php">Painel de Admin</a></li>
                    <li class="nav-item"><a class="nav-link" href="jogos_gerados.php">Jogos Gerados</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center">Adicionar Resultado</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php elseif (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label for="concurso" class="form-label">Concurso</label>
                <input type="number" class="form-control" id="concurso" name="concurso" required>
            </div>
            <div class="mb-3">
                <label for="numeros" class="form-label">Números (15, separados por vírgula)</label>
                <input type="text" class="form-control" id="numeros" name="numeros" required placeholder="Ex: 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15">
            </div>
            <button type="submit" class="btn btn-primary w-100">Adicionar Resultado</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>