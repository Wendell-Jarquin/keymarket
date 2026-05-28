<?php
require_once __DIR__ . '/../db/db_con.php';

$slugCategoria = $_GET['slug'] ?? null;
$categoriaActual = null;

if ($slugCategoria) {
    $stmt = $pdo->prepare("SELECT * FROM `categorias` WHERE slug = ? LIMIT 1");
    $stmt->execute([$slugCategoria]);
    $categoriaActual = $stmt->fetch();
}

// Obtener licencias
if ($categoriaActual) {
    $stmt = $pdo->prepare("SELECT l.*, c.nombre AS cat_nombre, c.slug AS cat_slug, c.icono AS cat_icono FROM `licencias` l LEFT JOIN `categorias` c ON l.categoria_id = c.id WHERE l.activo = 1 AND l.categoria_id = ? ORDER BY l.destacado DESC, l.id DESC");
    $stmt->execute([$categoriaActual['id']]);
} else {
    $stmt = $pdo->query("SELECT l.*, c.nombre AS cat_nombre, c.slug AS cat_slug, c.icono AS cat_icono FROM `licencias` l LEFT JOIN `categorias` c ON l.categoria_id = c.id WHERE l.activo = 1 ORDER BY l.destacado DESC, l.id DESC");
}
$licencias = $stmt->fetchAll();

// Agrupar por categoría si es vista general
$grupos = [];
if (!$categoriaActual) {
    foreach ($licencias as $l) {
        $key = $l['categoria_id'];
        if (!isset($grupos[$key])) {
            $grupos[$key] = [
                'nombre' => $l['cat_nombre'],
                'slug' => $l['cat_slug'],
                'icono' => $l['cat_icono'],
                'items' => []
            ];
        }
        $grupos[$key]['items'][] = $l;
    }
}

