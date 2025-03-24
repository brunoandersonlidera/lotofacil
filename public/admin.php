<?php
require_once realpath(__DIR__ . '/../includes/db.php');
require_once realpath(__DIR__ . '/../includes/auth.php');
requireAdmin();

$pdo = getDB();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chave = $_POST['chave'];
    $valor = $_POST['valor'];
    $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt->execute([$chave, $valor, $valor]);
    $success = "Configuração salva com sucesso!";
}

$configuracoes = $pdo->query("SELECT * FROM configuracoes")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel de Admin - Lotofácil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Painel de Admin</h2>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="chave" class="form-label">Chave</label>
                <input type="text" class="form-control" id="chave" name="chave" required>
            </div>
            <div class="mb-3">
                <label for="valor" class="form-label">Valor</label>
                <input type="text" class="form-control" id="valor" name="valor" required>
            </div>
            <button type="submit" class="btn btn-primary">Salvar Configuração</button>
        </form>
        <h3 class="mt-4">Configurações Atuais</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Chave</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($configuracoes as $config): ?>
                    <tr>
                        <td><?= $config['chave'] ?></td>
                        <td><?= $config['valor'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-secondary">Voltar</a>
    </div>
</body>
</html>