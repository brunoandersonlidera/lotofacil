<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    echo "Username: $username, Password: $password<br>"; // Depuração
    $pdo = getDB();
    echo "Conexão com banco OK<br>"; // Depuração
    
    if (login($username, $password, $pdo)) {
        echo "Login bem-sucedido, redirecionando..."; // Depuração
        header('Location: index.php');
        exit;
    } else {
        $error = "Usuário ou senha inválidos.";
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
        <label>Usuário: <input type="text" name="username" required></label><br>
        <label>Senha: <input type="password" name="password" required></label><br>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>