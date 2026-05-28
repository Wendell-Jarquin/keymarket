<?php
require_once __DIR__ . '/../db/db_con.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    header("Location: /");
    exit;
}

$stmt = $pdo->prepare("SELECT le.*, o.numero_orden, l.duracion FROM `licencias_entregadas` le LEFT JOIN `ordenes` o ON le.orden_id = o.id LEFT JOIN `licencias` l ON o.licencia_id = l.id WHERE le.token = ? LIMIT 1");
$stmt->execute([$token]);
$licencia = $stmt->fetch();

if (!$licencia) {
    http_response_code(404);
    echo "404 - Enlace no v&aacute;lido o expirado";
    exit;
}

$yaCanjeado = $licencia['canjeado'] ? true : false;

if (!$yaCanjeado && isset($_POST['abrir'])) {
    $stmt = $pdo->prepare("UPDATE `licencias_entregadas` SET `canjeado` = 1, `canjeado_at` = NOW() WHERE `id` = ?");
    $stmt->execute([$licencia['id']]);
    $yaCanjeado = true;
    $stmt = $pdo->prepare("SELECT le.*, o.numero_orden, l.duracion FROM `licencias_entregadas` le LEFT JOIN `ordenes` o ON le.orden_id = o.id LEFT JOIN `licencias` l ON o.licencia_id = l.id WHERE le.token = ? LIMIT 1");
    $stmt->execute([$token]);
    $licencia = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canjear licencia — Key Market Nicaragua</title>
    <link rel="icon" type="image/svg+xml" href="/assets/favico.svg">
    <link rel="icon" type="image/x-icon" href="/assets/logo.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
        }

        .card-wrap {
            perspective: 1000px;
            width: 100%;
            max-width: 640px;
        }

        .card {
            background: #fff;
            border-radius: 24px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,.1);
            overflow: hidden;
            transition: all .5s ease;
        }

        /* --- Estado sellado (antes de abrir) --- */
        .card-sealed {
            padding: 60px 40px 50px;
            text-align: center;
            animation: floatIn .8s ease-out;
        }
        @keyframes floatIn {
            0% { opacity: 0; transform: translateY(30px) scale(.95); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        .seal {
            width: 90px; height: 90px;
            background: linear-gradient(135deg, #1a1a1a, #333);
            color: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
            margin: 0 auto 28px;
            box-shadow: 0 8px 24px rgba(0,0,0,.15);
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .card-sealed h1 {
            font-size: 1.6rem; color: #1a1a1a; margin-bottom: 6px;
        }
        .card-sealed .producto {
            font-size: .95rem; color: #666; margin-bottom: 28px;
        }
        .card-sealed .detail-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 0; border-bottom: 1px solid #f0f0f0;
            font-size: .9rem;
        }
        .card-sealed .detail-row:last-child { border-bottom: none; }
        .card-sealed .detail-row .label { color: #999; }
        .card-sealed .detail-row .value {
            color: #1a1a1a; font-weight: 600; text-align: right;
        }

        .card-sealed .ribbon {
            margin: 24px 0 20px;
            padding: 10px;
            background: #fef3c7;
            color: #92400e;
            border-radius: 10px;
            font-size: .82rem;
            font-weight: 600;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }

        .btn-abrir {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 16px 48px;
            background: #1a1a1a; color: #fff;
            border: none; border-radius: 12px;
            font-size: 1.05rem; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: all .25s;
            margin-top: 8px;
        }
        .btn-abrir:hover {
            background: #333;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,.15);
        }
        .btn-abrir i {
            transition: transform .3s;
        }
        .btn-abrir:hover i {
            transform: rotate(90deg);
        }

        .btn-wa {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 24px;
            background: #25d366; color: #fff;
            border-radius: 10px;
            font-size: .85rem; font-weight: 500;
            text-decoration: none;
            transition: background .2s;
            width: 100%;
        }
        .btn-wa:hover {
            background: #1ebe5a;
        }

        /* --- Estado abierto (después de canjear) --- */
        .card-open {
            padding: 40px;
            animation: revealIn .6s ease-out;
        }
        @keyframes revealIn {
            0% { opacity: 0; transform: scale(.96); }
            100% { opacity: 1; transform: scale(1); }
        }

        .card-open .open-header {
            display: flex; align-items: center; gap: 16px;
            margin-bottom: 24px; text-align: left;
        }
        .card-open .open-icon {
            width: 48px; height: 48px;
            background: #f0fdf4; color: #16a34a;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0;
        }
        .card-open .open-header h1 {
            font-size: 1.2rem; color: #1a1a1a; margin-bottom: 2px;
        }
        .card-open .open-header .sub {
            font-size: .82rem; color: #999;
        }

        .open-grid {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 20px;
        }

        .open-box {
            background: #fafafa; border-radius: 14px; padding: 20px;
            text-align: left;
        }
        .open-box .row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; border-bottom: 1px solid #f0f0f0;
            font-size: .85rem;
        }
        .open-box .row:last-child { border-bottom: none; }
        .open-box .row .label { color: #999; font-size: .8rem; }
        .open-box .row .value { color: #1a1a1a; font-weight: 600; text-align: right; font-size: .85rem; }
        .open-box .row .value.code {
            font-family: 'Courier New', monospace; font-size: .85rem;
            background: #fff; padding: 4px 8px; border-radius: 6px;
            border: 1px solid #eee; word-break: break-all;
        }

        .open-instructions {
            background: #f9f9f9; border-radius: 14px; padding: 20px;
            text-align: left;
        }
        .open-instructions h3 {
            font-size: .85rem; color: #1a1a1a; margin-bottom: 8px;
            display: flex; align-items: center; gap: 6px;
        }
        .open-instructions p {
            font-size: .85rem; color: #555; line-height: 1.6;
            white-space: pre-wrap;
        }

        .open-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 20px;
            font-size: .75rem; font-weight: 600;
            background: #f0fdf4; color: #166534;
        }

        .open-footer {
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; flex-wrap: wrap;
        }
        .btn-done {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 28px; background: #16a34a; color: #fff;
            border: none; border-radius: 10px; font-size: .88rem;
            font-weight: 600; cursor: default; text-decoration: none;
        }

        @media (max-width: 600px) {
            .open-grid { grid-template-columns: 1fr; }
        }

        .back-link {
            display: block; margin-top: 20px; color: #999;
            text-decoration: none; font-size: .82rem; text-align: center;
        }
        .back-link:hover { color: #1a1a1a; }

        @media (max-width: 500px) {
            .card-sealed, .card-open { padding: 40px 24px; }
            .card-sealed h1 { font-size: 1.3rem; }
            .btn-abrir { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="card-wrap">
        <div class="card">
            <?php if (!$yaCanjeado): ?>
                <!-- Tarjeta sellada -->
                <div class="card-sealed">
                    <div class="seal"><i class="fas fa-gift"></i></div>
                    <h1><?= htmlspecialchars($licencia['licencia_nombre']) ?></h1>
                    <p class="producto">Tu licencia est&aacute; lista para ser canjeada</p>

                    <div class="detail-row">
                        <span class="label">Cliente</span>
                        <span class="value"><?= htmlspecialchars($licencia['nombre']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">V&aacute;lida por</span>
                        <span class="value"><?= htmlspecialchars($licencia['duracion'] ?: '—') ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Orden</span>
                        <span class="value">#<?= htmlspecialchars($licencia['numero_orden']) ?></span>
                    </div>

                    <div class="ribbon">
                        <i class="fas fa-clock"></i> Canjea en los pr&oacute;ximos 30 d&iacute;as
                    </div>

                    <form method="post">
                        <button type="submit" name="abrir" class="btn-abrir">
                            <i class="fas fa-envelope-open"></i> Abrir
                        </button>
                    </form>

                    <a href="https://wa.me/50586181155" target="_blank" class="btn-wa">
                        <i class="fab fa-whatsapp"></i> Si tienes problemas, cont&aacute;ctanos por WhatsApp
                    </a>
                </div>
            <?php else: ?>
                <!-- Tarjeta abierta -->
                <div class="card-open">
                    <div class="open-header">
                        <div class="open-icon"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <h1><?= htmlspecialchars($licencia['licencia_nombre']) ?></h1>
                            <div class="sub">Orden #<?= htmlspecialchars($licencia['numero_orden']) ?></div>
                        </div>
                    </div>

                    <div class="open-grid">
                        <div class="open-box">
                            <div class="row">
                                <span class="label">N&uacute;mero de licencia</span>
                                <span class="value code"><?= htmlspecialchars($licencia['numero_licencia']) ?></span>
                            </div>
                            <div class="row">
                                <span class="label">Cliente</span>
                                <span class="value"><?= htmlspecialchars($licencia['nombre']) ?></span>
                            </div>
                            <div class="row">
                                <span class="label">V&aacute;lida por</span>
                                <span class="value"><?= htmlspecialchars($licencia['duracion'] ?: '—') ?></span>
                            </div>
                            <div class="row">
                                <span class="label">Estado</span>
                                <span class="value" style="color:#16a34a">
                                    <span class="open-badge"><i class="fas fa-check-circle"></i> Canjeado</span>
                                </span>
                            </div>
                            <?php if ($licencia['canjeado_at']): ?>
                            <div class="row">
                                <span class="label">Canjeado el</span>
                                <span class="value"><?= date('d/m/Y H:i', strtotime($licencia['canjeado_at'])) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($licencia['instrucciones']): ?>
                        <div class="open-instructions">
                            <h3><i class="fas fa-info-circle"></i> Instrucciones</h3>
                            <p><?= nl2br(htmlspecialchars($licencia['instrucciones'])) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="open-footer">
                        <button class="btn-done" disabled><i class="fas fa-check-circle"></i> Canjeado</button>
                        <a href="/" class="back-link"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
                    </div>

                    <a href="https://wa.me/50586181155" target="_blank" class="btn-wa" style="margin-top:20px">
                        <i class="fab fa-whatsapp"></i> Si tienes problemas, cont&aacute;ctanos por WhatsApp
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
