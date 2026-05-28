<?php
require_once __DIR__ . '/../db/db_con.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /login?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

require_once __DIR__ . '/../helpers/context_helper.php';

$mensaje = '';

// Obtener categorías para el select
$categorias = $pdo->query("SELECT * FROM `categorias` ORDER BY nombre ASC")->fetchAll();

// Helper subir imagen
function subirImagen($campo, $destinoBase)
{
    if (empty($_FILES[$campo]['name']) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION));
    $exts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'];
    if (!in_array($ext, $exts)) return null;
    if (!is_dir($destinoBase)) mkdir($destinoBase, 0777, true);
    $nombre = uniqid('img_') . '.' . $ext;
    $ruta = "$destinoBase/$nombre";
    return move_uploaded_file($_FILES[$campo]['tmp_name'], $ruta) ? "uploads/licencias/$nombre" : null;
}

// Crear / Editar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $categoria_id = (int) ($_POST['categoria_id'] ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $caracteristicas = trim($_POST['caracteristicas'] ?? '');
    $duracion = trim($_POST['duracion'] ?? '');
    $precio = (float) ($_POST['precio'] ?? 0);
    $en_oferta = isset($_POST['en_oferta']) ? 1 : 0;
    $precio_oferta = $en_oferta ? (float) ($_POST['precio_oferta'] ?? 0) : null;
    $tipo_licencia = trim($_POST['tipo_licencia'] ?? 'Estandar');
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $activo = isset($_POST['activo']) ? 1 : 0;

    $destino = __DIR__ . '/../uploads/licencias';

    if ($_POST['accion'] === 'editar' && $id > 0) {
        $img1 = subirImagen('imagen_1', $destino);
        $img2 = subirImagen('imagen_2', $destino);
        $img3 = subirImagen('imagen_3', $destino);

        $sql = "UPDATE `licencias` SET categoria_id=?, nombre=?, slug=?, descripcion=?, caracteristicas=?, duracion=?, precio=?, en_oferta=?, precio_oferta=?, tipo_licencia=?, destacado=?, activo=?";
        $params = [$categoria_id, $nombre, $slug, $descripcion, $caracteristicas, $duracion, $precio, $en_oferta, $precio_oferta, $tipo_licencia, $destacado, $activo];

        if ($img1) { $sql .= ", imagen_1=?"; $params[] = $img1; }
        if ($img2) { $sql .= ", imagen_2=?"; $params[] = $img2; }
        if ($img3) { $sql .= ", imagen_3=?"; $params[] = $img3; }

        $sql .= " WHERE id=?";
        $params[] = $id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        saveContext(['last_action' => 'licencia_editada', 'last_edit_id' => $id, 'last_edit_nombre' => $nombre]);
        $mensaje = '<div class="alert success"><i class="fas fa-check-circle"></i> Licencia actualizada.</div>';
    } else {
        $img1 = subirImagen('imagen_1', $destino) ?: '';
        $img2 = subirImagen('imagen_2', $destino) ?: '';
        $img3 = subirImagen('imagen_3', $destino) ?: '';

        try {
            $stmt = $pdo->prepare("INSERT INTO `licencias` (categoria_id, nombre, slug, descripcion, caracteristicas, duracion, precio, en_oferta, precio_oferta, tipo_licencia, destacado, activo, imagen_1, imagen_2, imagen_3) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$categoria_id, $nombre, $slug, $descripcion, $caracteristicas, $duracion, $precio, $en_oferta, $precio_oferta, $tipo_licencia, $destacado, $activo, $img1, $img2, $img3]);
            saveContext(['last_action' => 'licencia_creada', 'last_edit_nombre' => $nombre]);
            $mensaje = '<div class="alert success"><i class="fas fa-check-circle"></i> Licencia creada.</div>';
        } catch (PDOException $e) {
            $mensaje = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> ' . ($e->getCode() == 23000 ? 'El slug ya existe.' : 'Error al guardar.') . '</div>';
        }
    }
}

// Eliminar
if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    $stmt = $pdo->prepare("SELECT imagen_1, imagen_2, imagen_3 FROM `licencias` WHERE id=?");
    $stmt->execute([$id]);
    $imgs = $stmt->fetch();
    if ($imgs) {
        foreach (['imagen_1', 'imagen_2', 'imagen_3'] as $c) {
            if (!empty($imgs[$c]) && file_exists(__DIR__ . '/../' . $imgs[$c])) {
                unlink(__DIR__ . '/../' . $imgs[$c]);
            }
        }
    }
    $pdo->prepare("DELETE FROM `licencias` WHERE id=?")->execute([$id]);
    header("Location: /admin/licencias");
    exit;
}

