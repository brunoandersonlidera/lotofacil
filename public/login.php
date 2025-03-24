<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once realpath(__DIR__ . '/../includes/db.php');
require_once realpath(__DIR__ . '/../includes/auth.php');

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $pdo = getDB();
    
    if (login($email, $password, $pdo)) {
        header('Location: index.php');
        exit;
    } else {
        $error = "E-mail ou senha inválidos.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label>E-mail: <input type="email" name="email" required></label><br>
        <label>Senha: <input type="password" name="password" required></label><br>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>