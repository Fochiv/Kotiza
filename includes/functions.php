<?php
require_once __DIR__ . '/../config/database.php';

function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin(string $redirect = '/auth/login.php'): void {
    if (!isLoggedIn()) {
        header('Location: ' . $redirect);
        exit;
    }
}

function requireAdmin(): void {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function generateSlug(string $text): string {
    $text = strtolower($text);
    $text = preg_replace('/[àáâãäå]/u', 'a', $text);
    $text = preg_replace('/[èéêë]/u', 'e', $text);
    $text = preg_replace('/[ìíîï]/u', 'i', $text);
    $text = preg_replace('/[òóôõö]/u', 'o', $text);
    $text = preg_replace('/[ùúûü]/u', 'u', $text);
    $text = preg_replace('/[ç]/u', 'c', $text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    $text = trim($text, '-');
    return $text . '-' . substr(uniqid(), -4);
}

function generateReference(): string {
    return 'KTZ-' . strtoupper(uniqid()) . '-' . rand(1000, 9999);
}

function formatAmount(float $amount, string $currency = 'FCFA'): string {
    return number_format($amount, 0, ',', ' ') . ' ' . $currency;
}

function progressPercent(float $collected, float $goal): float {
    if ($goal <= 0) return 0;
    return min(100, round(($collected / $goal) * 100, 1));
}

function uploadFile(array $file, string $dir, array $allowedTypes, int $maxSize = 5242880): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > $maxSize) return null;
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedTypes)) return null;
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . strtolower($ext);
    $path = rtrim($dir, '/') . '/' . $filename;
    
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $path)) return null;
    
    return $filename;
}

function getKYCStatus(int $userId): ?string {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT status FROM kyc WHERE user_id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row ? $row['status'] : null;
}

function redirectWithMessage(string $url, string $msg, string $type = 'success'): void {
    $_SESSION['flash_message'] = $msg;
    $_SESSION['flash_type'] = $type;
    header('Location: ' . $url);
    exit;
}

function getFlashMessage(): ?array {
    if (isset($_SESSION['flash_message'])) {
        $msg = ['message' => $_SESSION['flash_message'], 'type' => $_SESSION['flash_type'] ?? 'info'];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $msg;
    }
    return null;
}

function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return "À l'instant";
    if ($diff < 3600) return floor($diff / 60) . ' min';
    if ($diff < 86400) return floor($diff / 3600) . 'h';
    if ($diff < 2592000) return floor($diff / 86400) . 'j';
    return date('d/m/Y', $time);
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function getCampaignStats(): array {
    $pdo = getDB();
    $stats = [];
    $stats['total_campaigns'] = $pdo->query("SELECT COUNT(*) FROM campaigns")->fetchColumn();
    $stats['active_campaigns'] = $pdo->query("SELECT COUNT(*) FROM campaigns WHERE status='active'")->fetchColumn();
    $stats['total_donations'] = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE status='success'")->fetchColumn();
    $stats['total_donors'] = $pdo->query("SELECT COUNT(DISTINCT COALESCE(donor_id, donor_email, donor_name)) FROM donations WHERE status='success'")->fetchColumn();
    $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
    $stats['total_withdrawals'] = $pdo->query("SELECT COALESCE(SUM(net_amount),0) FROM withdrawals WHERE status='completed'")->fetchColumn();
    $stats['commissions'] = $pdo->query("SELECT COALESCE(SUM(commission),0) FROM withdrawals WHERE status='completed'")->fetchColumn();
    return $stats;
}

function ashtechCollect(array $params): array {
    $ch = curl_init(ASHTECH_BASE_URL . '/v1/collect');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . ASHTECH_API_KEY,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($params),
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($body, true) ?? [];
    $data['_http_code'] = $httpCode;
    return $data;
}

function ashtechGetTransaction(string $transactionId): array {
    $ch = curl_init(ASHTECH_BASE_URL . '/v1/transaction/' . $transactionId);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . ASHTECH_API_KEY],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($body, true) ?? [];
    $data['_http_code'] = $httpCode;
    return $data;
}
