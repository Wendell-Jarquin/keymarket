<?php
require_once __DIR__ . '/../db/db_con.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /login?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$mensaje = '';

// Crear / Editar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $slug   = trim($_POST['slug'] ?? '');
    $icono  = trim($_POST['icono'] ?? 'fa-folder');
    $id     = (int) ($_POST['id'] ?? 0);

    if ($nombre === '' || $slug === '') {
        $mensaje = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> Nombre y slug son obligatorios.</div>';
    } else {
        try {
            if ($_POST['accion'] === 'editar' && $id > 0) {
                $stmt = $pdo->prepare("UPDATE `categorias` SET nombre = ?, slug = ?, icono = ? WHERE id = ?");
                $stmt->execute([$nombre, $slug, $icono, $id]);
                $mensaje = '<div class="alert success"><i class="fas fa-check-circle"></i> Categoría actualizada.</div>';
            } else {
                $stmt = $pdo->prepare("INSERT INTO `categorias` (nombre, slug, icono) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $slug, $icono]);
                $mensaje = '<div class="alert success"><i class="fas fa-check-circle"></i> Categoría creada.</div>';
            }
        } catch (PDOException $e) {
            $mensaje = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> ' . ($e->getCode() == 23000 ? 'El slug ya existe.' : 'Error al guardar.') . '</div>';
        }
    }
}

// Eliminar
if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM `categorias` WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: /admin/categorias");
    exit;
}

// Obtener categorías
$categorias = $pdo->query("SELECT * FROM `categorias` ORDER BY id ASC")->fetchAll();

