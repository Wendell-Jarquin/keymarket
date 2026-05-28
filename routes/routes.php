<?php
require_once __DIR__ . '/../db/db_con.php';
require_once __DIR__ . '/../helpers/context_helper.php';

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request = rtrim($request, '/') ?: '/';

// Guardar contexto
$ctxData = [
    'last_page'  => $request,
    'last_method' => $_SERVER['REQUEST_METHOD'],
    'last_route'  => $request,
];
if (isset($_GET['slug'])) $ctxData['last_slug'] = $_GET['slug'];
saveContext($ctxData);

$routes = [
    '/'                     => __DIR__ . '/../index.php',
    '/login'                => __DIR__ . '/../auth/login.php',
    '/logout'               => __DIR__ . '/../auth/logout.php',
    '/catalogo'             => __DIR__ . '/../views/catalogo.php',
    '/contacto'             => __DIR__ . '/../views/contacto.php',
    '/admin/dashboard'      => __DIR__ . '/../admin/dashboard.php',
    '/admin/categorias'     => __DIR__ . '/../admin/categorias.php',
    '/admin/licencias'      => __DIR__ . '/../admin/licencias.php',
    '/admin/pedidos'        => __DIR__ . '/../admin/pedidos.php',
    '/admin/usuarios'       => __DIR__ . '/../admin/usuarios.php',
    '/setup'                => __DIR__ . '/../db/setup.php',
    '/perfil'               => __DIR__ . '/../views/perfil.php',
];

if (isset($routes[$request])) {
    require $routes[$request];
    exit;
}

// Cambiar moneda
if (preg_match('#^/moneda/(nio|usd)$#i', $request, $m)) {
    setMoneda(strtoupper($m[1]));
    $ref = $_SERVER['HTTP_REFERER'] ?? '/';
    header("Location: $ref");
    exit;
}

// Rutas dinámicas
if (preg_match('#^/categoria/([a-z0-9-]+)$#', $request, $m)) {
    $_GET['slug'] = $m[1];
    require __DIR__ . '/../views/catalogo.php';
    exit;
}

if (preg_match('#^/licencia/([a-z0-9-]+)$#', $request, $m)) {
    $_GET['slug'] = $m[1];
    require __DIR__ . '/../views/licencia.php';
    exit;
}

if (preg_match('#^/canjear/([a-zA-Z0-9]+)$#', $request, $m)) {
    $_GET['token'] = $m[1];
    require __DIR__ . '/../views/canjear.php';
    exit;
}

http_response_code(404);
echo "404 - Página no encontrada";
