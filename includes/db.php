<?php
require_once realpath(__DIR__ . '/config.php');

function getDB() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // echo "Conexão OK: " . DB_NAME; // Comentado para evitar saída indesejada em produção
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erro na conexão com o banco: " . $e->getMessage());
        die("Erro na conexão: " . $e->getMessage());
    }
}