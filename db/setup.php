<?php
$host = 'localhost';
$dbname = 'keymarket';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `usuarios` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nombre` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `rol` ENUM('admin','user') NOT NULL DEFAULT 'user',
        `avatar` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Agregar columna avatar si no existe (para DBs existentes)
    try {
        $pdo->exec("ALTER TABLE `usuarios` ADD COLUMN `avatar` VARCHAR(255) DEFAULT NULL AFTER `rol`");
    } catch (PDOException $e) {
        // Ya existe, ignorar
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS `categorias` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nombre` VARCHAR(100) NOT NULL,
        `slug` VARCHAR(100) NOT NULL UNIQUE,
        `icono` VARCHAR(50) DEFAULT 'fa-folder',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmt = $pdo->query("SELECT COUNT(*) FROM `usuarios` WHERE `email` = 'admin@keymarket.ni'");
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO `usuarios` (`nombre`, `email`, `password`, `rol`) VALUES (?, ?, ?, 'admin')");
        $insert->execute(['Administrador', 'admin@keymarket.ni', $hash]);
    }

    $stmt = $pdo->query("SELECT COUNT(*) FROM `categorias`");
    if ($stmt->fetchColumn() == 0) {
        $defaults = [
            ['Office', 'office', 'fa-file-word'],
            ['Diseño', 'diseno', 'fa-paint-brush'],
            ['Arquitectura', 'arquitectura', 'fa-building'],
            ['Juegos', 'juegos', 'fa-gamepad'],
            ['Antivirus', 'antivirus', 'fa-shield-halved'],
            ['Video', 'video', 'fa-video'],
        ];
        $insert = $pdo->prepare("INSERT INTO `categorias` (`nombre`, `slug`, `icono`) VALUES (?, ?, ?)");
        foreach ($defaults as $cat) {
            $insert->execute($cat);
        }
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS `licencias` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `categoria_id` INT NOT NULL,
        `nombre` VARCHAR(200) NOT NULL,
        `slug` VARCHAR(200) NOT NULL UNIQUE,
        `descripcion` TEXT,
        `caracteristicas` TEXT,
        `duracion` VARCHAR(100),
        `precio` DECIMAL(10,2) NOT NULL,
        `en_oferta` TINYINT(1) DEFAULT 0,
        `precio_oferta` DECIMAL(10,2) DEFAULT NULL,
        `tipo_licencia` VARCHAR(50) DEFAULT 'Estandar',
        `destacado` TINYINT(1) DEFAULT 0,
        `activo` TINYINT(1) DEFAULT 1,
        `imagen_1` VARCHAR(255),
        `imagen_2` VARCHAR(255),
        `imagen_3` VARCHAR(255),
        `vistas` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `ordenes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `numero_orden` VARCHAR(50) NOT NULL UNIQUE,
        `licencia_id` INT NOT NULL,
        `user_id` INT DEFAULT NULL,
        `nombre` VARCHAR(100) NOT NULL,
        `apellido` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL,
        `telefono` VARCHAR(50) NOT NULL,
        `pais` VARCHAR(100) NOT NULL,
        `codigo_postal` VARCHAR(20) DEFAULT NULL,
        `direccion` TEXT NOT NULL,
        `estado` ENUM('pendiente','atendiendo','activo','cancelado') NOT NULL DEFAULT 'pendiente',
        `motivo_cancelacion` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`licencia_id`) REFERENCES `licencias`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    try {
        $pdo->exec("ALTER TABLE `ordenes` ADD COLUMN `email` VARCHAR(100) NOT NULL AFTER `apellido`");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE `ordenes` ADD COLUMN `codigo_postal` VARCHAR(20) DEFAULT NULL AFTER `pais`");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE `ordenes` ADD COLUMN `direccion` TEXT NOT NULL AFTER `codigo_postal`");
    } catch (PDOException $e) {}

    $pdo->exec("CREATE TABLE IF NOT EXISTS `licencias_entregadas` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `orden_id` INT NOT NULL,
        `user_id` INT DEFAULT NULL,
        `email` VARCHAR(100) NOT NULL,
        `nombre` VARCHAR(100) NOT NULL,
        `licencia_nombre` VARCHAR(200) NOT NULL,
        `numero_licencia` VARCHAR(255) NOT NULL,
        `instrucciones` TEXT,
        `pagado` TINYINT(1) DEFAULT 1,
        `canjeado` TINYINT(1) DEFAULT 0,
        `canjeado_at` TIMESTAMP NULL DEFAULT NULL,
        `token` VARCHAR(64) NOT NULL UNIQUE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`orden_id`) REFERENCES `ordenes`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `configuraciones` (
        `clave` VARCHAR(50) PRIMARY KEY,
        `valor` VARCHAR(255) NOT NULL,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `configuraciones` WHERE `clave` = 'tasa_cambio'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO `configuraciones` (`clave`, `valor`) VALUES ('tasa_cambio', '36.50')");
    }

    echo "Base de datos y tablas creadas correctamente.";
    echo "<br>Usuario admin: admin@keymarket.ni / admin123";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
