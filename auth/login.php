<?php
require_once __DIR__ . '/../db/db_con.php';

if (isset($_SESSION['user_id'])) {
    $redirect = $_SESSION['rol'] === 'admin' ? '/admin/dashboard' : ($_GET['redirect'] ?? '/');
    header("Location: $redirect");
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? '/';

    if ($email === '' || $password === '') {
        $error = 'Todos los campos son obligatorios.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM `usuarios` WHERE `email` = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['rol'] = $user['rol'];
            if ($user['avatar']) $_SESSION['user_avatar'] = $user['avatar'];

            $destino = $user['rol'] === 'admin' ? '/admin/dashboard' : $redirect;
            header("Location: $destino");
            exit;
        } else {
            $error = 'Credenciales incorrectas.';
        }
    }
}

$redirect = $_GET['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? '/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — Key Market Nicaragua</title>
    <link rel="icon" type="image/svg+xml" href="/assets/favico.svg">
    <link rel="icon" type="image/x-icon" href="/assets/logo.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated background */
        .bg-animated {
            position: fixed;
            inset: 0;
            z-index: 0;
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 50%, #2a2a2a 100%);
        }

        .bg-animated .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: orbFloat 12s ease-in-out infinite alternate;
        }

        .bg-animated .orb:nth-child(1) {
            width: 400px; height: 400px;
            background: #ffffff;
            top: -10%; left: -10%;
            animation-duration: 14s;
        }

        .bg-animated .orb:nth-child(2) {
            width: 300px; height: 300px;
            background: #666666;
            bottom: -5%; right: -5%;
            animation-duration: 10s;
            animation-delay: -4s;
        }

        .bg-animated .orb:nth-child(3) {
            width: 250px; height: 250px;
            background: #444444;
            bottom: 40%; right: 30%;
            animation-duration: 16s;
            animation-delay: -8s;
        }

        @keyframes orbFloat {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(40px, -30px) scale(1.1); }
            66% { transform: translate(-20px, 40px) scale(0.9); }
            100% { transform: translate(30px, -20px) scale(1.05); }
        }

        /* Floating particles */
        .bg-animated .particles {
            position: absolute;
            inset: 0;
            overflow: hidden;
        }

        .bg-animated .particles span {
            position: absolute;
            width: 4px; height: 4px;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            animation: particleUp 20s linear infinite;
        }

        .bg-animated .particles span:nth-child(1) { left: 10%; bottom: -5%; animation-delay: 0s; animation-duration: 18s; }
        .bg-animated .particles span:nth-child(2) { left: 25%; bottom: -5%; animation-delay: 3s; animation-duration: 22s; width: 6px; height: 6px; }
        .bg-animated .particles span:nth-child(3) { left: 45%; bottom: -5%; animation-delay: 6s; animation-duration: 16s; }
        .bg-animated .particles span:nth-child(4) { left: 65%; bottom: -5%; animation-delay: 9s; animation-duration: 20s; width: 3px; height: 3px; }
        .bg-animated .particles span:nth-child(5) { left: 80%; bottom: -5%; animation-delay: 12s; animation-duration: 24s; }
        .bg-animated .particles span:nth-child(6) { left: 90%; bottom: -5%; animation-delay: 15s; animation-duration: 19s; width: 5px; height: 5px; }

        @keyframes particleUp {
            0% { transform: translateY(0) scale(1); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-110vh) scale(0.5); opacity: 0; }
        }

        .login-card {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 48px 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-logo img { width: 48px; height: 48px; }
        .login-logo h1 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-top: 12px;
        }
        .login-logo h1 span { font-weight: 300; color: #666; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
        }
        .form-group .input-wrap {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 0 14px;
            background: #fafafa;
            transition: border-color 0.2s;
        }
        .form-group .input-wrap:focus-within {
            border-color: #1a1a1a;
            background: #fff;
        }
        .form-group .input-wrap i {
            color: #999;
            font-size: 0.95rem;
        }
        .form-group .input-wrap input {
            width: 100%;
            border: none;
            background: transparent;
            padding: 12px 10px;
            font-size: 0.95rem;
            outline: none;
            font-family: inherit;
        }
        .toggle-pw {
            cursor: pointer;
            color: #bbb;
            font-size: 1rem;
            transition: color 0.2s;
            padding: 4px;
            user-select: none;
        }
        .toggle-pw:hover { color: #1a1a1a; }
        .btn-submit {
            width: 100%;
            padding: 13px;
            background: #1a1a1a;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover { background: #333; }
        .error {
            background: #fef2f2;
            color: #b91c1c;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #999;
            font-size: 0.85rem;
            text-decoration: none;
        }
        .back-link:hover { color: #1a1a1a; }
    </style>
</head>
<body>
    <div class="bg-animated">
        <div class="orb"></div>
        <div class="orb"></div>
        <div class="orb"></div>
        <div class="particles">
            <span></span><span></span><span></span>
            <span></span><span></span><span></span>
        </div>
    </div>
    <div class="login-card">
        <div class="login-logo">
            <img src="/assets/logo.svg" alt="Key Market">
            <h1>Key Market <span>Nicaragua</span></h1>
        </div>

        <?php if ($error): ?>
            <div class="error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <div class="form-group">
                <label>Correo electrónico</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="tu@correo.com" required>
                </div>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="pwField" placeholder="••••••••" required>
                    <i class="fas fa-eye toggle-pw" id="pwToggle" onclick="togglePassword()"></i>
                </div>
            </div>
            <button type="submit" class="btn-submit"><i class="fas fa-arrow-right"></i> Iniciar sesión</button>
        </form>

        <a href="/" class="back-link"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
    </div>

    <script>
        function togglePassword() {
            const pw = document.getElementById('pwField');
            const icon = document.getElementById('pwToggle');
            if (pw.type === 'password') {
                pw.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                pw.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