include __DIR__ . '/../components/header.php';
?>
<style>
    .catalogo-hero {
        background: linear-gradient(135deg, #fafafa 0%, #f0f0f0 100%);
        padding: 60px 20px;
        text-align: center;
        border-bottom: 1px solid #e5e5e5;
    }
    .catalogo-hero h1 { font-size: 2.2rem; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; }
    .catalogo-hero h1 i { margin-right: 10px; }
    .catalogo-hero p { color: #666; font-size: 1.05rem; }
    .catalogo-body { max-width: 1200px; margin: 0 auto; padding: 40px 20px 80px; }
    .cat-header {
        display: flex; align-items: center; gap: 12px;
        margin-bottom: 24px; margin-top: 48px;
    }
    .cat-header:first-child { margin-top: 0; }
    .cat-header .cat-icon {
        width: 44px; height: 44px; border-radius: 10px;
        background: #1a1a1a; color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    }
    .cat-header h2 { font-size: 1.4rem; font-weight: 700; color: #1a1a1a; }
    .cat-header a {
        margin-left: auto; font-size: .85rem; color: #999;
        text-decoration: none; display: flex; align-items: center; gap: 4px;
    }
    .cat-header a:hover { color: #1a1a1a; }

    .grid-licencias {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 20px;
    }

    .lic-card {
        background: #fff; border-radius: 14px; overflow: hidden;
        border: 1px solid #eee; transition: all .25s ease;
        text-decoration: none; color: inherit; display: flex; flex-direction: column;
    }
    .lic-card:hover {
        transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.08);
        border-color: #ddd;
    }
    .lic-card-img {
        width: 100%; height: 180px; background: #f8f8f8;
        display: flex; align-items: center; justify-content: center;
        overflow: hidden; position: relative;
    }
    .lic-card-img img {
        width: 100%; height: 100%; object-fit: cover;
        transition: transform .3s ease;
    }
    .lic-card:hover .lic-card-img img { transform: scale(1.05); }
    .lic-card-img .no-img {
        font-size: 2.5rem; color: #ddd;
    }
    .lic-card-badges {
        position: absolute; top: 10px; left: 10px;
        display: flex; gap: 6px;
    }
    .lic-card-badges span {
        padding: 3px 10px; border-radius: 4px; font-size: .7rem;
        font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
    }
    .badge-oferta { background: #dc2626; color: #fff; }
    .badge-destacado { background: #f59e0b; color: #fff; }
    .lic-card-body { padding: 18px; flex: 1; display: flex; flex-direction: column; }
    .lic-card-body h3 { font-size: 1rem; font-weight: 600; color: #1a1a1a; margin-bottom: 4px; }
    .lic-card-body .cat-label {
        font-size: .78rem; color: #999; margin-bottom: 10px;
        display: flex; align-items: center; gap: 4px;
    }
    .lic-card-body .lic-price { margin-top: auto; padding-top: 12px; }
    .lic-card-body .lic-price .price-actual {
        font-size: 1.25rem; font-weight: 700; color: #1a1a1a;
    }
    .lic-card-body .lic-price .price-old {
        font-size: .88rem; color: #999; text-decoration: line-through; margin-left: 8px;
    }
    .lic-card-body .lic-price .price-oferta {
        font-size: 1.25rem; font-weight: 700; color: #dc2626;
    }
    .lic-card-body .duracion {
        font-size: .78rem; color: #aaa; margin-top: 6px;
        display: flex; align-items: center; gap: 4px;
    }

    .empty-state {
        text-align: center; padding: 80px 20px; color: #999;
    }
    .empty-state i { font-size: 3rem; margin-bottom: 16px; color: #ddd; }
    .empty-state h3 { font-size: 1.2rem; color: #666; margin-bottom: 8px; }
    .empty-state p { font-size: .9rem; }

    @media (max-width: 768px) {
        .catalogo-hero h1 { font-size: 1.6rem; }
        .grid-licencias { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
        .lic-card-img { height: 140px; }
    }
</style>

<main>
    <section class="catalogo-hero">
        <h1><i class="fas <?= $categoriaActual ? htmlspecialchars($categoriaActual['icono']) : 'fa-th-large' ?>"></i> <?= $categoriaActual ? htmlspecialchars($categoriaActual['nombre']) : 'Catálogo completo' ?></h1>
        <p><?= $categoriaActual ? 'Todas las licencias de ' . htmlspecialchars($categoriaActual['nombre']) : 'Explora todas nuestras licencias de software original' ?></p>
    </section>

    <div class="catalogo-body">
        <?php if ($categoriaActual): ?>
            <?php if (count($licencias) === 0): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No hay licencias en esta categoría</h3>
                    <p>Pronto agregaremos más productos.</p>
                </div>
            <?php else: ?>
                <div class="grid-licencias">
                    <?php foreach ($licencias as $l): ?>
                        <a href="/licencia/<?= htmlspecialchars($l['slug']) ?>" class="lic-card">
                            <div class="lic-card-img">
                                <?php if ($l['imagen_1']): ?>
                                    <img src="/<?= htmlspecialchars($l['imagen_1']) ?>" alt="<?= htmlspecialchars($l['nombre']) ?>">
                                <?php else: ?>
                                    <div class="no-img"><i class="fas fa-key"></i></div>
                                <?php endif; ?>
                                <div class="lic-card-badges">
                                    <?php if ($l['en_oferta']): ?><span class="badge-oferta">Oferta</span><?php endif; ?>
                                    <?php if ($l['destacado']): ?><span class="badge-destacado">Destacado</span><?php endif; ?>
                                </div>
                            </div>
                            <div class="lic-card-body">
                                <h3><?= htmlspecialchars($l['nombre']) ?></h3>
                                <div class="cat-label"><i class="fas fa-tag"></i> <?= htmlspecialchars($l['cat_nombre']) ?></div>
                                <div class="lic-price">
                                    <?php if ($l['en_oferta'] && $l['precio_oferta']): ?>
                                        <span class="price-oferta"><?= formatoPrecio($l['precio_oferta']) ?></span>
                                        <span class="price-old"><?= formatoPrecio($l['precio']) ?></span>
                                    <?php else: ?>
                                        <span class="price-actual"><?= formatoPrecio($l['precio']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($l['duracion']): ?>
                                    <div class="duracion"><i class="far fa-clock"></i> <?= htmlspecialchars($l['duracion']) ?></div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <?php if (count($licencias) === 0): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No hay licencias disponibles</h3>
                    <p>Pronto agregaremos nuestros productos.</p>
                </div>
            <?php else: ?>
                <?php foreach ($grupos as $g): ?>
                    <div class="cat-header">
                        <div class="cat-icon"><i class="fas <?= htmlspecialchars($g['icono']) ?>"></i></div>
                        <h2><?= htmlspecialchars($g['nombre']) ?></h2>
                        <a href="/categoria/<?= htmlspecialchars($g['slug']) ?>">Ver todo <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="grid-licencias">
                        <?php foreach ($g['items'] as $l): ?>
                            <a href="/licencia/<?= htmlspecialchars($l['slug']) ?>" class="lic-card">
                                <div class="lic-card-img">
                                    <?php if ($l['imagen_1']): ?>
                                        <img src="/<?= htmlspecialchars($l['imagen_1']) ?>" alt="<?= htmlspecialchars($l['nombre']) ?>">
                                    <?php else: ?>
                                        <div class="no-img"><i class="fas fa-key"></i></div>
                                    <?php endif; ?>
                                    <div class="lic-card-badges">
                                        <?php if ($l['en_oferta']): ?><span class="badge-oferta">Oferta</span><?php endif; ?>
                                        <?php if ($l['destacado']): ?><span class="badge-destacado">Destacado</span><?php endif; ?>
                                    </div>
                                </div>
                                <div class="lic-card-body">
                                    <h3><?= htmlspecialchars($l['nombre']) ?></h3>
                                    <div class="cat-label"><i class="fas fa-tag"></i> <?= htmlspecialchars($l['cat_nombre']) ?></div>
                                    <div class="lic-price">
                                        <?php if ($l['en_oferta'] && $l['precio_oferta']): ?>
                                    <span class="price-oferta"><?= formatoPrecio($l['precio_oferta']) ?></span>
                                    <span class="price-old"><?= formatoPrecio($l['precio']) ?></span>
                                <?php else: ?>
                                    <span class="price-actual"><?= formatoPrecio($l['precio']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($l['duracion']): ?>
                                        <div class="duracion"><i class="far fa-clock"></i> <?= htmlspecialchars($l['duracion']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
