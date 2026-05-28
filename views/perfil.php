<?php
require_once __DIR__ . '/../db/db_con.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$mensaje = '';
$error = '';

// Cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $actual = $_POST['password_actual'] ?? '';
    $nueva = $_POST['password_nueva'] ?? '';
    $confirmar = $_POST['password_confirmar'] ?? '';

    $stmt = $pdo->prepare("SELECT `password` FROM `usuarios` WHERE `id` = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!password_verify($actual, $user['password'])) {
        $error = 'La contraseña actual no es correcta.';
    } elseif (strlen($nueva) < 6) {
        $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } elseif ($nueva !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE `usuarios` SET `password` = ? WHERE `id` = ?");
        $stmt->execute([$hash, $_SESSION['user_id']]);
        $mensaje = 'Contraseña actualizada correctamente.';
    }
}

// Subir foto de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $type = $_FILES['avatar']['type'];
        if (!in_array($type, $allowed)) {
            $error = 'Solo se permiten imágenes JPG, PNG, GIF o WebP.';
        } else {
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $filename = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            $destino = __DIR__ . '/../assets/avatars/' . $filename;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destino)) {
                // Eliminar avatar anterior si existe
                $stmt = $pdo->prepare("SELECT `avatar` FROM `usuarios` WHERE `id` = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $old = $stmt->fetchColumn();
                if ($old && file_exists(__DIR__ . '/../assets/avatars/' . $old)) {
                    unlink(__DIR__ . '/../assets/avatars/' . $old);
                }

                $stmt = $pdo->prepare("UPDATE `usuarios` SET `avatar` = ? WHERE `id` = ?");
                $stmt->execute([$filename, $_SESSION['user_id']]);
                $_SESSION['user_avatar'] = $filename;
                $mensaje = 'Foto de perfil actualizada.';
            } else {
                $error = 'Error al subir la imagen.';
            }
        }
    } else {
        $error = 'Selecciona una imagen para subir.';
    }
}

// Obtener datos actualizados del usuario
$stmt = $pdo->prepare("SELECT * FROM `usuarios` WHERE `id` = ?");
$stmt->execute([$_SESSION['user_id']]);
$usuario = $stmt->fetch();

// Sincronizar sesión
$_SESSION['user_nombre'] = $usuario['nombre'];
$_SESSION['user_email'] = $usuario['email'];
$_SESSION['rol'] = $usuario['rol'];
if ($usuario['avatar']) {
    $_SESSION['user_avatar'] = $usuario['avatar'];
}

$avatarSrc = $usuario['avatar'] ? '/assets/avatars/' . $usuario['avatar'] : null;

