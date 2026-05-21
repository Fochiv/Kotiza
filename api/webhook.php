<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');
http_response_code(200);
echo json_encode(['received' => true]);

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) exit;

$event = $input['event'] ?? '';
$transactionId = $input['transaction_id'] ?? '';
$reference = $input['reference'] ?? '';
$status = $input['status'] ?? '';
$amount = floatval($input['amount'] ?? 0);
$totalAmount = floatval($input['total_amount'] ?? $amount);

$pdo = getDB();

if ($event === 'payment.completed') {
    $donation = $pdo->prepare("SELECT d.*, c.user_id as campaign_owner FROM donations d JOIN campaigns c ON d.campaign_id=c.id WHERE d.reference=? OR d.transaction_id=?");
    $donation->execute([$reference, $transactionId]);
    $don = $donation->fetch();

    if ($don && $don['status'] !== 'success') {
        $pdo->prepare("UPDATE donations SET status='success', confirmed_at=CURRENT_TIMESTAMP WHERE id=?")->execute([$don['id']]);

        $creditAmount = $amount > 0 ? $amount : $don['amount'];
        $pdo->prepare("UPDATE campaigns SET collected_amount=collected_amount+?, donors_count=donors_count+1 WHERE id=?")->execute([$creditAmount, $don['campaign_id']]);

        $user = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $user->execute([$don['campaign_owner']]);
        $u = $user->fetch();

        $newPending = max(0, ($u['balance_pending'] ?? 0) - $don['amount']);
        $newAvailable = ($u['balance_available'] ?? 0) + $creditAmount;
        $newTotal = ($u['total_collected'] ?? 0) + $creditAmount;

        $pdo->prepare("UPDATE users SET balance_pending=?, balance_available=?, total_collected=? WHERE id=?")
            ->execute([$newPending, $newAvailable, $newTotal, $don['campaign_owner']]);

        $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, balance_before, balance_after, description, reference_id, reference_type)
                       VALUES (?,?,?,?,?,?,?,?)")
            ->execute([$don['campaign_owner'], 'credit', $creditAmount, $u['balance_available'] ?? 0, $newAvailable,
                       'Don reçu · ' . ($don['operator'] ?? '') . ' · ' . ($don['donor_name'] ?? 'Anonyme'), $don['id'], 'donation']);
    }
}

if ($event === 'payment.failed') {
    $pdo->prepare("UPDATE donations SET status='failed' WHERE reference=? OR transaction_id=?")
        ->execute([$reference, $transactionId]);

    $donation2 = $pdo->prepare("SELECT d.*, c.user_id as campaign_owner FROM donations d JOIN campaigns c ON d.campaign_id=c.id WHERE d.reference=? OR d.transaction_id=?");
    $donation2->execute([$reference, $transactionId]);
    $don2 = $donation2->fetch();
    if ($don2) {
        $pdo->prepare("UPDATE users SET balance_pending=GREATEST(0,balance_pending-?) WHERE id=?")
            ->execute([$don2['amount'], $don2['campaign_owner']]);
    }
}

exit;
