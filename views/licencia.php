<?php
require_once __DIR__ . '/../db/db_con.php';
require_once __DIR__ . '/../helpers/email_helper.php';

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    header("Location: /catalogo");
    exit;
}

$stmt = $pdo->prepare("SELECT l.*, c.nombre AS cat_nombre, c.slug AS cat_slug, c.icono AS cat_icono FROM `licencias` l LEFT JOIN `categorias` c ON l.categoria_id = c.id WHERE l.slug = ? AND l.activo = 1 LIMIT 1");
$stmt->execute([$slug]);
$licencia = $stmt->fetch();

if (!$licencia) {
    http_response_code(404);
    echo "404 - Licencia no encontrada";
    exit;
}

// Incrementar vistas
$pdo->prepare("UPDATE `licencias` SET vistas = vistas + 1 WHERE id = ?")->execute([$licencia['id']]);

$caracteristicas = array_filter(array_map('trim', explode("\n", $licencia['caracteristicas'] ?? '')));

// Procesar orden
$ordenCreada = false;
$ordenNumero = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_orden'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $pais = trim($_POST['pais'] ?? '');
    $codigo_postal = trim($_POST['codigo_postal'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if ($nombre && $apellido && $email && $telefono && $pais && $direccion) {
        $fecha = date('Ymd');
        $stmt = $pdo->query("SELECT COUNT(*) FROM `ordenes`");
        $count = $stmt->fetchColumn() + 1;
        $numero_orden = "ORD-$fecha-" . str_pad($count, 4, '0', STR_PAD_LEFT);

        $user_id = $_SESSION['user_id'] ?? null;
        $stmt = $pdo->prepare("INSERT INTO `ordenes` (`numero_orden`, `licencia_id`, `user_id`, `nombre`, `apellido`, `email`, `telefono`, `pais`, `codigo_postal`, `direccion`, `estado`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')");
        $stmt->execute([$numero_orden, $licencia['id'], $user_id, $nombre, $apellido, $email, $telefono, $pais, $codigo_postal ?: null, $direccion]);
        enviarCorreoConfirmacion($email, $nombre, $numero_orden, $licencia['nombre']);
        $ordenCreada = true;
        $ordenNumero = $numero_orden;
    }
}

include __DIR__ . '/../components/header.php';
?>
<style>
    .detail {
        max-width: 1100px; margin: 0 auto; padding: 40px 20px 80px;
    }
    .breadcrumb {
        font-size: .82rem; color: #999; margin-bottom: 28px;
        display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
    }
    .breadcrumb a { color: #999; text-decoration: none; }
    .breadcrumb a:hover { color: #1a1a1a; }
    .breadcrumb span { color: #1a1a1a; }

    .detail-grid {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 48px; align-items: start;
    }

    /* Galería — optimizada para imágenes 1920×1080 (16:9) */
    .gallery-main {
        width: 100%; aspect-ratio: 16 / 9; border-radius: 14px; overflow: hidden;
        background: #1a1a1a; display: flex; align-items: center; justify-content: center;
        border: 1px solid #eee; margin-bottom: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,.06);
    }
    .gallery-main img {
        width: 100%; height: 100%; object-fit: cover;
        transition: opacity .3s ease;
    }
    .gallery-main img.fade {
        opacity: 0;
    }
    .gallery-main .no-img { font-size: 4rem; color: #444; }
    .gallery-thumbs {
        display: flex; gap: 10px;
    }
    .gallery-thumbs .thumb {
        width: 100px; aspect-ratio: 16 / 9; border-radius: 8px; overflow: hidden;
        border: 2px solid transparent; cursor: pointer; opacity: .6;
        transition: all .2s; background: #1a1a1a;
        display: flex; align-items: center; justify-content: center;
    }
    .gallery-thumbs .thumb:hover, .gallery-thumbs .thumb.active { opacity: 1; border-color: #1a1a1a; }
    .gallery-thumbs .thumb img { width: 100%; height: 100%; object-fit: cover; }
    .gallery-thumbs .thumb .no-img-thumb { font-size: 1.2rem; color: #444; }

    .gallery-badges {
        display: grid; grid-template-columns: 1fr 1fr; gap: 8px;
        margin-top: 16px;
    }
    .gallery-badge {
        display: flex; align-items: center; gap: 8px;
        font-size: .82rem; color: #555; font-weight: 500;
        padding: 10px 14px; border-radius: 8px;
        background: #fafafa; border: 1px solid #f0f0f0;
        transition: all .2s;
    }
    .gallery-badge i { width: 16px; color: #1a1a1a; font-size: .85rem; }
    .gallery-badge:hover { background: #f0f0f0; border-color: #ddd; }

    /* Info */
    .detail-info .badges-top { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
    .detail-info .badges-top span {
        padding: 4px 12px; border-radius: 4px; font-size: .75rem;
        font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
    }
    .detail-info .cat-link {
        font-size: .85rem; color: #999; text-decoration: none;
        display: inline-flex; align-items: center; gap: 4px; margin-bottom: 8px;
    }
    .detail-info .cat-link:hover { color: #1a1a1a; }
    .detail-info h1 { font-size: 2rem; font-weight: 700; color: #1a1a1a; margin-bottom: 6px; }
    .detail-info .meta {
        font-size: .85rem; color: #aaa; margin-bottom: 20px;
        display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    }
    .detail-info .meta i { margin-right: 4px; }

    .detail-info .precio-box {
        background: #fafafa; border-radius: 12px; padding: 20px 24px;
        margin-bottom: 24px; display: flex; align-items: center;
        justify-content: space-between; flex-wrap: wrap; gap: 16px;
    }
    .detail-info .precio-box .precios {
        display: flex; align-items: baseline; gap: 12px;
    }
    .detail-info .precio-box .precio-actual {
        font-size: 2rem; font-weight: 700; color: #1a1a1a;
    }
    .detail-info .precio-box .precio-oferta {
        font-size: 2rem; font-weight: 700; color: #dc2626;
    }
    .detail-info .precio-box .precio-old {
        font-size: 1.1rem; color: #999; text-decoration: line-through;
    }
    .detail-info .precio-box .ahorro {
        font-size: .82rem; color: #dc2626; font-weight: 600;
    }
    .btn-group { display: flex; gap: 10px; flex-wrap: wrap; }
    .btn-ordenar {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 10px 22px; background: #1a1a1a; color: #fff;
        border: none; border-radius: 8px; font-size: .88rem;
        font-weight: 600; cursor: pointer; text-decoration: none;
        transition: all .2s;
    }
    .btn-ordenar:hover { background: #333; transform: translateY(-1px); }
    .btn-consultar {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 10px 22px; background: #25d366; color: #fff;
        border: none; border-radius: 8px; font-size: .88rem;
        font-weight: 600; cursor: pointer; text-decoration: none;
        transition: all .2s;
    }
    .btn-consultar:hover { background: #1da851; transform: translateY(-1px); }

    .detail-info .descripcion {
        color: #555; line-height: 1.7; margin-bottom: 24px;
        font-size: .95rem;
    }

    .detail-info .caracteristicas h3 {
        font-size: 1rem; font-weight: 600; color: #1a1a1a;
        margin-bottom: 12px;
    }
    .detail-info .caracteristicas ul {
        list-style: none; display: grid;
        grid-template-columns: 1fr 1fr; gap: 8px;
    }
    .detail-info .caracteristicas ul li {
        font-size: .88rem; color: #555;
        display: flex; align-items: center; gap: 8px;
    }
    .detail-info .caracteristicas ul li i { color: #16a34a; font-size: .8rem; }



    /* Modal */
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,.5);
        display: none; align-items: center; justify-content: center;
        z-index: 2000; padding: 20px;
    }
    .modal-overlay.open { display: flex; }
    .modal {
        background: #fff; border-radius: 16px; padding: 40px;
        width: 100%; max-width: 460px; box-shadow: 0 20px 60px rgba(0,0,0,.2);
        position: relative; max-height: 90vh; overflow-y: auto;
    }
    .modal-close {
        position: absolute; top: 14px; right: 16px;
        background: none; border: none; font-size: 1.2rem; color: #999;
        cursor: pointer; transition: color .2s; padding: 4px;
    }
    .modal-close:hover { color: #1a1a1a; }
    .modal h2 { font-size: 1.2rem; color: #1a1a1a; margin-bottom: 4px; }
    .modal .modal-sub { font-size: .85rem; color: #999; margin-bottom: 24px; }
    .modal .form-group { margin-bottom: 16px; }
    .modal .form-group label {
        display: block; font-size: .82rem; font-weight: 600; color: #333; margin-bottom: 6px;
    }
    .modal .form-group input,
    .modal .form-group select {
        width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px;
        font-size: .88rem; outline: none; transition: border .2s; font-family: inherit;
    }
    .modal .form-group input:focus,
    .modal .form-group select:focus { border-color: #1a1a1a; }
    .modal .btn-enviar {
        width: 100%; padding: 12px; background: #1a1a1a; color: #fff;
        border: none; border-radius: 8px; font-size: .9rem; font-weight: 600;
        cursor: pointer; transition: background .2s; margin-top: 4px;
    }
    .modal .btn-enviar:hover { background: #333; }

    .modal-success { text-align: center; padding: 20px 0; }
    .modal-success .icon {
        width: 64px; height: 64px; border-radius: 50%;
        background: #f0fdf4; color: #16a34a;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; margin: 0 auto 16px;
    }
    .modal-success h2 { font-size: 1.3rem; margin-bottom: 12px; }
    .modal-success p { font-size: .9rem; color: #555; line-height: 1.6; margin-bottom: 8px; }
    .modal-success .num-orden {
        font-size: 1rem; font-weight: 700; color: #1a1a1a;
        background: #f5f5f5; padding: 8px 16px; border-radius: 8px;
        display: inline-block; margin: 12px 0;
    }
    .modal-success .btn-cerrar {
        margin-top: 20px; padding: 10px 28px; background: #1a1a1a; color: #fff;
        border: none; border-radius: 8px; font-size: .88rem; font-weight: 600;
        cursor: pointer; transition: background .2s;
    }
    .modal-success .btn-cerrar:hover { background: #333; }

    @media (max-width: 768px) {
        .detail-grid { grid-template-columns: 1fr; gap: 24px; }
        .gallery-main { aspect-ratio: 16 / 9; }
        .gallery-thumbs .thumb { width: 70px; }
        .detail-info h1 { font-size: 1.5rem; }
        .detail-info .precio-box .precio-actual,
        .detail-info .precio-box .precio-oferta { font-size: 1.5rem; }
        .detail-info .caracteristicas ul { grid-template-columns: 1fr; }
        .gallery-badges { grid-template-columns: 1fr; }
    }
</style>

<main>
    <div class="detail">
        <div class="breadcrumb">
            <a href="/">Inicio</a>
            <i class="fas fa-chevron-right" style="font-size:.6rem"></i>
            <a href="/catalogo">Catálogo</a>
            <i class="fas fa-chevron-right" style="font-size:.6rem"></i>
            <a href="/categoria/<?= htmlspecialchars($licencia['cat_slug']) ?>"><?= htmlspecialchars($licencia['cat_nombre']) ?></a>
            <i class="fas fa-chevron-right" style="font-size:.6rem"></i>
            <span><?= htmlspecialchars($licencia['nombre']) ?></span>
        </div>

        <div class="detail-grid">
            <!-- Galería -->
            <div>
                <div class="gallery-main" id="galleryMain">
                    <?php if ($licencia['imagen_1']): ?>
                        <img id="mainImg" src="/<?= htmlspecialchars($licencia['imagen_1']) ?>" alt="<?= htmlspecialchars($licencia['nombre']) ?>">
                    <?php else: ?>
                        <div class="no-img"><i class="fas fa-key"></i></div>
                    <?php endif; ?>
                </div>
                <div class="gallery-thumbs" id="galleryThumbs">
                    <?php for ($i = 1; $i <= 3; $i++):
                        $campo = "imagen_$i";
                        if ($licencia[$campo]): ?>
                            <div class="thumb <?= $i === 1 ? 'active' : '' ?>" onclick="cambiarImagen(this, '/<?= htmlspecialchars($licencia[$campo]) ?>')">
                                <img src="/<?= htmlspecialchars($licencia[$campo]) ?>" alt="">
                            </div>
                        <?php endif;
                    endfor; ?>
                </div>
                <div class="gallery-badges">
                    <div class="gallery-badge"><i class="fas fa-shield-alt"></i> Producto 100% original</div>
                    <div class="gallery-badge"><i class="fas fa-bolt"></i> Entrega inmediata</div>
                    <div class="gallery-badge"><i class="fas fa-headset"></i> Soporte humano incluido</div>
                    <div class="gallery-badge"><i class="fas fa-undo-alt"></i> Garantía de satisfacción</div>
                </div>
            </div>

            <!-- Info -->
            <div class="detail-info">
                <div class="badges-top">
                    <?php if ($licencia['en_oferta']): ?><span style="background:#dc2626;color:#fff">Oferta</span><?php endif; ?>
                    <?php if ($licencia['destacado']): ?><span style="background:#f59e0b;color:#fff">Destacado</span><?php endif; ?>
                    <?php if ($licencia['activo']): ?><span style="background:#f0fdf4;color:#166534">Disponible</span><?php endif; ?>
                </div>
                <a href="/categoria/<?= htmlspecialchars($licencia['cat_slug']) ?>" class="cat-link"><i class="fas <?= htmlspecialchars($licencia['cat_icono']) ?>"></i> <?= htmlspecialchars($licencia['cat_nombre']) ?></a>
                <h1><?= htmlspecialchars($licencia['nombre']) ?></h1>
                <div class="meta">
                    <span><i class="far fa-clock"></i> <?= htmlspecialchars($licencia['duracion'] ?: '—') ?></span>
                    <span><i class="fas fa-tag"></i> Licencia <?= htmlspecialchars($licencia['tipo_licencia']) ?></span>
                    <span><i class="far fa-eye"></i> <?= number_format($licencia['vistas']) ?> vistas</span>
                </div>

                <div class="precio-box">
                    <div class="precios">
                        <?php if ($licencia['en_oferta'] && $licencia['precio_oferta']): ?>
                            <span class="precio-oferta"><?= formatoPrecio($licencia['precio_oferta']) ?></span>
                            <span class="precio-old"><?= formatoPrecio($licencia['precio']) ?></span>
                            <div class="ahorro">
                                <i class="fas fa-tag"></i> Ahorras <?= formatoPrecio($licencia['precio'] - $licencia['precio_oferta']) ?>
                            </div>
                        <?php else: ?>
                            <span class="precio-actual"><?= formatoPrecio($licencia['precio']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="btn-group">
                        <a href="#" class="btn-ordenar" onclick="abrirModal();return false"><i class="fas fa-shopping-cart"></i> Ordenar</a>
                        <a href="https://wa.me/50586181155?text=Hola,%20me%20interesa%20la%20licencia:%20<?= urlencode($licencia['nombre']) ?>" target="_blank" class="btn-consultar">
                            <i class="fab fa-whatsapp"></i> Consultar
                        </a>
                    </div>
                </div>

                <?php if ($licencia['descripcion']): ?>
                    <div class="descripcion"><?= nl2br(htmlspecialchars($licencia['descripcion'])) ?></div>
                <?php endif; ?>

                <?php if (count($caracteristicas) > 0): ?>
                    <div class="caracteristicas">
                        <h3><i class="fas fa-check-circle"></i> Características</h3>
                        <ul>
                            <?php foreach ($caracteristicas as $c): ?>
                                <li><i class="fas fa-check"></i> <?= htmlspecialchars($c) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Modal orden -->
    <div class="modal-overlay" id="modalOrden">
        <div class="modal">
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>

            <?php if ($ordenCreada): ?>
                <div class="modal-success">
                    <div class="icon"><i class="fas fa-check"></i></div>
                    <h2>¡Orden recibida!</h2>
                    <p>Uno de nuestros ejecutivos se pondrá en contacto con usted para agilizar el pago y la instalación de su licencia.</p>
                    <p>En breve le llegará un correo de confirmación con su número de orden.</p>
                    <div class="num-orden"><?= htmlspecialchars($ordenNumero) ?></div>
                    <div><button class="btn-cerrar" onclick="cerrarModal()">Cerrar</button></div>
                </div>
            <?php else: ?>
                <h2>Solicitar licencia</h2>
                <p class="modal-sub">Complete sus datos para generar una orden de compra.</p>
                <form method="post" onsubmit="return validarForm()">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="apellido">Apellido</label>
                        <input type="text" name="apellido" id="apellido" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Número telefónico</label>
                        <input type="tel" name="telefono" id="telefono" required>
                    </div>
                    <div class="form-group">
                        <label for="pais">País</label>
                        <select name="pais" id="pais" required>
                            <option value="">Seleccione un país</option>
                            <option value="Nicaragua">Nicaragua</option>
                            <option value="Costa Rica">Costa Rica</option>
                            <option value="Honduras">Honduras</option>
                            <option value="El Salvador">El Salvador</option>
                            <option value="Guatemala">Guatemala</option>
                            <option value="Panamá">Panamá</option>
                            <option value="México">México</option>
                            <option value="Colombia">Colombia</option>
                            <option value="Perú">Perú</option>
                            <option value="Argentina">Argentina</option>
                            <option value="Chile">Chile</option>
                            <option value="Ecuador">Ecuador</option>
                            <option value="Bolivia">Bolivia</option>
                            <option value="Paraguay">Paraguay</option>
                            <option value="Uruguay">Uruguay</option>
                            <option value="Venezuela">Venezuela</option>
                            <option value="Estados Unidos">Estados Unidos</option>
                            <option value="España">España</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="codigo_postal">Código postal</label>
                        <input type="text" name="codigo_postal" id="codigo_postal">
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección domiciliar</label>
                        <input type="text" name="direccion" id="direccion" required>
                    </div>
                    <button type="submit" name="crear_orden" class="btn-enviar"><i class="fas fa-paper-plane"></i> Enviar solicitud</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    function cambiarImagen(el, src) {
        const img = document.getElementById('mainImg');
        img.classList.add('fade');
        setTimeout(() => {
            img.src = src;
            img.classList.remove('fade');
        }, 150);
        document.querySelectorAll('#galleryThumbs .thumb').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
    }

    function abrirModal() {
        document.getElementById('modalOrden').classList.add('open');
    }

    function cerrarModal() {
        document.getElementById('modalOrden').classList.remove('open');
        <?php if ($ordenCreada): ?>
            window.location.href = '/licencia/<?= htmlspecialchars($licencia['slug']) ?>';
        <?php endif; ?>
    }

    function validarForm() {
        return document.getElementById('nombre').value.trim() !== ''
            && document.getElementById('apellido').value.trim() !== ''
            && document.getElementById('email').value.trim() !== ''
            && document.getElementById('telefono').value.trim() !== ''
            && document.getElementById('pais').value !== ''
            && document.getElementById('direccion').value.trim() !== '';
    }

    // Cerrar al hacer clic fuera del modal
    document.getElementById('modalOrden').addEventListener('click', function(e) {
        if (e.target === this) cerrarModal();
    });

    // Abrir automáticamente si se acaba de crear una orden
    <?php if ($ordenCreada): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('modalOrden').classList.add('open');
        });
    <?php endif; ?>
</script>

<?php include __DIR__ . '/../components/footer.php'; ?>
