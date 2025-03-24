<?php
session_start();
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function login($email, $password, $pdo) {
    $stmt = $pdo->prepare("SELECT id, senha FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['senha'])) {
        $_SESSION['user_id'] = $user['id'];
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}