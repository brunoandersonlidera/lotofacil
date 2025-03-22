<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Redireciona para login se o usuário não estiver logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Obtém as informações do usuário logado
$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lotofácil - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navbar para navegação -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Lotofácil</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="gerar_jogos.php">Gerar Jogos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="temperatura.php">Temperatura dos Números</a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="adicionar_resultado.php">Adicionar Resultado</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">Painel de Admin</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="jogos_gerados.php">Jogos Gerados</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo principal -->
    <div class="container mt-5">
        <h2>Bem-vindo, <?= $user['nome'] ?>!</h2>
        <p>Aqui você pode gerenciar suas apostas na Lotofácil.</p>

        <!-- Cards para navegação rápida -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Gerar Jogos</h5>
                        <p class="card-text">Crie novos jogos para o próximo concurso.</p>
                        <a href="gerar_jogos.php" class="btn btn-primary">Ir para Gerar Jogos</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Temperatura dos Números</h5>
                        <p class="card-text">Veja a análise de frequência dos números.</p>
                        <a href="temperatura.php" class="btn btn-primary">Ver Temperatura</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Jogos Gerados</h5>
                        <p class="card-text">Consulte os jogos que você já gerou.</p>
                        <a href="jogos_gerados.php" class="btn btn-primary">Ver Jogos Gerados</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>