include __DIR__ . '/../components/header.php';
?>
<style>
    .perfil { max-width: 640px; margin: 60px auto; padding: 0 20px; }
    .perfil-card { background: #fff; border-radius: 16px; padding: 40px; box-shadow: 0 2px 20px rgba(0,0,0,.06); }

    .perfil-header { text-align: center; margin-bottom: 32px; }

    .perfil-avatar-wrap { position: relative; width: 100px; height: 100px; margin: 0 auto 16px; }
    .perfil-avatar-img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block; }
    .perfil-avatar-letter {
        width: 100%; height: 100%; border-radius: 50%; background: #1a1a1a; color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 2.5rem; font-weight: 700;
    }
    .perfil-avatar-label {
        position: absolute; bottom: 0; right: 0; width: 32px; height: 32px;
        border-radius: 50%; background: #1a1a1a; color: #fff; border: 3px solid #fff;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        font-size: .75rem; transition: background .2s;
    }
    .perfil-avatar-label:hover { background: #333; }

    .perfil-card h1 { font-size: 1.5rem; color: #1a1a1a; margin-bottom: 4px; }
    .perfil-card .rol {
        font-size: .85rem; color: #999; text-transform: uppercase;
        letter-spacing: .5px; margin-bottom: 4px;
    }
    .perfil-card .email { font-size: .9rem; color: #666; display: flex; align-items: center; justify-content: center; gap: 8px; }

    .perfil-section { border-top: 1px solid #eee; padding-top: 28px; margin-top: 28px; }
    .perfil-section h2 { font-size: 1.1rem; color: #1a1a1a; margin-bottom: 16px; }

    .alert { padding: 12px 16px; border-radius: 8px; font-size: .85rem; margin-bottom: 16px; }
    .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: .82rem; font-weight: 600; color: #333; margin-bottom: 6px; }
    .form-group input[type="password"],
    .form-group input[type="file"] {
        width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px;
        font-size: .9rem; transition: border .2s; outline: none;
    }
    .form-group input:focus { border-color: #1a1a1a; }

    .btn-submit {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 24px; border-radius: 8px; font-size: .85rem; font-weight: 600;
        border: none; cursor: pointer; transition: all .2s;
        background: #1a1a1a; color: #fff;
    }
    .btn-submit:hover { background: #333; transform: translateY(-1px); }

    .perfil-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; border-top: 1px solid #eee; padding-top: 24px; margin-top: 24px; }
    .perfil-actions .btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 24px; border-radius: 10px; font-size: .9rem;
        font-weight: 600; text-decoration: none; transition: all .2s; border: none; cursor: pointer;
    }
    .perfil-actions .btn-primary { background: #1a1a1a; color: #fff; }
    .perfil-actions .btn-primary:hover { background: #333; transform: translateY(-2px); }
    .perfil-actions .btn-danger { background: #fef2f2; color: #b91c1c; }
    .perfil-actions .btn-danger:hover { background: #fee2e2; transform: translateY(-2px); }
</style>

<main>
    <div class="perfil">
        <div class="perfil-card">
            <div class="perfil-header">
                <div class="perfil-avatar-wrap">
                    <?php if ($avatarSrc): ?>
                        <img src="<?= $avatarSrc ?>" alt="Avatar" class="perfil-avatar-img" id="avatarPreview">
                    <?php else: ?>
                        <div class="perfil-avatar-letter" id="avatarLetter"><?= strtoupper(substr($usuario['nombre'], 0, 1)) ?></div>
                        <img src="" alt="Avatar" class="perfil-avatar-img" id="avatarPreview" style="display:none">
                    <?php endif; ?>
                    <form method="post" enctype="multipart/form-data" id="avatarForm">
                        <label for="avatarInput" class="perfil-avatar-label"><i class="fas fa-camera"></i></label>
                        <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display:none">
                        <input type="hidden" name="subir_avatar" value="1">
                    </form>
                </div>
                <h1><?= htmlspecialchars($usuario['nombre']) ?></h1>
                <div class="rol"><?= $usuario['rol'] === 'admin' ? 'Administrador' : 'Usuario' ?></div>
                <div class="email"><i class="fas fa-envelope"></i> <?= htmlspecialchars($usuario['email']) ?></div>
            </div>

            <?php if ($mensaje): ?><div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

            <div class="perfil-section">
                <h2>Cambiar contraseña</h2>
                <form method="post">
                    <div class="form-group">
                        <label for="password_actual">Contraseña actual</label>
                        <input type="password" name="password_actual" id="password_actual" required>
                    </div>
                    <div class="form-group">
                        <label for="password_nueva">Nueva contraseña</label>
                        <input type="password" name="password_nueva" id="password_nueva" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="password_confirmar">Confirmar nueva contraseña</label>
                        <input type="password" name="password_confirmar" id="password_confirmar" required minlength="6">
                    </div>
                    <button type="submit" name="cambiar_password" class="btn-submit"><i class="fas fa-key"></i> Actualizar contraseña</button>
                </form>
            </div>

            <div class="perfil-actions">
                <?php if ($usuario['rol'] === 'admin'): ?>
                    <a href="/admin/dashboard" class="btn btn-primary"><i class="fas fa-shield-alt"></i> Panel de administración</a>
                <?php endif; ?>
                <a href="/logout" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
            </div>
        </div>
    </div>
</main>

<script>
document.getElementById('avatarInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
        const img = document.getElementById('avatarPreview');
        const letter = document.getElementById('avatarLetter');
        img.src = ev.target.result;
        img.style.display = 'block';
        if (letter) letter.style.display = 'none';
    };
    reader.readAsDataURL(file);
    document.getElementById('avatarForm').submit();
});
</script>

<?php include __DIR__ . '/../components/footer.php'; ?>
