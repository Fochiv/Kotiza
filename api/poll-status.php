<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$transactionId = sanitize($_GET['transaction_id'] ?? '');
$reference = sanitize($_GET['reference'] ?? '');
$forceStatus = sanitize($_GET['force_status'] ?? '');

if (empty($transactionId) && empty($reference)) {
    echo json_encode(['status' => 'unknown']); exit;
}

$pdo = getDB();

if (!empty($forceStatus) && !empty($reference)) {
    $pdo->prepare("UPDATE donations SET status=? WHERE reference=?")->execute([$forceStatus === 'failed' ? 'failed' : $forceStatus, $reference]);
    echo json_encode(['status' => $forceStatus]); exit;
}

$donation = $pdo->prepare("SELECT * FROM donations WHERE reference=? OR transaction_id=? LIMIT 1");
$donation->execute([$reference, $transactionId]);
$don = $donation->fetch();

if (!$don) {
    echo json_encode(['status' => 'unknown']); exit;
}

if ($don['status'] === 'success') {
    echo json_encode(['status' => 'success']); exit;
}
if ($don['status'] === 'failed') {
    echo json_encode(['status' => 'failed']); exit;
}

if (!empty($transactionId)) {
    $apiResult = ashtechGetTransaction($transactionId);
    $apiStatus = $apiResult['status'] ?? '';

    if ($apiStatus === 'success') {
        $amount = floatval($apiResult['credited_amount'] ?? $don['amount']);
        $donStmt = $pdo->prepare("SELECT c.user_id FROM campaigns c JOIN donations d ON d.campaign_id=c.id WHERE d.id=?");
        $donStmt->execute([$don['id']]);
        $row = $donStmt->fetch();

        if ($row && $don['status'] !== 'success') {
            $pdo->prepare("UPDATE donations SET status='success', confirmed_at=CURRENT_TIMESTAMP WHERE id=?")->execute([$don['id']]);
            $pdo->prepare("UPDATE campaigns SET collected_amount=collected_amount+?, donors_count=donors_count+1 WHERE id=?")->execute([$amount, $don['campaign_id']]);

            $u = $pdo->prepare("SELECT * FROM users WHERE id=?");
            $u->execute([$row['user_id']]);
            $user = $u->fetch();

            $newPending = max(0, ($user['balance_pending'] ?? 0) - $don['amount']);
            $newAvailable = ($user['balance_available'] ?? 0) + $amount;
            $newTotal = ($user['total_collected'] ?? 0) + $amount;

            $pdo->prepare("UPDATE users SET balance_pending=?, balance_available=?, total_collected=? WHERE id=?")
                ->execute([$newPending, $newAvailable, $newTotal, $row['user_id']]);

            $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, balance_before, balance_after, description)
                           VALUES (?,?,?,?,?,?)")
                ->execute([$row['user_id'], 'credit', $amount, $user['balance_available'] ?? 0, $newAvailable, 'Don confirmé · ' . ($don['operator'] ?? '')]);
        }

        echo json_encode(['status' => 'success']); exit;
    }

    if ($apiStatus === 'failed') {
        $pdo->prepare("UPDATE donations SET status='failed' WHERE id=?")->execute([$don['id']]);
        echo json_encode(['status' => 'failed']); exit;
    }
}

echo json_encode(['status' => 'pending']);
