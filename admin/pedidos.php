<?php
if (!function_exists('mb_substr')) {
    function mb_substr($s, $start, $length = null, $encoding = 'UTF-8') { return $length === null ? substr($s, $start) : substr($s, $start, $length); }
    function mb_strlen($s, $encoding = 'UTF-8') { return strlen($s); }
}
require_once __DIR__ . '/../db/db_con.php';
require_once __DIR__ . '/../helpers/email_helper.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /login?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$mensaje = '';

// Activar orden (modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activar_orden'])) {
    $orden_id = (int)$_POST['orden_id'];
    $numero_licencia = trim($_POST['numero_licencia'] ?? '');
    $instrucciones = trim($_POST['instrucciones'] ?? '');
    $pagado = isset($_POST['pagado']) ? 1 : 0;

    if ($numero_licencia) {
        $stmt = $pdo->prepare("SELECT o.*, l.nombre AS licencia_nombre, l.duracion FROM `ordenes` o LEFT JOIN `licencias` l ON o.licencia_id = l.id WHERE o.id = ?");
        $stmt->execute([$orden_id]);
        $orden = $stmt->fetch();

        if ($orden) {
            $token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("INSERT INTO `licencias_entregadas` (`orden_id`, `user_id`, `email`, `nombre`, `licencia_nombre`, `numero_licencia`, `instrucciones`, `pagado`, `token`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$orden_id, $orden['user_id'], $orden['email'], $orden['nombre'], $orden['licencia_nombre'], $numero_licencia, $instrucciones, $pagado, $token]);

            $stmt = $pdo->prepare("UPDATE `ordenes` SET `estado` = 'activo' WHERE `id` = ?");
            $stmt->execute([$orden_id]);

            $enviado = enviarCorreoLicenciaActiva($orden['email'], $orden['nombre'], $orden['numero_orden'], $orden['licencia_nombre'], $orden['duracion'], $token);

            if ($enviado) {
                $mensaje = '<div class="alert success"><i class="fas fa-check-circle"></i> Licencia activada y correo enviado al cliente.</div>';
            } else {
                $mensaje = '<div class="alert success"><i class="fas fa-check-circle"></i> Licencia activada. No se pudo enviar el correo.</div>';
            }
        }
    } else {
        $mensaje = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> El n\úmeroero de licencia es obligatorio.</div>';
    }
}

// Cancelar orden (modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_orden'])) {
    $id = (int)$_POST['id'];
    $motivo = trim($_POST['motivo_cancelacion'] ?? '');

    if ($motivo) {
        $stmt = $pdo->prepare("SELECT o.*, l.nombre AS licencia_nombre FROM `ordenes` o LEFT JOIN `licencias` l ON o.licencia_id = l.id WHERE o.id = ?");
        $stmt->execute([$id]);
        $orden = $stmt->fetch();

        if ($orden) {
            $stmt = $pdo->prepare("UPDATE `ordenes` SET `estado` = 'cancelado', `motivo_cancelacion` = ? WHERE `id` = ?");
            $stmt->execute([$motivo, $id]);

            $enviado = enviarCorreoCancelacion($orden['email'], $orden['nombre'], $orden['numero_orden'], $orden['licencia_nombre'], $motivo);

            if ($enviado) {
                $mensaje = '<div class="alert success"><i class="fas fa-check-circle"></i> Pedido cancelado y correo enviado al cliente.</div>';
            } else {
                $mensaje = '<div class="alert success"><i class="fas fa-check-circle"></i> Pedido cancelado. No se pudo enviar el correo.</div>';
            }
        }
    } else {
        $mensaje = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> El motivo de cancelación es obligatorio.</div>';
    }
}

// Cambiar estado (pendiente / atendiendo)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $id = (int)$_POST['id'];
    $estado = $_POST['estado'];
    if (in_array($estado, ['pendiente', 'atendiendo'])) {
        $stmt = $pdo->prepare("SELECT `nombre`, `email`, `numero_orden` FROM `ordenes` WHERE `id` = ?");
        $stmt->execute([$id]);
        $orden = $stmt->fetch();

        $stmt = $pdo->prepare("UPDATE `ordenes` SET `estado` = ? WHERE `id` = ?");
        $stmt->execute([$estado, $id]);
        $mensaje = '<div class="alert success"><i class="fas fa-check-circle"></i> Estado actualizado.</div>';

        if ($estado === 'atendiendo' && $orden) {
            $enviado = enviarCorreoAtendiendo($orden['email'], $orden['nombre'], $orden['numero_orden']);
            if ($enviado) {
                $mensaje = '<div class="alert success"><i class="fas fa-check-circle"></i> Estado actualizado. Correo de notificaci&oacute;n enviado al cliente.</div>';
            } else {
                $mensaje = '<div class="alert success"><i class="fas fa-check-circle"></i> Estado actualizado. No se pudo enviar el correo de notificaci&oacute;n.</div>';
            }
        }
    }
}

