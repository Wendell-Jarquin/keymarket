<?php

define('TASA_CAMBIO_DEFAULT', 36.50);
define('MONEDA_DEFAULT', 'NIO');

function obtenerTasaCambio(): float {
    global $pdo;
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT `valor` FROM `configuraciones` WHERE `clave` = 'tasa_cambio' LIMIT 1");
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row && is_numeric($row['valor'])) {
                return (float) $row['valor'];
            }
        } catch (Exception $e) {
            // Si la tabla no existe, usar default
        }
    }
    return TASA_CAMBIO_DEFAULT;
}

function getMoneda(): string {
    return $_SESSION['moneda'] ?? MONEDA_DEFAULT;
}

function setMoneda(string $moneda): void {
    if (in_array($moneda, ['NIO', 'USD'])) {
        $_SESSION['moneda'] = $moneda;
    }
}

function convertirMoneda(float $montoNio): float {
    if (getMoneda() === 'USD') {
        return round($montoNio / obtenerTasaCambio(), 2);
    }
    return round($montoNio, 2);
}

function simboloMoneda(): string {
    return getMoneda() === 'USD' ? 'US$' : 'C$';
}

function codigoMoneda(): string {
    return getMoneda();
}

function formatoPrecio(float $montoNio): string {
    $monto = convertirMoneda($montoNio);
    $simbolo = simboloMoneda();
    return $simbolo . ' ' . number_format($monto, 2, '.', ',');
}
