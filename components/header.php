<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../db/db_con.php';
$navCategorias = $pdo->query("SELECT * FROM `categorias` ORDER BY id ASC")->fetchAll();
?>
<link rel="icon" type="image/svg+xml" href="/assets/favico.svg">
<link rel="icon" type="image/x-icon" href="/assets/logo.ico">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .main-content {
            flex: 1;
        }

        .header {
            position: sticky;
            top: 0;
            width: 100%;
            background: #ffffff;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            z-index: 1000;
            border-bottom: 1px solid #e5e5e5;
        }

        .header-inner {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0;
            display: flex;
            align-items: center;
            height: 70px;
            gap: 24px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .header-logo {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .header-brand {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a1a1a;
            letter-spacing: -0.5px;
        }

        .header-brand span {
            color: #4a4a4a;
            font-weight: 300;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 4px;
            list-style: none;
            padding-left: 30px;
        }

        .header-nav li a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            color: #333333;
            border-radius: 8px;
            transition: all 0.2s ease;
            position: relative;
        }

        .header-nav li a:hover {
            background: #f0f0f0;
            color: #000000;
        }

        .header-nav li a::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: #1a1a1a;
            border-radius: 1px;
            transition: width 0.2s ease;
        }

        .header-nav li a:hover::after {
            width: 60%;
        }

        .header-right {
            display: flex;
            align-items: center;
            margin-left: auto;
        }

        .currency-switcher { margin-right: 12px; }
        .currency-btn {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 6px 12px; border-radius: 6px;
            font-size: .78rem; font-weight: 700;
            text-decoration: none; transition: all .2s;
            background: #f0f0f0; color: #1a1a1a;
            border: 1px solid #e0e0e0;
        }
        .currency-btn:hover { background: #1a1a1a; color: #fff; border-color: #1a1a1a; }

        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 24px;
            background: #1a1a1a;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-login:hover {
            background: #333333;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: auto;
        }

        .header-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #1a1a1a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .header-user-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            padding: 0 4px;
        }

        .header-user-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1a1a1a;
        }

        .header-user-role {
            font-size: 0.7rem;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-user-link {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            padding: 4px 8px;
            border-radius: 8px;
            transition: background .2s;
        }
        .header-user-link:hover { background: #f0f0f0; }
        .header-user-link .header-user-name { color: #1a1a1a; }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            color: #999;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        .btn-logout:hover {
            background: #fef2f2;
            color: #b91c1c;
        }

        .menu-toggle {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
        }

        .menu-toggle span {
            width: 24px;
            height: 2px;
            background: #1a1a1a;
            border-radius: 2px;
            transition: all 0.2s;
        }

        @media (max-width: 768px) {
            .header-inner {
                padding: 0 0 0 0;
            }

            .menu-toggle {
                display: flex;
            }

            .header-nav {
                display: none;
                position: absolute;
                top: 70px;
                left: 0;
                right: 0;
                background: #ffffff;
                flex-direction: column;
                padding: 16px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
                border-top: 1px solid #e5e5e5;
                gap: 2px;
            }

            .header-nav.open {
                display: flex;
            }

            .header-nav li a {
                display: block;
                padding: 12px 16px;
                width: 100%;
            }

            .header-nav li a::after {
                display: none;
            }

            .header-right .btn-login {
                font-size: 0.8rem;
                padding: 7px 16px;
            }

        }
        @media (max-width: 500px) {
            .header-user-info { display: none; }
        }
    </style>

    <header class="header">
        <div class="header-inner">
            <a href="/" class="header-left">
                <div class="header-logo">
                    <img src="/assets/logo.svg" alt="Key Market Nicaragua">
                </div>
                <span class="header-brand">Key Market <span>Nicaragua</span></span>
            </a>

            <button class="menu-toggle" id="menuToggle" aria-label="Menú">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <ul class="header-nav" id="headerNav">
                <li><a href="/"><i class="fas fa-home"></i> Inicio</a></li>
                <?php foreach ($navCategorias as $cat): ?>
                    <li><a href="/categoria/<?= htmlspecialchars($cat['slug']) ?>"><i class="fas <?= htmlspecialchars($cat['icono']) ?>"></i> <?= htmlspecialchars($cat['nombre']) ?></a></li>
                <?php endforeach; ?>
                <li><a href="/catalogo"><i class="fas fa-th-large"></i> Todos</a></li>
            </ul>

            <div class="header-right">
                <div class="currency-switcher">
                    <?php if (getMoneda() === 'NIO'): ?>
                        <a href="/moneda/usd" class="currency-btn" title="Cambiar a USD">C$ NIO</a>
                    <?php else: ?>
                        <a href="/moneda/nio" class="currency-btn" title="Cambiar a NIO">US$ USD</a>
                    <?php endif; ?>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="header-user">
                        <a href="/perfil" class="header-user-link">
                            <?php if (!empty($_SESSION['user_avatar'])): ?>
                                <img src="/assets/avatars/<?= htmlspecialchars($_SESSION['user_avatar']) ?>" alt="" class="header-user-avatar" style="border-radius:50%;object-fit:cover;width:34px;height:34px">
                            <?php else: ?>
                                <div class="header-user-avatar"><?= strtoupper(substr($_SESSION['user_nombre'], 0, 1)) ?></div>
                            <?php endif; ?>
                            <div class="header-user-info">
                                <span class="header-user-name"><?= htmlspecialchars($_SESSION['user_nombre']) ?></span>
                                <span class="header-user-role"><?= $_SESSION['rol'] === 'admin' ? 'Administrador' : 'Usuario' ?></span>
                            </div>
                        </a>
                        <a href="/logout" class="btn-logout" title="Cerrar sesión"><i class="fas fa-sign-out-alt"></i></a>
                    </div>
                <?php else: ?>
                    <a href="/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-login"><i class="fas fa-user"></i> Iniciar sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('headerNav').classList.toggle('open');
        });
    </script>