$buscar = trim($_GET['buscar'] ?? '');
$filtro_estado = $_GET['estado'] ?? '';

$sql = "SELECT o.*, l.nombre AS licencia_nombre, l.slug AS licencia_slug, l.duracion FROM `ordenes` o LEFT JOIN `licencias` l ON o.licencia_id = l.id WHERE 1=1";
$params = [];
if ($buscar) {
    $sql .= " AND (o.numero_orden LIKE ? OR o.nombre LIKE ? OR o.apellido LIKE ? OR o.telefono LIKE ? OR o.email LIKE ?)";
    $like = "%$buscar%";
    $params = array_merge($params, [$like, $like, $like, $like, $like]);
}
if ($filtro_estado) {
    $sql .= " AND o.estado = ?";
    $params[] = $filtro_estado;
}
$sql .= " ORDER BY o.created_at DESC";
$ordenes = $pdo->prepare($sql);
$ordenes->execute($params);
$ordenes = $ordenes->fetchAll();

$pendientes = $pdo->query("SELECT COUNT(*) FROM `ordenes` WHERE `estado` = 'pendiente'")->fetchColumn();
$atendiendo = $pdo->query("SELECT COUNT(*) FROM `ordenes` WHERE `estado` = 'atendiendo'")->fetchColumn();
$activas = $pdo->query("SELECT COUNT(*) FROM `ordenes` WHERE `estado` = 'activo'")->fetchColumn();
$canceladas = $pdo->query("SELECT COUNT(*) FROM `ordenes` WHERE `estado` = 'cancelado'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos — Key Market Nicaragua</title>
    <link rel="icon" type="image/svg+xml" href="/assets/favico.svg">
    <link rel="icon" type="image/x-icon" href="/assets/logo.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #1a1a1a;
            color: #fff;
            padding: 32px 20px;
            display: flex;
            flex-direction: column;
        }
        .sidebar h2 { font-size: 1.1rem; font-weight: 700; margin-bottom: 4px; }
        .sidebar h2 span { font-weight: 300; color: #999; }
        .sidebar .role-badge {
            font-size: 0.75rem; color: #999; margin-bottom: 32px;
            display: flex; align-items: center; gap: 6px;
        }
        .sidebar nav { display: flex; flex-direction: column; gap: 4px; flex: 1; }
        .sidebar nav a {
            color: #ccc; text-decoration: none; padding: 10px 14px;
            border-radius: 8px; font-size: 0.88rem;
            display: flex; align-items: center; gap: 10px; transition: all 0.2s;
        }
        .sidebar nav a:hover, .sidebar nav a.active { background: #333; color: #fff; }
        .sidebar .logout {
            margin-top: auto; color: #999; text-decoration: none;
            padding: 10px 14px; border-radius: 8px; font-size: 0.88rem;
            display: flex; align-items: center; gap: 10px; transition: all 0.2s;
        }
        .sidebar .logout:hover { background: #333; color: #fff; }
        .main { flex: 1; padding: 40px; }
        .main h1 { font-size: 1.8rem; font-weight: 700; color: #1a1a1a; margin-bottom: 24px; }

        .stats {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px; margin-bottom: 28px;
        }
        .stat-card {
            background: #fff; border-radius: 12px; padding: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,.04);
        }
        .stat-card .num { font-size: 1.5rem; font-weight: 700; }
        .stat-card .label { font-size: .82rem; color: #999; margin-top: 2px; }
        .stat-card.pendiente .num { color: #f59e0b; }
        .stat-card.atendiendo .num { color: #3b82f6; }
        .stat-card.activo .num { color: #16a34a; }

        .filtros {
            display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px;
        }
        .filtros input, .filtros select {
            padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px;
            font-size: .88rem; outline: none; font-family: inherit;
        }
        .filtros input { flex: 1; min-width: 200px; }
        .filtros input:focus, .filtros select:focus { border-color: #1a1a1a; }
        .filtros .btn {
            padding: 10px 20px; border: none; border-radius: 8px;
            font-size: .88rem; font-weight: 600; cursor: pointer;
            background: #1a1a1a; color: #fff; text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .filtros .btn:hover { background: #333; }
        .filtros .btn-outline {
            background: transparent; color: #666; border: 1px solid #ddd;
        }
        .filtros .btn-outline:hover { background: #f5f5f5; color: #1a1a1a; }

        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.04); }
        th, td { padding: 14px 16px; text-align: left; font-size: .85rem; }
        th { background: #fafafa; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; font-size: .75rem; }
        td { border-top: 1px solid #f0f0f0; color: #333; }
        tr:hover td { background: #fafafa; }

        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 10px; border-radius: 20px; font-size: .75rem;
            font-weight: 600;
        }
        .badge.pendiente { background: #fef3c7; color: #92400e; }
        .badge.atendiendo { background: #dbeafe; color: #1e40af; }
        .badge.activo { background: #dcfce7; color: #166534; }
        .badge.cancelado { background: #fef2f2; color: #b91c1c; }

        .estado-form { display: flex; gap: 6px; align-items: center; }
        .estado-form select {
            padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px;
            font-size: .8rem; outline: none; font-family: inherit;
        }
        .estado-form .btn-sm {
            padding: 6px 12px; border: none; border-radius: 6px;
            font-size: .75rem; font-weight: 600; cursor: pointer;
            background: #1a1a1a; color: #fff; transition: background .2s;
        }
        .estado-form .btn-sm:hover { background: #333; }

        .alert {
            padding: 12px 16px; border-radius: 8px; font-size: .85rem;
            margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
        }
        .alert.success { background: #f0fdf4; color: #166534; }
        .alert.error { background: #fef2f2; color: #b91c1c; }

        /* Modal */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.5);
            display: none; align-items: center; justify-content: center;
            z-index: 2000; padding: 20px;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: #fff; border-radius: 16px; padding: 40px;
            width: 100%; max-width: 500px; box-shadow: 0 20px 60px rgba(0,0,0,.2);
            position: relative; max-height: 90vh; overflow-y: auto;
        }
        .modal-close {
            position: absolute; top: 14px; right: 16px;
            background: none; border: none; font-size: 1.2rem; color: #999;
            cursor: pointer; padding: 4px;
        }
        .modal-close:hover { color: #1a1a1a; }
        .modal h2 { font-size: 1.2rem; color: #1a1a1a; margin-bottom: 20px; }
        .modal .form-group { margin-bottom: 16px; }
        .modal .form-group label {
            display: block; font-size: .82rem; font-weight: 600; color: #333; margin-bottom: 6px;
        }
        .modal .form-group input,
        .modal .form-group textarea {
            width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px;
            font-size: .88rem; outline: none; font-family: inherit; transition: border .2s;
        }
        .modal .form-group input:focus,
        .modal .form-group textarea:focus { border-color: #1a1a1a; }
        .modal .form-group textarea { min-height: 80px; resize: vertical; }
        .modal .form-group .checkbox-wrap {
            display: flex; align-items: center; gap: 8px; padding: 4px 0;
        }
        .modal .form-group .checkbox-wrap input[type="checkbox"] {
            width: auto;
        }
        .modal .btn-activar {
            width: 100%; padding: 12px; background: #16a34a; color: #fff;
            border: none; border-radius: 8px; font-size: .9rem; font-weight: 600;
            cursor: pointer; transition: background .2s; margin-top: 4px;
        }
        .modal .btn-activar:hover { background: #15803d; }

        .btn-cancel {
            padding: 6px 12px; border: 1px solid #dc2626; border-radius: 6px;
            font-size: .75rem; font-weight: 600; cursor: pointer;
            background: #fff; color: #dc2626;
            display: inline-flex; align-items: center; gap: 6px;
            transition: all .2s;
        }
        .btn-cancel:hover { background: #dc2626; color: #fff; }

        @media (max-width: 768px) {
            .main { padding: 20px; }
            .stats { grid-template-columns: 1fr 1fr; }
            table { font-size: .8rem; }
            th, td { padding: 10px 12px; }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>Key Market <span>Admin</span></h2>
        <div class="role-badge"><i class="fas fa-shield-alt"></i> Administrador</div>
        <nav>
            <a href="/admin/dashboard"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <a href="/admin/categorias"><i class="fas fa-tags"></i> Categor&iacute;as</a>
            <a href="/admin/licencias"><i class="fas fa-key"></i> Licencias</a>
            <a href="/admin/usuarios"><i class="fas fa-users"></i> Usuarios</a>
            <a href="/admin/pedidos" class="active"><i class="fas fa-shopping-cart"></i> Pedidos</a>
            <a href="/admin/dashboard"><i class="fas fa-cog"></i> Configuraci&oacute;n</a>
        </nav>
        <a href="/logout" class="logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesi&oacute;n</a>
    </aside>
    <div class="main">
        <h1><i class="fas fa-shopping-cart"></i> Pedidos</h1>
        <?= $mensaje ?>

        <div class="stats">
            <div class="stat-card pendiente">
                <div class="num"><?= $pendientes ?></div>
                <div class="label">Pendientes</div>
            </div>
            <div class="stat-card atendiendo">
                <div class="num"><?= $atendiendo ?></div>
                <div class="label">Atendiendo</div>
            </div>
            <div class="stat-card activo">
                <div class="num"><?= $activas ?></div>
                <div class="label">Activas</div>
            </div>
            <div class="stat-card" style="--num-color:#dc2626">
                <div class="num" style="color:#dc2626"><?= $canceladas ?></div>
                <div class="label">Canceladas</div>
            </div>
            <div class="stat-card">
                <div class="num"><?= count($ordenes) ?></div>
                <div class="label">Total mostrados</div>
            </div>
        </div>

        <form class="filtros" method="GET">
            <input type="text" name="buscar" placeholder="Buscar por nombre, correo, tel&eacute;fono u orden..." value="<?= htmlspecialchars($buscar) ?>">
            <select name="estado">
                <option value="">Todos los estados</option>
                <option value="pendiente" <?= $filtro_estado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                <option value="atendiendo" <?= $filtro_estado === 'atendiendo' ? 'selected' : '' ?>>Atendiendo</option>
                <option value="activo" <?= $filtro_estado === 'activo' ? 'selected' : '' ?>>Activo</option>
                <option value="cancelado" <?= $filtro_estado === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
            </select>
            <button type="submit" class="btn"><i class="fas fa-search"></i> Filtrar</button>
            <?php if ($buscar || $filtro_estado): ?>
                <a href="/admin/pedidos" class="btn btn-outline"><i class="fas fa-times"></i> Limpiar</a>
            <?php endif; ?>
        </form>

        <?php if (count($ordenes) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th># Orden</th>
                        <th>Cliente</th>
                        <th>Contacto</th>
                        <th>Direcci&oacute;n</th>
                        <th>Licencia</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acci\ón</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordenes as $o): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($o['numero_orden']) ?></strong></td>
                            <td><?= htmlspecialchars($o['nombre'] . ' ' . $o['apellido']) ?><br><small style="color:#999"><?= htmlspecialchars($o['pais']) ?></small></td>
                            <td>
                                <a href="mailto:<?= htmlspecialchars($o['email']) ?>" style="color:#333;text-decoration:none;display:block"><?= htmlspecialchars($o['email']) ?></a>
                                <a href="tel:<?= htmlspecialchars($o['telefono']) ?>" style="color:#999;font-size:.8rem;text-decoration:none"><?= htmlspecialchars($o['telefono']) ?></a>
                            </td>
                            <td style="max-width:160px;font-size:.8rem;color:#666">
                                <?= htmlspecialchars($o['direccion']) ?>
                                <?php if ($o['codigo_postal']): ?><br><small>CP: <?= htmlspecialchars($o['codigo_postal']) ?></small><?php endif; ?>
                            </td>
                            <td>
                                <a href="/licencia/<?= htmlspecialchars($o['licencia_slug']) ?>" target="_blank" style="color:#1a1a1a;text-decoration:none;font-weight:500">
                                    <?= htmlspecialchars($o['licencia_nombre']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge <?= $o['estado'] ?>">
                                    <?php if ($o['estado'] === 'pendiente'): ?><i class="fas fa-clock"></i>
                                    <?php elseif ($o['estado'] === 'atendiendo'): ?><i class="fas fa-headset"></i>
                                    <?php elseif ($o['estado'] === 'activo'): ?><i class="fas fa-check-circle"></i>
                                    <?php else: ?><i class="fas fa-times-circle"></i>
                                    <?php endif; ?>
                                    <?= $o['estado'] === 'cancelado' ? 'Cancelado' : ucfirst($o['estado']) ?>
                                </span>
                                <?php if ($o['estado'] === 'cancelado' && $o['motivo_cancelacion']): ?>
                                <br><small style="color:#999;font-size:.7rem" title="<?= htmlspecialchars($o['motivo_cancelacion']) ?>"><?= htmlspecialchars(mb_substr($o['motivo_cancelacion'], 0, 30)) ?><?= mb_strlen($o['motivo_cancelacion']) > 30 ? '...' : '' ?></small>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:.8rem;color:#999"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                            <td>
                                <?php if ($o['estado'] === 'pendiente' || $o['estado'] === 'atendiendo'): ?>
                                    <form method="post" class="estado-form" onsubmit="return cambiarEstado(event, this)">
                                        <input type="hidden" name="id" value="<?= $o['id'] ?>">
                                        <select name="estado" data-orden-id="<?= $o['id'] ?>">
                                            <option value="pendiente" <?= $o['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                            <option value="atendiendo" <?= $o['estado'] === 'atendiendo' ? 'selected' : '' ?>>Atendiendo</option>
                                            <option value="activo">Activar</option>
                                            <option value="cancelar">Cancelar</option>
                                        </select>
                                        <button type="submit" name="cambiar_estado" class="btn-sm"><i class="fas fa-sync"></i></button>
                                    </form>
                                <?php elseif ($o['estado'] === 'activo'): ?>
                                    <span style="color:#16a34a;font-size:.8rem;display:flex;align-items:center;gap:4px"><i class="fas fa-check-circle"></i> Completado</span>
                                <?php else: ?>
                                    <span style="color:#dc2626;font-size:.8rem;display:flex;align-items:center;gap:4px"><i class="fas fa-times-circle"></i> Cancelado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center;padding:60px 20px;color:#999;background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.04)">
                <i class="fas fa-shopping-cart" style="font-size:2rem;display:block;margin-bottom:12px"></i>
                No hay pedidos<?= $buscar ? ' que coincidan con la b&uacute;squeda' : ' todav&iacute;a' ?>.
            </p>
        <?php endif; ?>
    </div>

    <!-- Modal activar orden -->
    <div class="modal-overlay" id="modalActivar">
        <div class="modal">
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
            <h2><i class="fas fa-check-circle" style="color:#16a34a"></i> Activar orden</h2>
            <form method="post">
                <input type="hidden" name="orden_id" id="modalOrdenId">
                <div class="form-group">
                    <label for="numero_licencia">N&uacute;mero de licencia <span style="color:#dc2626">*</span></label>
                    <input type="text" name="numero_licencia" id="numero_licencia" required placeholder="XXXXX-XXXXX-XXXXX">
                </div>
                <div class="form-group">
                    <label for="instrucciones">Instrucciones</label>
                    <textarea name="instrucciones" id="instrucciones" placeholder="Instrucciones de instalaci&oacute;n o activaci&oacute;n..."></textarea>
                </div>
                <div class="form-group">
                    <div class="checkbox-wrap">
                        <input type="checkbox" name="pagado" id="pagado" checked>
                        <label for="pagado" style="margin:0;font-weight:500">Marcar como pagado</label>
                    </div>
                </div>
                <button type="submit" name="activar_orden" class="btn-activar"><i class="fas fa-check"></i> Activar y enviar correo</button>
            </form>
        </div>
    </div>

    <!-- Modal cancelar orden -->
    <div class="modal-overlay" id="modalCancelar">
        <div class="modal">
            <button class="modal-close" onclick="cerrarModalCancelar()"><i class="fas fa-times"></i></button>
            <h2><i class="fas fa-times-circle" style="color:#dc2626"></i> Cancelar orden</h2>
            <form method="post">
                <input type="hidden" name="id" id="cancelOrdenId">
                <div class="form-group">
                    <label for="motivo_cancelacion">Motivo de cancelaci&oacute;n <span style="color:#dc2626">*</span></label>
                    <textarea name="motivo_cancelacion" id="motivo_cancelacion" required placeholder="Describa el motivo por el cual se cancela el pedido..." style="min-height:100px"></textarea>
                </div>
                <button type="submit" name="cancelar_orden" class="btn-activar" style="background:#dc2626"><i class="fas fa-times"></i> Cancelar pedido y enviar correo</button>
            </form>
        </div>
    </div>

    <script>
        function cambiarEstado(e, form) {
            const select = form.querySelector('select');
            const id = select.getAttribute('data-orden-id');
            if (select.value === 'activo') {
                e.preventDefault();
                document.getElementById('modalOrdenId').value = id;
                document.getElementById('modalActivar').classList.add('open');
                return false;
            }
            if (select.value === 'cancelar') {
                e.preventDefault();
                abrirModalCancelar(id);
                return false;
            }
            return true;
        }

        function cerrarModal() {
            document.getElementById('modalActivar').classList.remove('open');
        }

        document.getElementById('modalActivar').addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });

        function abrirModalCancelar(id) {
            document.getElementById('cancelOrdenId').value = id;
            document.getElementById('motivo_cancelacion').value = '';
            document.getElementById('modalCancelar').classList.add('open');
        }

        function cerrarModalCancelar() {
            document.getElementById('modalCancelar').classList.remove('open');
        }

        document.getElementById('modalCancelar').addEventListener('click', function(e) {
            if (e.target === this) cerrarModalCancelar();
        });
    </script>
</body>
</html>
