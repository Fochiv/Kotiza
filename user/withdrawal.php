<?php
$pageTitle = 'Demande de retrait';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$user = getCurrentUser();
$pdo = getDB();
$errors = [];
$kycStatus = getKYCStatus($user['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token invalide.';
    } elseif ($kycStatus !== 'approved') {
        $errors[] = 'Votre identité doit être vérifiée (KYC) avant tout retrait.';
    } else {
        $amount = floatval($_POST['amount'] ?? 0);
        $phone = trim($_POST['mobile_number'] ?? '');
        $operator = trim($_POST['operator'] ?? '');

        if ($amount < 1000) $errors[] = 'Montant minimum : 1 000 FCFA.';
        if ($amount > $user['balance_available']) $errors[] = 'Solde insuffisant.';
        if (empty($phone)) $errors[] = 'Numéro Mobile Money requis.';
        if (empty($operator)) $errors[] = 'Opérateur requis.';

        if (empty($errors)) {
            $commission = $amount * COMMISSION_RATE;
            $net = $amount - $commission;

            $pdo->prepare("INSERT INTO withdrawals (user_id, amount, commission, net_amount, mobile_number, operator) VALUES (?,?,?,?,?,?)")
                ->execute([$user['id'], $amount, $commission, $net, $phone, $operator]);

            $newBalance = $user['balance_available'] - $amount;
            $pdo->prepare("UPDATE users SET balance_available=? WHERE id=?")->execute([$newBalance, $user['id']]);

            $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, balance_before, balance_after, description) VALUES (?,?,?,?,?,?)")
                ->execute([$user['id'], 'debit', $amount, $user['balance_available'], $newBalance, 'Demande de retrait · ' . $operator]);

            redirectWithMessage('/user/withdrawals.php', 'Demande de retrait soumise. L\'admin la traitera sous 24h.', 'success');
        }
    }
}

require_once __DIR__ . '/../includes/dashboard_header.php';
?>

<div class="page-header">
  <h1 class="page-title">Demande de retrait</h1>
  <p class="page-subtitle">Retirez votre solde disponible via Mobile Money</p>
</div>

<?php if ($kycStatus !== 'approved'): ?>
<div class="card-kotiza mb-4" style="border-color:var(--warning);max-width:600px;">
  <div class="card-body text-center py-4">
    <i class="bi bi-shield-exclamation" style="font-size:3rem;color:var(--warning);"></i>
    <h5 style="margin-top:1rem;color:var(--text);">Vérification KYC requise</h5>
    <p style="color:var(--text-muted);">Vous devez vérifier votre identité avant d'effectuer un retrait.</p>
    <a href="/user/kyc.php" class="btn-primary-kotiza">
      <i class="bi bi-shield-check"></i> Vérifier maintenant
    </a>
  </div>
</div>
<?php else: ?>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger border-0 rounded-3 mb-4">
    <?php foreach ($errors as $e): ?><div><i class="bi bi-exclamation-circle me-2"></i><?= sanitize($e) ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card-kotiza">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3" style="background:rgba(67,217,173,0.08);border:1px solid rgba(67,217,173,0.2);">
          <i class="bi bi-wallet2" style="font-size:1.5rem;color:var(--accent);"></i>
          <div>
            <div style="font-size:0.8rem;color:var(--text-muted);">Solde disponible</div>
            <div style="font-size:1.4rem;font-weight:800;color:var(--accent);"><?= formatAmount($user['balance_available']) ?></div>
          </div>
        </div>

        <form method="POST" id="withdrawal-form">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

          <div class="mb-3">
            <label class="form-label-kotiza">Montant souhaité (FCFA) *</label>
            <div class="input-group-kotiza">
              <i class="bi bi-currency-exchange input-icon"></i>
              <input type="number" name="amount" id="withdraw-amount" class="form-control-kotiza form-control"
                placeholder="Minimum 1 000 FCFA" min="1000" max="<?= $user['balance_available'] ?>"
                required value="<?= sanitize($_POST['amount'] ?? '') ?>">
            </div>
          </div>

          <!-- Live calc -->
          <div class="p-3 rounded-3 mb-3" style="background:var(--bg2);border:1px solid var(--border);">
            <div class="d-flex justify-content-between mb-2">
              <span style="color:var(--text-muted);">Montant brut :</span>
              <span id="gross-display" style="font-weight:600;">0 FCFA</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span style="color:var(--text-muted);">Commission (10%) :</span>
              <span id="commission-display" style="color:var(--secondary);font-weight:600;">0 FCFA</span>
            </div>
            <div style="height:1px;background:var(--border);margin:8px 0;"></div>
            <div class="d-flex justify-content-between">
              <span style="font-weight:700;">Vous recevez :</span>
              <span id="net-display" style="font-weight:800;color:var(--accent);">0 FCFA</span>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label-kotiza">Numéro Mobile Money *</label>
            <div class="input-group-kotiza">
              <i class="bi bi-phone input-icon"></i>
              <input type="tel" name="mobile_number" class="form-control-kotiza form-control"
                placeholder="+237 6XX XXX XXX" required value="<?= sanitize($_POST['mobile_number'] ?? '') ?>">
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label-kotiza">Opérateur *</label>
            <select name="operator" class="form-control-kotiza form-control" required>
              <option value="">-- Choisir l'opérateur --</option>
              <option>MTN Mobile Money</option>
              <option>Orange Money</option>
              <option>Wave</option>
              <option>Moov Money</option>
              <option>Airtel Money</option>
              <option>Free Money</option>
            </select>
          </div>

          <button type="submit" class="btn-primary-kotiza w-100 justify-content-center"
            onclick="return confirm('Confirmer la demande de retrait ?')">
            <i class="bi bi-arrow-up-circle"></i> Soumettre la demande
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card-kotiza">
      <div class="card-body">
        <h5 style="font-weight:700;margin-bottom:1rem;">ℹ️ Informations</h5>
        <div style="color:var(--text-muted);font-size:0.88rem;line-height:1.9;">
          <p>💰 <strong>Commission : 10%</strong> déduite du montant brut</p>
          <p>⏱️ <strong>Délai : 24-48h</strong> après validation par l'admin</p>
          <p>📱 <strong>Vérifiez</strong> que le numéro Mobile Money est correct</p>
          <p>🔒 <strong>KYC validé</strong> est requis pour tout retrait</p>
          <p>📉 <strong>Minimum</strong> : 1 000 FCFA par retrait</p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
document.getElementById('withdraw-amount')?.addEventListener('input', function() {
  const amount = parseFloat(this.value) || 0;
  const comm = amount * 0.10;
  const net = amount - comm;
  document.getElementById('gross-display').textContent = amount.toLocaleString('fr-FR') + ' FCFA';
  document.getElementById('commission-display').textContent = comm.toLocaleString('fr-FR') + ' FCFA';
  document.getElementById('net-display').textContent = net.toLocaleString('fr-FR') + ' FCFA';
});
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
