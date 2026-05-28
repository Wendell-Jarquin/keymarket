<?php
require_once __DIR__ . '/../db/db_con.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /login?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Guardar tasa de cambio
$tasaMensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tasa_cambio'])) {
    $tasa = str_replace(',', '.', trim($_POST['tasa_cambio']));
    if (is_numeric($tasa) && (float)$tasa > 0) {
        $stmt = $pdo->prepare("INSERT INTO `configuraciones` (`clave`, `valor`) VALUES ('tasa_cambio', ?) ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`)");
        $stmt->execute([$tasa]);
        $tasaMensaje = '<div class="alert success"><i class="fas fa-check-circle"></i> Tasa de cambio actualizada a 1 USD = ' . number_format((float)$tasa, 2) . ' NIO.</div>';
    } else {
        $tasaMensaje = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> Ingresa un valor numérico válido.</div>';
    }
}

$tasaActual = obtenerTasaCambio();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Key Market Nicaragua</title>
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
        .sidebar h2 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .sidebar h2 span { font-weight: 300; color: #999; }
        .sidebar .role-badge {
            font-size: 0.75rem;
            color: #999;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .sidebar nav { display: flex; flex-direction: column; gap: 4px; flex: 1; }
        .sidebar nav a {
            color: #ccc;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }
        .sidebar nav a:hover, .sidebar nav a.active {
            background: #333;
            color: #fff;
        }
        .sidebar .logout {
            margin-top: auto;
            color: #999;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }
        .sidebar .logout:hover { background: #333; color: #fff; }
        .main {
            flex: 1;
            padding: 40px;
        }
        .main h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }
        .main p {
            color: #666;
            margin-bottom: 32px;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .card {
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .card .card-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 16px;
        }
        .card .card-number {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1a1a1a;
        }
        .card .card-label {
            font-size: 0.85rem;
            color: #999;
            margin-top: 4px;
        }
        .user-info {
            background: #fff;
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .user-info .avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #1a1a1a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .user-info .details strong { display: block; color: #1a1a1a; }
        .user-info .details span { font-size: 0.85rem; color: #999; }
        .settings-card {
            background: #fff; border-radius: 14px; padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04); margin-top: 28px;
        }
        .settings-card h3 {
            font-size: 1rem; font-weight: 600; color: #1a1a1a;
            margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
        }
        .settings-card .form-row {
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        }
        .settings-card .form-row input {
            padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px;
            font-size: .95rem; width: 160px; outline: none;
            font-family: inherit; transition: border .2s;
        }
        .settings-card .form-row input:focus { border-color: #1a1a1a; }
        .settings-card .form-row .btn {
            padding: 10px 22px; border: none; border-radius: 8px;
            font-size: .88rem; font-weight: 600; cursor: pointer;
            background: #1a1a1a; color: #fff; transition: all .2s;
        }
        .settings-card .form-row .btn:hover { background: #333; }
        .settings-card .form-row .tasa-label { font-size: .82rem; color: #999; }
        .alert {
            padding: 12px 16px; border-radius: 8px; font-size: .85rem;
            margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
        }
        .alert.success { background: #f0fdf4; color: #166534; }
        .alert.error { background: #fef2f2; color: #b91c1c; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>Key Market <span>Admin</span></h2>
        <div class="role-badge"><i class="fas fa-shield-alt"></i> Administrador</div>
        <nav>
            <a href="/admin/dashboard" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <a href="/admin/categorias"><i class="fas fa-tags"></i> Categorías</a>
            <a href="/admin/licencias"><i class="fas fa-key"></i> Licencias</a>
            <a href="/admin/usuarios"><i class="fas fa-users"></i> Usuarios</a>
            <a href="/admin/pedidos"><i class="fas fa-shopping-cart"></i> Pedidos</a>
            <a href="/admin/dashboard"><i class="fas fa-cog"></i> Configuración</a>
        </nav>
        <a href="/logout" class="logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </aside>
    <div class="main">
        <div class="user-info">
            <div class="avatar"><?= strtoupper(substr($_SESSION['user_nombre'], 0, 1)) ?></div>
            <div class="details">
                <strong><?= htmlspecialchars($_SESSION['user_nombre']) ?></strong>
                <span><?= htmlspecialchars($_SESSION['user_email']) ?></span>
            </div>
        </div>
        <h1>Dashboard</h1>
        <p>Bienvenido al panel de administración de Key Market Nicaragua.</p>
        <?php
        $totalProductos = $pdo->query("SELECT COUNT(*) FROM `licencias`")->fetchColumn();
        $totalUsuarios = $pdo->query("SELECT COUNT(*) FROM `usuarios`")->fetchColumn();
        $totalPedidos = $pdo->query("SELECT COUNT(*) FROM `ordenes`")->fetchColumn();
        $pendientes = $pdo->query("SELECT COUNT(*) FROM `ordenes` WHERE `estado` = 'pendiente'")->fetchColumn();
        $activos = $pdo->query("SELECT COUNT(*) FROM `ordenes` WHERE `estado` = 'activo'")->fetchColumn();
        ?>
        <div class="cards">
            <div class="card">
                <div class="card-icon"><i class="fas fa-box"></i></div>
                <div class="card-number"><?= $totalProductos ?></div>
                <div class="card-label">Productos</div>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fas fa-users"></i></div>
                <div class="card-number"><?= $totalUsuarios ?></div>
                <div class="card-label">Usuarios</div>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="card-number"><?= $totalPedidos ?></div>
                <div class="card-label">Pedidos totales</div>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fas fa-clock"></i></div>
                <div class="card-number"><?= $pendientes ?></div>
                <div class="card-label">Pendientes</div>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fas fa-check-circle"></i></div>
                <div class="card-number"><?= $activos ?></div>
                <div class="card-label">Activos</div>
            </div>
        </div>

        <div class="settings-card">
            <h3><i class="fas fa-dollar-sign"></i> Tasa de cambio</h3>
            <?= $tasaMensaje ?>
            <form method="POST">
                <div class="form-row">
                    <span class="tasa-label">1 USD =</span>
                    <input type="text" name="tasa_cambio" value="<?= number_format($tasaActual, 2, '.', '') ?>" required>
                    <span class="tasa-label">NIO</span>
                    <button type="submit" class="btn"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
