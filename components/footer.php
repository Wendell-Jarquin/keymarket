<style>
    .footer {
        background: #1a1a1a;
        color: #cccccc;
        padding: 60px 30px 30px;
        margin-top: auto;
    }

    .footer-inner {
        max-width: 1400px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 40px;
    }

    .footer-brand .footer-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        margin-bottom: 16px;
    }

    .footer-brand .footer-logo img {
        width: 36px;
        height: 36px;
    }

    .footer-brand .footer-logo span {
        font-size: 1.1rem;
        font-weight: 700;
        color: #ffffff;
    }

    .footer-brand .footer-logo span small {
        font-weight: 300;
        color: #999999;
    }

    .footer-brand p {
        font-size: 0.9rem;
        line-height: 1.7;
        color: #999999;
        max-width: 320px;
    }

    .footer-col h4 {
        color: #ffffff;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 20px;
    }

    .footer-col ul {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .footer-col ul li a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #999999;
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.2s ease;
    }

    .footer-col ul li a:hover {
        color: #ffffff;
    }

    .footer-bottom {
        max-width: 1400px;
        margin: 40px auto 0;
        padding-top: 24px;
        border-top: 1px solid #333333;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.85rem;
        color: #666666;
    }

    .footer-bottom-links {
        display: flex;
        gap: 24px;
    }

    .footer-bottom-links a {
        color: #666666;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .footer-bottom-links a:hover {
        color: #ffffff;
    }

    .social-links {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }

    .social-links a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #333333;
        color: #ffffff;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .social-links a:hover {
        background: #555555;
        transform: translateY(-2px);
    }

    .social-links a i {
        font-size: 1.1rem;
    }

    @media (max-width: 768px) {
        .footer {
            padding: 40px 20px 20px;
        }

        .footer-inner {
            grid-template-columns: 1fr;
            gap: 32px;
        }

        .footer-bottom {
            flex-direction: column;
            gap: 12px;
            text-align: center;
        }

        .footer-bottom-links {
            flex-wrap: wrap;
            justify-content: center;
        }
    }
</style>

<footer class="footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <a href="/" class="footer-logo">
                <img src="/assets/logo.svg" alt="Key Market Nicaragua">
                <span>Key Market <small>Nicaragua</small></span>
            </a>
            <p>Licencias de software originales al mejor precio. Diseño, Office, Arquitectura y más, con entrega inmediata en Nicaragua.</p>
            <div class="social-links">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>

        <div class="footer-col">
            <h4>Navegación</h4>
            <ul>
                <li><a href="/">Inicio</a></li>
                <li><a href="/diseno">Diseño</a></li>
                <li><a href="/office">Office</a></li>
                <li><a href="/arquitectura">Arquitectura</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Servicios</h4>
            <ul>
                <li><a href="#">Diseño Gráfico</a></li>
                <li><a href="#">Soporte Técnico</a></li>
                <li><a href="#">Consultoría</a></li>
                <li><a href="#">Proyectos</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Contacto</h4>
            <ul>
                <li><a href="#"><i class="fas fa-envelope"></i> info@keymarket.ni</a></li>
                <li><a href="#"><i class="fas fa-phone"></i> +505 8618 1155</a></li>
                <li><a href="#"><i class="fas fa-map-marker-alt"></i> Managua, Nicaragua</a></li>
                <li><a href="#"><i class="fas fa-clock"></i> Lun - Vie: 8am - 6pm</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <span>&copy; <?php echo date('Y'); ?> Key Market Nicaragua. Todos los derechos reservados.</span>
        <div class="footer-bottom-links">
            <a href="#">Privacidad</a>
            <a href="#">Términos</a>
            <a href="#">Cookies</a>
        </div>
    </div>
</footer>
