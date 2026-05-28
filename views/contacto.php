<?php include __DIR__ . '/../components/header.php'; ?>
<style>
    .contacto { max-width: 700px; margin: 0 auto; padding: 80px 20px; text-align: center; }
    .contacto h1 { font-size: 2rem; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; }
    .contacto p { color: #666; margin-bottom: 40px; }
    .contacto .metodos { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .contacto .metodos a {
        background: #fff; border: 1px solid #eee; border-radius: 14px;
        padding: 32px; text-decoration: none; color: inherit;
        transition: all .2s; display: flex; flex-direction: column;
        align-items: center; gap: 12px;
    }
    .contacto .metodos a:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.06); }
    .contacto .metodos a i { font-size: 2rem; }
    .contacto .metodos a h3 { font-size: 1.1rem; font-weight: 600; }
    .contacto .metodos a span { font-size: .9rem; color: #999; }
    @media (max-width: 600px) { .contacto .metodos { grid-template-columns: 1fr; } }
</style>
<main>
    <div class="contacto">
        <h1>Contáctanos</h1>
        <p>Estamos aquí para ayudarte. Elige el medio que prefieras.</p>
        <div class="metodos">
            <a href="https://wa.me/50586181155" target="_blank">
                <i class="fab fa-whatsapp" style="color:#25d366"></i>
                <h3>WhatsApp</h3>
                <span>Respuesta en minutos</span>
            </a>
            <a href="mailto:info@keymarket.ni">
                <i class="far fa-envelope" style="color:#1a1a1a"></i>
                <h3>Correo electrónico</h3>
                <span>info@keymarket.ni</span>
            </a>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../components/footer.php'; ?>
