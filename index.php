<?php include __DIR__ . '/components/header.php'; ?>

<style>
    .hero {
        min-height: calc(100vh - 70px);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #fafafa 0%, #f0f0f0 100%);
        text-align: center;
        padding: 60px 20px;
        position: relative;
        overflow: hidden;
    }

    .hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(26,26,26,0.03) 0%, transparent 70%);
        pointer-events: none;
    }

    .hero-content {
        max-width: 720px;
        position: relative;
        z-index: 1;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #1a1a1a;
        color: #fff;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        letter-spacing: 0.5px;
        margin-bottom: 24px;
    }

    .hero h1 {
        color: #1a1a1a;
        font-weight: 700;
        font-size: 3.2rem;
        line-height: 1.15;
        margin-bottom: 20px;
        letter-spacing: -1px;
    }

    .hero h1 span {
        color: #555555;
        font-weight: 300;
    }

    .hero p {
        color: #666666;
        font-size: 1.15rem;
        line-height: 1.7;
        max-width: 540px;
        margin: 0 auto 36px;
    }

    .hero-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 32px;
        background: #1a1a1a;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.25s ease;
    }

    .btn-primary:hover {
        background: #333333;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 32px;
        background: transparent;
        color: #1a1a1a;
        border: 2px solid #1a1a1a;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.25s ease;
    }

    .btn-secondary:hover {
        background: #1a1a1a;
        color: #ffffff;
        transform: translateY(-2px);
    }

    .hero-stats {
        display: flex;
        gap: 48px;
        margin-top: 60px;
        padding-top: 40px;
        border-top: 1px solid #e0e0e0;
    }

    .hero-stat {
        text-align: center;
    }

    .hero-stat-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1a1a1a;
    }

    .hero-stat-label {
        font-size: 0.85rem;
        color: #999999;
        margin-top: 4px;
    }

    .features-bar {
        display: flex;
        justify-content: center;
        gap: 40px;
        padding: 24px 20px;
        background: #ffffff;
        border-top: 1px solid #e5e5e5;
    }

    .features-bar-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #555555;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .features-bar-item i {
        font-size: 1.1rem;
        color: #1a1a1a;
    }

    /* ========== Sections ========== */
    .section {
        padding: 80px 20px;
        max-width: 1100px;
        margin: 0 auto;
    }

    .section-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #999999;
        margin-bottom: 12px;
    }

    .section-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 16px;
        letter-spacing: -0.5px;
    }

    .section-desc {
        color: #666666;
        font-size: 1.05rem;
        line-height: 1.7;
        max-width: 600px;
        margin-bottom: 48px;
    }

    /* --- How it Works --- */
    .steps {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    .step-card {
        background: #ffffff;
        border: 1px solid #e5e5e5;
        border-radius: 16px;
        padding: 36px 24px;
        text-align: center;
        transition: all 0.25s ease;
        position: relative;
    }

    .step-card:hover {
        border-color: #cccccc;
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.06);
    }

    .step-number {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #1a1a1a;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 700;
        margin: 0 auto 18px;
    }

    .step-card h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .step-card p {
        font-size: 0.85rem;
        color: #888888;
        line-height: 1.6;
    }

    .step-card i {
        font-size: 1.4rem;
        color: #1a1a1a;
        margin-bottom: 14px;
    }

    /* --- Guarantee --- */
    .guarantee {
        background: #1a1a1a;
        padding: 80px 20px;
        text-align: center;
    }

    .guarantee-inner {
        max-width: 800px;
        margin: 0 auto;
    }

    .guarantee-icon {
        font-size: 2.5rem;
        color: #ffffff;
        margin-bottom: 20px;
    }

    .guarantee h2 {
        font-size: 2rem;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 16px;
    }

    .guarantee p {
        font-size: 1.05rem;
        color: #aaaaaa;
        line-height: 1.7;
        max-width: 600px;
        margin: 0 auto 40px;
    }

    .guarantee-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        text-align: left;
    }

    .guarantee-card {
        background: #2a2a2a;
        border-radius: 14px;
        padding: 28px;
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }

    .guarantee-card-icon {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 10px;
        background: #3a3a3a;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: #ffffff;
    }

    .guarantee-card h4 {
        color: #ffffff;
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .guarantee-card p {
        color: #999999;
        font-size: 0.88rem;
        line-height: 1.6;
        margin: 0;
    }

    /* --- CTA Final --- */
    .cta-final {
        text-align: center;
        padding: 80px 20px;
        background: #fafafa;
    }

    .cta-final h2 {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 12px;
    }

    .cta-final p {
        color: #666666;
        font-size: 1.05rem;
        margin-bottom: 32px;
    }

    @media (max-width: 768px) {
        .hero h1 {
            font-size: 2rem;
        }

        .hero p {
            font-size: 1rem;
        }

        .hero-actions {
            flex-direction: column;
            width: 100%;
        }

        .hero-actions a {
            width: 100%;
            justify-content: center;
        }

        .hero-stats {
            flex-direction: column;
            gap: 24px;
        }

        .features-bar {
            flex-direction: column;
            align-items: center;
            gap: 16px;
            padding: 20px;
        }

        .steps {
            grid-template-columns: 1fr;
        }

        .guarantee-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<main>
    <section class="hero">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-shield-alt"></i> 100% Original
            </div>
            <h1>Licencias de software <span>al mejor precio</span></h1>
            <p>Diseño, Office, Arquitectura y más. Software original con entrega inmediata y soporte técnico en Nicaragua.</p>
            <div class="hero-actions">
                <a href="/catalogo" class="btn-primary">
                    <i class="fas fa-shopping-cart"></i> Ver catálogo
                </a>
                <a href="/contacto" class="btn-secondary">
                    <i class="fas fa-phone-alt"></i> Contáctanos
                </a>
            </div>

        </div>
    </section>

    <div class="features-bar">
        <div class="features-bar-item">
            <i class="fas fa-bolt"></i> Entrega inmediata
        </div>
        <div class="features-bar-item">
            <i class="fas fa-credit-card"></i> Pagos seguros
        </div>
        <div class="features-bar-item">
            <i class="fas fa-headset"></i> Soporte especializado
        </div>
        <div class="features-bar-item">
            <i class="fas fa-medal"></i> Productos originales
        </div>
    </div>

    <!-- ========== Featured Licenses ========== -->
    <?php
    $stmt = $pdo->query("SELECT l.*, c.nombre AS cat_nombre, c.slug AS cat_slug FROM `licencias` l LEFT JOIN `categorias` c ON l.categoria_id = c.id WHERE l.activo = 1 AND l.destacado = 1 ORDER BY l.id DESC LIMIT 4");
    $destacadas = $stmt->fetchAll();
    ?>
    <?php if (count($destacadas) > 0): ?>
    <style>
        .featured-section { max-width: 1100px; margin: 0 auto; padding: 80px 20px 40px; }
        .featured-section .section-title { margin-bottom: 32px; }
        .featured-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }
        .featured-card {
            background: #fff; border-radius: 14px; overflow: hidden;
            border: 1px solid #eee; transition: all .25s ease;
            text-decoration: none; color: inherit; display: flex; flex-direction: column;
        }
        .featured-card:hover {
            transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.08);
            border-color: #ddd;
        }
        .featured-card-img {
            width: 100%; height: 160px; background: #f8f8f8;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        .featured-card-img img { width: 100%; height: 100%; object-fit: cover; }
        .featured-card-img .no-img { font-size: 2rem; color: #ddd; }
        .featured-card-body { padding: 18px; flex: 1; display: flex; flex-direction: column; }
        .featured-card-body h3 { font-size: .95rem; font-weight: 600; color: #1a1a1a; margin-bottom: 4px; }
        .featured-card-body .cat-label { font-size: .75rem; color: #999; margin-bottom: 10px; display: flex; align-items: center; gap: 4px; }
        .featured-card-body .price { font-size: 1.1rem; font-weight: 700; color: #1a1a1a; margin-top: auto; padding-top: 10px; }
        .featured-card-body .price .old { font-size: .82rem; color: #999; text-decoration: line-through; margin-left: 6px; font-weight: 400; }
        .featured-card-body .price .oferta { color: #dc2626; }
    </style>
    <section class="featured-section">
        <div class="section-label"><i class="fas fa-star"></i> DESTACADOS</div>
        <h2 class="section-title">Licencias más populares</h2>
        <div class="featured-grid">
            <?php foreach ($destacadas as $l): ?>
                <a href="/licencia/<?= htmlspecialchars($l['slug']) ?>" class="featured-card">
                    <div class="featured-card-img">
                        <?php if ($l['imagen_1']): ?>
                            <img src="/<?= htmlspecialchars($l['imagen_1']) ?>" alt="<?= htmlspecialchars($l['nombre']) ?>">
                        <?php else: ?>
                            <div class="no-img"><i class="fas fa-key"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="featured-card-body">
                        <h3><?= htmlspecialchars($l['nombre']) ?></h3>
                        <div class="cat-label"><i class="fas fa-tag"></i> <?= htmlspecialchars($l['cat_nombre']) ?></div>
                        <div class="price">
                            <?php if ($l['en_oferta'] && $l['precio_oferta']): ?>
                                <span class="oferta"><?= formatoPrecio($l['precio_oferta']) ?></span>
                                <span class="old"><?= formatoPrecio($l['precio']) ?></span>
                            <?php else: ?>
                                <?= formatoPrecio($l['precio']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ========== How it Works ========== -->
    <section class="section">
        <div class="section-label"><i class="fas fa-list-ol"></i> PROCESO</div>
        <h2 class="section-title">¿Cómo comprar tu licencia?</h2>
        <p class="section-desc">Cuatro pasos simples y tendrás tu software original con asistencia personalizada.</p>
        <div class="steps">
            <div class="step-card">
                <i class="fas fa-search"></i>
                <h3>Selecciona tu licencia</h3>
                <p>Explora nuestro catálogo y elige el producto que necesitas. Todos los precios son finales.</p>
            </div>
            <div class="step-card">
                <i class="fas fa-file-alt"></i>
                <h3>Llena el formulario</h3>
                <p>Completa tus datos o contáctanos directamente por WhatsApp para empezar tu pedido.</p>
            </div>
            <div class="step-card">
                <i class="fas fa-credit-card"></i>
                <h3>Arreglamos el pago</h3>
                <p>Te indicamos las opciones disponibles. Pagas de forma segura y recibes confirmación al instante.</p>
            </div>
            <div class="step-card">
                <i class="fas fa-headset"></i>
                <h3>Recibes tu licencia</h3>
                <p>Te enviamos tu licencia con asistencia humana. Te guiamos en la instalación si lo necesitas.</p>
            </div>
        </div>
    </section>

    <!-- ========== Guarantee ========== -->
    <section class="guarantee">
        <div class="guarantee-inner">
            <div class="guarantee-icon"><i class="fas fa-handshake"></i></div>
            <h2>Te atendemos de persona a persona</h2>
            <p>Nada de bots ni respuestas automáticas. Cuando nos escribes, un agente real toma tu caso y lo gestiona de principio a fin.</p>
            <div class="guarantee-grid">
                <div class="guarantee-card">
                    <div class="guarantee-card-icon"><i class="fas fa-user-check"></i></div>
                    <div>
                        <h4>Siempre un humano</h4>
                        <p>Te comunicas con una persona real desde el primer mensaje. Te conoce, entiende lo que necesitas y te da seguimiento personalizado.</p>
                    </div>
                </div>
                <div class="guarantee-card">
                    <div class="guarantee-card-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <h4>Garantía de respuesta rápida</h4>
                        <p>Te respondemos en la mitad del tiempo habitual. Mientras otros tardan días, nosotros resolvemos en cuestión de horas.</p>
                    </div>
                </div>
                <div class="guarantee-card">
                    <div class="guarantee-card-icon"><i class="fas fa-file-signature"></i></div>
                    <div>
                        <h4>Un solo interlocutor</h4>
                        <p>La misma persona que recibe tu solicitud la tramita hasta el final. No te revotan entre departamentos ni te hacen repetir tu historia.</p>
                    </div>
                </div>
                <div class="guarantee-card">
                    <div class="guarantee-card-icon"><i class="fas fa-shield-alt"></i></div>
                    <div>
                        <h4>Proceso transparente</h4>
                        <p>Te mantenemos al tanto en cada etapa. Sabes exactamente en qué estado está tu pedido y cuándo lo recibirás.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== Final CTA ========== -->
    <section class="cta-final">
        <h2>¿Listo para obtener tu licencia?</h2>
        <p>Habla directamente con un asesor y recibe tu software en horas, no en días.</p>
        <div class="hero-actions">
            <a href="/catalogo" class="btn-primary">
                <i class="fas fa-shopping-cart"></i> Ver catálogo
            </a>
            <a href="/contacto" class="btn-secondary">
                <i class="fas fa-comment-dots"></i> Hablar con un asesor
            </a>
        </div>
    </section>
</main>

<?php include __DIR__ . '/components/footer.php'; ?>
