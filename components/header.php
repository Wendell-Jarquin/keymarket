<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
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
        }

        .header-nav li a {
            display: inline-block;
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
        }

        .btn-login {
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
                padding: 0 16px;
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
                <li><a href="/">Inicio</a></li>
                <li><a href="/diseno">Diseño</a></li>
                <li><a href="/office">Office</a></li>
                <li><a href="/arquitectura">Arquitectura</a></li>
            </ul>

            <div class="header-right">
                <a href="/login" class="btn-login">Iniciar sesión</a>
            </div>
        </div>
    </header>

    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('headerNav').classList.toggle('open');
        });
    </script>