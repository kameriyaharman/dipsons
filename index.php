<?php
// Front controller — every request (except real files in /assets) comes here.
if (PHP_SAPI === 'cli-server') { // local dev: php -S localhost:8000 index.php
    $f = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($f) && (str_starts_with(realpath($f), __DIR__ . '/assets/') || preg_match('#/(manifest\.webmanifest|sw\.js|robots\.txt)$#', $f))) return false;
}
require __DIR__ . '/src/core.php';

$path = url_path();

// Health check for hosting platforms
if ($path === '/health') { header('Content-Type: text/plain'); echo 'ok'; exit; }

try {
    ensure_installed();
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h2>Database connection failed</h2><p>Please check database settings (config.php or environment variables).</p>';
    if (DEBUG) echo '<pre>' . e($e->getMessage()) . '</pre>';
    exit;
}

if ($path === '/admin' || str_starts_with($path, '/admin/')) {
    require APP . '/routes_admin.php';
} elseif ($path === '/app' || str_starts_with($path, '/app/')) {
    require APP . '/routes_app.php';
} else {
    require APP . '/routes_site.php';
}
not_found();
