<?php
$host = 'localhost';
$dbname = 'keymarket';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../helpers/currency_helper.php';
if (!isset($_SESSION['moneda'])) {
    $_SESSION['moneda'] = MONEDA_DEFAULT;
}

// Sincronizar avatar en sesión si ya está logueado
if (isset($_SESSION['user_id']) && !isset($_SESSION['user_avatar'])) {
    $stmt = $pdo->prepare("SELECT `avatar` FROM `usuarios` WHERE `id` = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $avatar = $stmt->fetchColumn();
    if ($avatar) $_SESSION['user_avatar'] = $avatar;
}
