<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once realpath(__DIR__ . '/../includes/db.php');
require_once realpath(__DIR__ . '/../includes/auth.php');
require_once realpath(__DIR__ . '/../includes/functions.php');

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pdo = getDB();
list($ultimo_concurso, $ultimo_sorteio) = getUltimoConcurso($pdo);

include realpath(__DIR__ . '/../templates/header.php');
?>

<!-- Removido o <div class="container mt-4"> extra -->
<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="gerar-tab" data-bs-toggle="tab" data-bs-target="#gerar" type="button" role="tab" aria-controls="gerar" aria-selected="true">Gerar Jogos</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="temperatura-tab" data-bs-toggle="tab" data-bs-target="#temperatura" type="button" role="tab" aria-controls="temperatura" aria-selected="false">Temperatura dos Números</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="adicionar-tab" data-bs-toggle="tab" data-bs-target="#adicionar" type="button" role="tab" aria-controls="adicionar" aria-selected="false">Adicionar Resultado</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="jogos-gerados-tab" data-bs-toggle="tab" data-bs-target="#jogos_gerados" type="button" role="tab" aria-controls="jogos_gerados" aria-selected="false">Jogos Gerados</button>
    </li>
</ul>

<div class="tab-content" id="myTabContent">
    <!-- Restante do código das abas permanece igual -->
</div>

<?php include realpath(__DIR__ . '/../templates/footer.php'); ?>