// Datos para editar
$editar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM `categorias` WHERE id = ? LIMIT 1");
    $stmt->execute([(int)$_GET['editar']]);
    $editar = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías — Key Market Admin</title>
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
        .grid { display: grid; grid-template-columns: 360px 1fr; gap: 32px; align-items: start; }
        .card {
            background: #fff; border-radius: 14px; padding: 28px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .card h2 { font-size: 1.1rem; font-weight: 600; margin-bottom: 20px; color: #1a1a1a; }
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block; font-size: 0.82rem; font-weight: 600;
            color: #333; margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            width: 100%; padding: 10px 12px; border: 1px solid #ddd;
            border-radius: 8px; font-size: 0.9rem; outline: none;
            font-family: inherit; transition: border 0.2s; background: #fafafa;
        }
        .form-group input:focus { border-color: #1a1a1a; background: #fff; }
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 10px 22px; border: none; border-radius: 8px;
            font-size: 0.9rem; font-weight: 600; cursor: pointer;
            text-decoration: none; transition: all 0.2s;
        }
        .btn-primary { background: #1a1a1a; color: #fff; }
        .btn-primary:hover { background: #333; }
        .btn-secondary { background: #f0f0f0; color: #333; }
        .btn-secondary:hover { background: #ddd; }
        .btn-danger { background: #fef2f2; color: #b91c1c; }
        .btn-danger:hover { background: #fee2e2; }
        .alert {
            padding: 12px 16px; border-radius: 8px; font-size: 0.85rem;
            margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
        }
        .alert.success { background: #f0fdf4; color: #166534; }
        .alert.error { background: #fef2f2; color: #b91c1c; }
        table { width: 100%; border-collapse: collapse; }
        th {
            text-align: left; font-size: 0.78rem; font-weight: 600;
            color: #999; text-transform: uppercase; letter-spacing: 0.5px;
            padding: 10px 12px; border-bottom: 1px solid #eee;
        }
        td {
            padding: 14px 12px; font-size: 0.9rem; color: #333;
            border-bottom: 1px solid #f0f0f0; vertical-align: middle;
        }
        tr:last-child td { border-bottom: none; }
        .icon-preview { font-size: 1.1rem; width: 28px; text-align: center; color: #1a1a1a; }
        .actions { display: flex; gap: 6px; }
        .actions a {
            width: 32px; height: 32px; display: inline-flex; align-items: center;
            justify-content: center; border-radius: 6px; text-decoration: none;
            font-size: 0.85rem; transition: all 0.2s;
        }
        .actions .edit { color: #666; }
        .actions .edit:hover { background: #f0f0f0; color: #1a1a1a; }
        .actions .delete { color: #999; }
        .actions .delete:hover { background: #fef2f2; color: #b91c1c; }
        .empty { text-align: center; padding: 40px 20px; color: #999; font-size: 0.9rem; }
        .modal-overlay {
            display: none; position: fixed; inset: 0; z-index: 1000;
            background: rgba(0,0,0,0.5); align-items: center; justify-content: center;
            padding: 20px; backdrop-filter: blur(4px);
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: #fff; border-radius: 16px; padding: 32px;
            max-width: 400px; width: 100%; text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            animation: modalIn 0.2s ease;
        }
        @keyframes modalIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-icon {
            width: 56px; height: 56px; border-radius: 50%;
            background: #fef2f2; color: #b91c1c;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; margin: 0 auto 16px;
        }
        .modal h3 { font-size: 1.15rem; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; }
        .modal p { font-size: 0.9rem; color: #666; margin-bottom: 24px; line-height: 1.5; }
        .modal .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .modal .modal-actions .btn { min-width: 110px; justify-content: center; }
        .slug-hint { font-size: 0.75rem; color: #999; margin-top: 4px; }
        .icon-grid {
            display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px;
        }
        .icon-grid .opt {
            width: 36px; height: 36px; display: flex; align-items: center;
            justify-content: center; border: 1px solid #ddd; border-radius: 6px;
            cursor: pointer; font-size: 1rem; color: #555; transition: all 0.2s;
        }
        .icon-grid .opt:hover, .icon-grid .opt.selected { border-color: #1a1a1a; background: #1a1a1a; color: #fff; }

        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>Key Market <span>Admin</span></h2>
        <div class="role-badge"><i class="fas fa-shield-alt"></i> Administrador</div>
        <nav>
            <a href="/admin/dashboard"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <a href="/admin/categorias" class="active"><i class="fas fa-tags"></i> Categorías</a>
            <a href="/admin/licencias"><i class="fas fa-key"></i> Licencias</a>
            <a href="/admin/usuarios"><i class="fas fa-users"></i> Usuarios</a>
            <a href="/admin/pedidos"><i class="fas fa-shopping-cart"></i> Pedidos</a>
            <a href="/admin/dashboard"><i class="fas fa-cog"></i> Configuración</a>
        </nav>
        <a href="/logout" class="logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </aside>
    <div class="main">
        <h1><i class="fas fa-tags"></i> Categorías</h1>

        <?= $mensaje ?>

        <div class="grid">
            <div class="card">
                <h2><?= $editar ? 'Editar categoría' : 'Nueva categoría' ?></h2>
                <form method="POST">
                    <input type="hidden" name="accion" value="<?= $editar ? 'editar' : 'crear' ?>">
                    <?php if ($editar): ?>
                        <input type="hidden" name="id" value="<?= $editar['id'] ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" id="inputNombre" value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>" required placeholder="Ej: Office">
                    </div>

                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" name="slug" id="inputSlug" value="<?= htmlspecialchars($editar['slug'] ?? '') ?>" required placeholder="Ej: office">
                        <div class="slug-hint">Se usa en la URL. Sin espacios ni caracteres especiales.</div>
                    </div>

                    <div class="form-group">
                        <label>Icono</label>
                        <input type="text" name="icono" id="inputIcono" value="<?= htmlspecialchars($editar['icono'] ?? 'fa-folder') ?>" placeholder="fa-folder">
                        <div class="icon-grid" id="iconGrid">
                            <div class="opt" data-icon="fa-folder"><i class="fas fa-folder"></i></div>
                            <div class="opt" data-icon="fa-file-word"><i class="fas fa-file-word"></i></div>
                            <div class="opt" data-icon="fa-paint-brush"><i class="fas fa-paint-brush"></i></div>
                            <div class="opt" data-icon="fa-building"><i class="fas fa-building"></i></div>
                            <div class="opt" data-icon="fa-gamepad"><i class="fas fa-gamepad"></i></div>
                            <div class="opt" data-icon="fa-shield-halved"><i class="fas fa-shield-halved"></i></div>
                            <div class="opt" data-icon="fa-video"><i class="fas fa-video"></i></div>
                            <div class="opt" data-icon="fa-music"><i class="fas fa-music"></i></div>
                            <div class="opt" data-icon="fa-code"><i class="fas fa-code"></i></div>
                            <div class="opt" data-icon="fa-camera"><i class="fas fa-camera"></i></div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?= $editar ? 'Guardar cambios' : 'Crear categoría' ?>
                        </button>
                        <?php if ($editar): ?>
                            <a href="/admin/categorias" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2>Todas las categorías</h2>
                <?php if (count($categorias) === 0): ?>
                    <div class="empty"><i class="fas fa-tags"></i><br><br>No hay categorías aún.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 32px;"></th>
                                <th>Nombre</th>
                                <th>Slug</th>
                                <th style="width: 80px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $cat): ?>
                                <tr>
                                    <td><span class="icon-preview"><i class="fas <?= htmlspecialchars($cat['icono']) ?>"></i></span></td>
                                    <td><strong><?= htmlspecialchars($cat['nombre']) ?></strong></td>
                                    <td style="color: #999;"><?= htmlspecialchars($cat['slug']) ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="/admin/categorias?editar=<?= $cat['id'] ?>" class="edit" title="Editar"><i class="fas fa-pen"></i></a>
                                            <a href="#" class="delete" title="Eliminar" onclick="return openModal(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['nombre'], ENT_QUOTES) ?>')"><i class="fas fa-trash-can"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <div class="modal-icon"><i class="fas fa-trash-can"></i></div>
            <h3>¿Eliminar categoría?</h3>
            <p id="modalText">Esta acción no se puede deshacer.</p>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeModal()"><i class="fas fa-times"></i> Cancelar</button>
                <a href="#" id="modalConfirm" class="btn btn-danger"><i class="fas fa-trash-can"></i> Eliminar</a>
            </div>
        </div>
    </div>

    <script>
        function openModal(id, nombre) {
            document.getElementById('modalText').textContent = '¿Eliminar la categoría «' + nombre + '»? Esta acción no se puede deshacer.';
            document.getElementById('modalConfirm').href = '/admin/categorias?eliminar=' + id;
            document.getElementById('modalOverlay').classList.add('open');
            return false;
        }

        function closeModal() {
            document.getElementById('modalOverlay').classList.remove('open');
        }

        document.getElementById('modalOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });

        // Auto-slug from nombre
        document.getElementById('inputNombre')?.addEventListener('input', function() {
            const slug = document.getElementById('inputSlug');
            if (!slug.dataset.manual) slug.dataset.manual = '0';
            if (slug.dataset.manual === '0') {
                slug.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            }
        });
        document.getElementById('inputSlug')?.addEventListener('input', function() {
            this.dataset.manual = this.value ? '1' : '0';
        });

        // Icon picker
        document.querySelectorAll('#iconGrid .opt').forEach(el => {
            el.addEventListener('click', function() {
                document.querySelectorAll('#iconGrid .opt').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('inputIcono').value = this.dataset.icon;
            });
        });

        // Preselect current icon
        const currentIcon = document.getElementById('inputIcono')?.value;
        if (currentIcon) {
            document.querySelectorAll('#iconGrid .opt').forEach(o => {
                if (o.dataset.icon === currentIcon) o.classList.add('selected');
            });
        }
    </script>
</body>
</html>