// Obtener todas
$licencias = $pdo->query("SELECT l.*, c.nombre AS cat_nombre FROM `licencias` l LEFT JOIN `categorias` c ON l.categoria_id = c.id ORDER BY l.id DESC")->fetchAll();

// Editar
$editar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM `licencias` WHERE id=? LIMIT 1");
    $stmt->execute([(int)$_GET['editar']]);
    $editar = $stmt->fetch();
    if ($editar) {
        saveContext(['last_action' => 'editando_licencia', 'last_edit_id' => $editar['id'], 'last_edit_nombre' => $editar['nombre']]);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Licencias — Key Market Admin</title>
    <link rel="icon" type="image/svg+xml" href="/assets/favico.svg">
    <link rel="icon" type="image/x-icon" href="/assets/logo.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
            background:#f5f5f5; display:flex; min-height:100vh;
        }
        .sidebar {
            width:250px; background:#1a1a1a; color:#fff;
            padding:32px 20px; display:flex; flex-direction:column;
        }
        .sidebar h2 { font-size:1.1rem; font-weight:700; margin-bottom:4px; }
        .sidebar h2 span { font-weight:300; color:#999; }
        .sidebar .role-badge {
            font-size:.75rem; color:#999; margin-bottom:32px;
            display:flex; align-items:center; gap:6px;
        }
        .sidebar nav { display:flex; flex-direction:column; gap:4px; flex:1; }
        .sidebar nav a {
            color:#ccc; text-decoration:none; padding:10px 14px;
            border-radius:8px; font-size:.88rem;
            display:flex; align-items:center; gap:10px; transition:all .2s;
        }
        .sidebar nav a:hover, .sidebar nav a.active { background:#333; color:#fff; }
        .sidebar .logout {
            margin-top:auto; color:#999; text-decoration:none;
            padding:10px 14px; border-radius:8px; font-size:.88rem;
            display:flex; align-items:center; gap:10px; transition:all .2s;
        }
        .sidebar .logout:hover { background:#333; color:#fff; }
        .main { flex:1; padding:40px; }
        .main h1 { font-size:1.8rem; font-weight:700; color:#1a1a1a; margin-bottom:24px; }
        .grid { display:grid; grid-template-columns:1fr; gap:32px; }
        .card {
            background:#fff; border-radius:14px; padding:28px;
            box-shadow:0 2px 12px rgba(0,0,0,.04);
        }
        .card h2 { font-size:1.1rem; font-weight:600; margin-bottom:20px; color:#1a1a1a; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .form-grid .full { grid-column:1/-1; }
        .form-group { margin-bottom:16px; }
        .form-group label {
            display:block; font-size:.82rem; font-weight:600;
            color:#333; margin-bottom:5px;
        }
        .form-group input, .form-group textarea, .form-group select {
            width:100%; padding:10px 12px; border:1px solid #ddd;
            border-radius:8px; font-size:.9rem; outline:none;
            font-family:inherit; transition:border .2s; background:#fafafa;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            border-color:#1a1a1a; background:#fff;
        }
        .form-group textarea { resize:vertical; min-height:80px; }
        .form-group .slug-hint { font-size:.75rem; color:#999; margin-top:4px; }
        .form-group .checkbox-wrap {
            display:flex; align-items:center; gap:8px; padding-top:8px;
        }
        .form-group .checkbox-wrap input[type="checkbox"] {
            width:18px; height:18px; accent-color:#1a1a1a;
        }
        .form-row-imgs {
            display:grid; grid-template-columns:repeat(3,1fr); gap:16px;
        }
        .form-row-imgs .img-upload {
            border:2px dashed #ddd; border-radius:10px; padding:20px;
            text-align:center; cursor:pointer; transition:all .2s;
            position:relative; min-height:140px;
            display:flex; flex-direction:column; align-items:center; justify-content:center;
        }
        .form-row-imgs .img-upload:hover { border-color:#999; }
        .form-row-imgs .img-upload i { font-size:1.8rem; color:#ccc; margin-bottom:8px; }
        .form-row-imgs .img-upload span { font-size:.8rem; color:#aaa; }
        .form-row-imgs .img-upload input { position:absolute; inset:0; opacity:0; cursor:pointer; }
        .form-row-imgs .img-upload img {
            max-width:100%; max-height:120px; border-radius:6px; display:none;
        }
        .form-row-imgs .img-upload.has-img img { display:block; }
        .form-row-imgs .img-upload.has-img i,
        .form-row-imgs .img-upload.has-img span { display:none; }
        .form-row-imgs .img-upload .remove-img {
            position:absolute; top:6px; right:6px; width:26px; height:26px;
            border-radius:50%; background:#b91c1c; color:#fff; border:none;
            cursor:pointer; font-size:.75rem; display:none; align-items:center; justify-content:center;
        }
        .form-row-imgs .img-upload.has-img .remove-img { display:flex; }
        .btn {
            display:inline-flex; align-items:center; gap:6px;
            padding:10px 22px; border:none; border-radius:8px;
            font-size:.9rem; font-weight:600; cursor:pointer;
            text-decoration:none; transition:all .2s;
        }
        .btn-primary { background:#1a1a1a; color:#fff; }
        .btn-primary:hover { background:#333; }
        .btn-secondary { background:#f0f0f0; color:#333; }
        .btn-secondary:hover { background:#ddd; }
        .btn-danger { background:#fef2f2; color:#b91c1c; }
        .btn-danger:hover { background:#fee2e2; }
        .alert {
            padding:12px 16px; border-radius:8px; font-size:.85rem;
            margin-bottom:16px; display:flex; align-items:center; gap:8px;
        }
        .alert.success { background:#f0fdf4; color:#166534; }
        .alert.error { background:#fef2f2; color:#b91c1c; }
        table { width:100%; border-collapse:collapse; }
        th {
            text-align:left; font-size:.78rem; font-weight:600;
            color:#999; text-transform:uppercase; letter-spacing:.5px;
            padding:10px 12px; border-bottom:1px solid #eee;
        }
        td {
            padding:14px 12px; font-size:.88rem; color:#333;
            border-bottom:1px solid #f0f0f0; vertical-align:middle;
        }
        tr:last-child td { border-bottom:none; }
        .td-img { width:40px; }
        .td-img img { width:36px; height:36px; border-radius:6px; object-fit:cover; }
        .badge {
            display:inline-block; padding:2px 10px; border-radius:4px;
            font-size:.72rem; font-weight:600;
        }
        .badge-green { background:#f0fdf4; color:#166534; }
        .badge-red { background:#fef2f2; color:#b91c1c; }
        .badge-blue { background:#eff6ff; color:#1e40af; }
        .badge-yellow { background:#fefce8; color:#a16207; }
        .actions { display:flex; gap:6px; }
        .actions a {
            width:32px; height:32px; display:inline-flex; align-items:center;
            justify-content:center; border-radius:6px; text-decoration:none;
            font-size:.85rem; transition:all .2s;
        }
        .actions .edit { color:#666; }
        .actions .edit:hover { background:#f0f0f0; color:#1a1a1a; }
        .actions .delete { color:#999; }
        .actions .delete:hover { background:#fef2f2; color:#b91c1c; }
        .empty { text-align:center; padding:40px 20px; color:#999; font-size:.9rem; }
        .modal-overlay {
            display:none; position:fixed; inset:0; z-index:1000;
            background:rgba(0,0,0,.5); align-items:center; justify-content:center;
            padding:20px; backdrop-filter:blur(4px);
        }
        .modal-overlay.open { display:flex; }
        .modal {
            background:#fff; border-radius:16px; padding:32px;
            max-width:400px; width:100%; text-align:center;
            box-shadow:0 20px 60px rgba(0,0,0,.2); animation:modalIn .2s ease;
        }
        @keyframes modalIn { from{transform:scale(.95);opacity:0} to{transform:scale(1);opacity:1} }
        .modal-icon {
            width:56px; height:56px; border-radius:50%;
            background:#fef2f2; color:#b91c1c;
            display:flex; align-items:center; justify-content:center;
            font-size:1.4rem; margin:0 auto 16px;
        }
        .modal h3 { font-size:1.15rem; font-weight:700; color:#1a1a1a; margin-bottom:8px; }
        .modal p { font-size:.9rem; color:#666; margin-bottom:24px; line-height:1.5; }
        .modal .modal-actions { display:flex; gap:10px; justify-content:center; }
        .modal .modal-actions .btn { min-width:110px; justify-content:center; }
        @media (max-width:900px) { .form-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>Key Market <span>Admin</span></h2>
        <div class="role-badge"><i class="fas fa-shield-alt"></i> Administrador</div>
        <nav>
            <a href="/admin/dashboard"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <a href="/admin/categorias"><i class="fas fa-tags"></i> Categorías</a>
            <a href="/admin/licencias" class="active"><i class="fas fa-key"></i> Licencias</a>
            <a href="/admin/usuarios"><i class="fas fa-users"></i> Usuarios</a>
            <a href="/admin/pedidos"><i class="fas fa-shopping-cart"></i> Pedidos</a>
            <a href="/admin/dashboard"><i class="fas fa-cog"></i> Configuración</a>
        </nav>
        <a href="/logout" class="logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </aside>
    <div class="main">
        <h1><i class="fas fa-key"></i> Licencias</h1>

        <?= $mensaje ?>

        <div class="grid">
            <div class="card">
                <h2><?= $editar ? 'Editar licencia' : 'Nueva licencia' ?></h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="<?= $editar ? 'editar' : 'crear' ?>">
                    <?php if ($editar): ?>
                        <input type="hidden" name="id" value="<?= $editar['id'] ?>">
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" id="inputNombre" value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Slug</label>
                            <input type="text" name="slug" id="inputSlug" value="<?= htmlspecialchars($editar['slug'] ?? '') ?>" required>
                            <div class="slug-hint">Se usa en la URL. Sin espacios.</div>
                        </div>
                        <div class="form-group">
                            <label>Categoría</label>
                            <select name="categoria_id" required>
                                <option value="">— Seleccionar —</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($editar && $editar['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Duración</label>
                            <input type="text" name="duracion" value="<?= htmlspecialchars($editar['duracion'] ?? '') ?>" placeholder="Ej: 1 año, Perpetua, 6 meses">
                        </div>
                        <div class="form-group">
                            <label>Tipo de licencia</label>
                            <select name="tipo_licencia">
                                <?php foreach (['Estandar', 'Profesional', 'Empresarial', 'Personal', 'Académica'] as $t): ?>
                                    <option value="<?= $t ?>" <?= ($editar && $editar['tipo_licencia'] === $t) ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Precio (US$)</label>
                            <input type="number" step="0.01" min="0" name="precio" value="<?= htmlspecialchars($editar['precio'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <div class="checkbox-wrap">
                                <input type="checkbox" name="en_oferta" id="chkOferta" <?= ($editar && $editar['en_oferta']) ? 'checked' : '' ?>>
                                <label for="chkOferta" style="margin:0">En oferta</label>
                            </div>
                        </div>
                        <div class="form-group" id="grupoOferta" style="<?= ($editar && $editar['en_oferta']) ? '' : 'display:none' ?>">
                            <label>Precio de oferta (US$)</label>
                            <input type="number" step="0.01" min="0" name="precio_oferta" value="<?= htmlspecialchars($editar['precio_oferta'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <div class="checkbox-wrap">
                                <input type="checkbox" name="destacado" id="chkDestacado" <?= ($editar && $editar['destacado']) ? 'checked' : '' ?>>
                                <label for="chkDestacado" style="margin:0">Destacado</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="checkbox-wrap">
                                <input type="checkbox" name="activo" id="chkActivo" <?= ($editar && !$editar['activo']) ? '' : 'checked' ?>>
                                <label for="chkActivo" style="margin:0">Activo</label>
                            </div>
                        </div>
                        <div class="form-group full">
                            <label>Descripción</label>
                            <textarea name="descripcion" style="min-height:100px"><?= htmlspecialchars($editar['descripcion'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group full">
                            <label>Características (una por línea)</label>
                            <textarea name="caracteristicas" style="min-height:100px" placeholder="Ej:&#10;Activación vitalicia&#10;Soporte técnico incluido&#10;Actualizaciones gratuitas"><?= htmlspecialchars($editar['caracteristicas'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group full">
                            <label>Imágenes</label>
                            <div class="form-row-imgs">
                                <?php for ($i = 1; $i <= 3; $i++):
                                    $campo = "imagen_$i";
                                    $val = $editar[$campo] ?? '';
                                ?>
                                <div class="img-upload <?= $val ? 'has-img' : '' ?>" id="wrap_<?= $campo ?>">
                                    <i class="fas fa-image"></i>
                                    <span>Imagen <?= $i ?></span>
                                    <input type="file" name="<?= $campo ?>" accept="image/*" onchange="previewImg(this, 'preview_<?= $campo ?>')">
                                    <img id="preview_<?= $campo ?>" src="<?= $val ? '/' . htmlspecialchars($val) : '' ?>" alt="">
                                    <?php if ($val): ?>
                                        <button type="button" class="remove-img" onclick="removeImg('<?= $campo ?>')"><i class="fas fa-times"></i></button>
                                    <?php endif; ?>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; gap:8px; margin-top:8px;">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $editar ? 'Guardar cambios' : 'Crear licencia' ?></button>
                        <?php if ($editar): ?>
                            <a href="/admin/licencias" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2>Todas las licencias (<?= count($licencias) ?>)</h2>
                <?php if (count($licencias) === 0): ?>
                    <div class="empty"><i class="fas fa-key"></i><br><br>No hay licencias aún.</div>
                <?php else: ?>
                    <div style="overflow-x:auto">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width:40px;"></th>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Estado</th>
                                    <th>Vistas</th>
                                    <th style="width:70px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($licencias as $l): ?>
                                    <tr>
                                        <td class="td-img">
                                            <?php if ($l['imagen_1']): ?>
                                                <img src="/<?= htmlspecialchars($l['imagen_1']) ?>" alt="">
                                            <?php else: ?>
                                                <div style="width:36px;height:36px;border-radius:6px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:.8rem"><i class="fas fa-key"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($l['nombre']) ?></strong></td>
                                        <td style="color:#999;font-size:.82rem"><?= htmlspecialchars($l['cat_nombre'] ?? '—') ?></td>
                                        <td>
                                            <?php if ($l['en_oferta'] && $l['precio_oferta']): ?>
                                                <span style="text-decoration:line-through;color:#999">US$<?= number_format($l['precio'], 2) ?></span>
                                                <strong style="color:#b91c1c">US$<?= number_format($l['precio_oferta'], 2) ?></strong>
                                            <?php else: ?>
                                                <strong>US$<?= number_format($l['precio'], 2) ?></strong>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($l['destacado']): ?><span class="badge badge-yellow"><i class="fas fa-star"></i> Destacado</span><?php endif; ?>
                                            <span class="badge <?= $l['activo'] ? 'badge-green' : 'badge-red' ?>"><?= $l['activo'] ? 'Activo' : 'Inactivo' ?></span>
                                        </td>
                                        <td style="color:#999"><?= number_format($l['vistas']) ?></td>
                                        <td>
                                            <div class="actions">
                                                <a href="/admin/licencias?editar=<?= $l['id'] ?>" class="edit" title="Editar"><i class="fas fa-pen"></i></a>
                                                <a href="#" class="delete" title="Eliminar" onclick="return openModal(<?= $l['id'] ?>, '<?= htmlspecialchars($l['nombre'], ENT_QUOTES) ?>')"><i class="fas fa-trash-can"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <div class="modal-icon"><i class="fas fa-trash-can"></i></div>
            <h3>¿Eliminar licencia?</h3>
            <p id="modalText">Esta acción no se puede deshacer.</p>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeModal()"><i class="fas fa-times"></i> Cancelar</button>
                <a href="#" id="modalConfirm" class="btn btn-danger"><i class="fas fa-trash-can"></i> Eliminar</a>
            </div>
        </div>
    </div>

    <script>
        // Modal
        function openModal(id, nombre) {
            document.getElementById('modalText').textContent = '¿Eliminar la licencia «' + nombre + '»? Esta acción no se puede deshacer.';
            document.getElementById('modalConfirm').href = '/admin/licencias?eliminar=' + id;
            document.getElementById('modalOverlay').classList.add('open');
            return false;
        }
        function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); }
        document.getElementById('modalOverlay').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });

        // Auto-slug
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

        // Toggle oferta
        document.getElementById('chkOferta')?.addEventListener('change', function() {
            document.getElementById('grupoOferta').style.display = this.checked ? 'block' : 'none';
        });

        // Preview imagenes
        function previewImg(input, previewId) {
            const wrap = input.closest('.img-upload');
            const img = document.getElementById(previewId);
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) { img.src = e.target.result; };
                reader.readAsDataURL(file);
                wrap.classList.add('has-img');
            }
        }

        function removeImg(campo) {
            const wrap = document.getElementById('wrap_' + campo);
            const input = wrap.querySelector('input[type="file"]');
            const img = wrap.querySelector('img');
            input.value = '';
            img.src = '';
            wrap.classList.remove('has-img');
        }
    </script>
</body>
</html>
