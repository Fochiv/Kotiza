<?php
// PHP built-in server router
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}

// Try with .php extension
$phpFile = __DIR__ . $uri;
if (is_dir($phpFile)) {
    $phpFile = rtrim($phpFile, '/') . '/index.php';
} elseif (!file_exists($phpFile) && file_exists($phpFile . '.php')) {
    $phpFile .= '.php';
}

if (file_exists($phpFile) && !is_dir($phpFile)) {
    include $phpFile;
    return true;
}

// 404
http_response_code(404);
echo '<!DOCTYPE html><html><body style="background:#0f0e17;color:#fffffe;font-family:sans-serif;text-align:center;padding:4rem;">
<h1 style="color:#6c63ff;">404</h1><p>Page introuvable</p><a href="/" style="color:#43d9ad;">← Accueil</a>
</body></html>';
