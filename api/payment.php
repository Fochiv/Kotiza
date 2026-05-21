<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']); exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['error' => 'Invalid JSON']); exit;
}

$campaignId = (int)($input['campaign_id'] ?? 0);
$amount = floatval($input['amount'] ?? 0);
$currency = sanitize($input['currency'] ?? 'XAF');
$phone = sanitize($input['phone'] ?? '');
$operator = sanitize($input['operator'] ?? '');
$countryCode = sanitize($input['country_code'] ?? '');
$donorName = sanitize($input['donor_name'] ?? 'Anonyme');
$donorEmail = sanitize($input['donor_email'] ?? '');
$otp = sanitize($input['otp'] ?? '');

if (!$campaignId || $amount < 100 || empty($operator) || empty($countryCode)) {
    echo json_encode(['error' => 'Paramètres manquants']); exit;
}

$pdo = getDB();
$campaign = $pdo->prepare("SELECT * FROM campaigns WHERE id=? AND status='active'");
$campaign->execute([$campaignId]);
$camp = $campaign->fetch();
if (!$camp) {
    echo json_encode(['error' => 'Cagnotte introuvable ou inactive']); exit;
}

$reference = generateReference();
$donorId = isLoggedIn() ? $_SESSION['user_id'] : null;
$notifyUrl = APP_URL . '/api/webhook.php';

$params = [
    'amount' => (int)$amount,
    'currency' => $currency,
    'phone' => $phone,
    'operator' => $operator,
    'country_code' => $countryCode,
    'reference' => $reference,
    'notify_url' => $notifyUrl,
];

if (!empty($otp)) $params['otp'] = $otp;

$result = ashtechCollect($params);
$httpCode = $result['_http_code'] ?? 0;
unset($result['_http_code']);

if ($httpCode === 400 && ($result['error'] ?? '') === 'otp_required') {
    $pdo->prepare("INSERT INTO donations (campaign_id, donor_id, donor_name, donor_email, amount, currency, status, reference, operator, country_code, phone)
                   VALUES (?,?,?,?,?,?,'pending',?,?,?,?)")
        ->execute([$campaignId, $donorId, $donorName, $donorEmail, $amount, $currency, $reference, $operator, $countryCode, $phone]);

    echo json_encode([
        'otp_required' => true,
        'ussd_code' => $result['ussd_code'] ?? null,
        'message' => $result['message'] ?? 'OTP requis',
        'reference' => $reference,
    ]);
    exit;
}

if ($httpCode === 202 && isset($result['transaction_id'])) {
    $flow = $result['flow'] ?? 'ussd_push';
    $waveUrl = $result['wave_url'] ?? null;

    $pdo->prepare("INSERT INTO donations (campaign_id, donor_id, donor_name, donor_email, amount, currency, status, transaction_id, reference, operator, country_code, phone, payment_flow, wave_url)
                   VALUES (?,?,?,?,?,?,'pending',?,?,?,?,?,?,?)")
        ->execute([$campaignId, $donorId, $donorName, $donorEmail, $amount, $currency,
                   $result['transaction_id'], $reference, $operator, $countryCode, $phone, $flow, $waveUrl]);

    if ($waveUrl) {
        $pdo->prepare("UPDATE users SET balance_pending=balance_pending+? WHERE id=?")
            ->execute([$amount, $camp['user_id']]);
    } else {
        $pdo->prepare("UPDATE users SET balance_pending=balance_pending+? WHERE id=?")
            ->execute([$amount, $camp['user_id']]);
    }

    echo json_encode([
        'success' => true,
        'transaction_id' => $result['transaction_id'],
        'reference' => $reference,
        'flow' => $flow,
        'wave_url' => $waveUrl,
        'status' => 'pending',
    ]);
    exit;
}

echo json_encode(['error' => $result['message'] ?? 'Erreur de paiement. Réessayez.']);
