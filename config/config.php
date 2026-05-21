<?php
define('APP_NAME', 'Kotiza');
define('APP_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
define('APP_VERSION', '1.0.0');
define('ASHTECH_API_KEY', 'ak_83adbb920ef3efd424561f70d6b76e7bf0ed91cce302973a');
define('ASHTECH_BASE_URL', 'https://ashtechpay.top');
define('COMMISSION_RATE', 0.10);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900);
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_KYC_TYPES', ['image/jpeg', 'image/png', 'application/pdf']);
define('UPLOAD_CAMPAIGNS_DIR', __DIR__ . '/../uploads/campaigns/');
define('UPLOAD_KYC_DIR', __DIR__ . '/../uploads/kyc/');
define('ADMIN_EMAIL', 'admin@kotiza.com');
define('ADMIN_DEFAULT_PASSWORD', 'Admin@123');
define('SESSION_LIFETIME', 3600 * 24);
define('DB_PATH', __DIR__ . '/../db/kotiza.db');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
date_default_timezone_set('Africa/Douala');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
