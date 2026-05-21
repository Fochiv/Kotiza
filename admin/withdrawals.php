<?php
$pageTitle = 'Gestion des retraits';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $withdrawalId = (int)($_POST['withdrawal_id'] ?? 0);
    $action = sanitize($_POST['action'] ?? '');
    $reason = sanitize($_POST['reason'] ?? '');

    if ($withdrawalId && in_array($action, ['approve', 'reject'])) {
        $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE id=? AND status='pending'");
        $stmt->execute([$withdrawalId]);
        $w = $stmt->fetch();

        if ($w) {
            if ($action === 'approve') {
                $pdo->prepare("UPDATE withdrawals SET status='completed', processed_at=CURRENT_TIMESTAMP, processed_by=? WHERE id=?")
                    ->execute([$_SESSION['user_id'], $withdrawalId]);
                redirectWithMessage('/admin/withdrawals.php', 'Retrait validé avec succès.', 'success');
            } elseif ($action === 'reject') {
                if (empty($reason)) {
                    redirectWithMessage('/admin/withdrawals.php', 'Le motif de refus est obligatoire.', 'error');
                }
                $pdo->prepare("UPDATE withdrawals SET status='rejected', rejection_reason=?, processed_at=CURRENT_TIMESTAMP, processed_by=? WHERE id=?")
                    ->execute([$reason, $_SESSION['user_id'], $withdrawalId]);

                $user = $pdo->prepare("SELECT * FROM users WHERE id=?");
                $user->execute([$w['user_id']]);
                $u = $user->fetch();
                $newBalance = ($u['balance_available'] ?? 0) + $w['amount'];
                $pdo->prepare("UPDATE users SET balance_available=? WHERE id=?")->execute([$newBalance, $w['user_id']]);

                $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, balance_before, balance_after, description)
                               VALUES (?,?,?,?,?,?)")
                    ->execute([$w['user_id'], 'credit', $w['amount'], $u['balance_available'], $newBalance, 'Retrait refusé — Remboursement · ' . $reason]);

                redirectWithMessage('/admin/withdrawals.php', 'Retrait refusé et solde remboursé.', 'success');
            }
        }
    }
}

$filter = sanitize($_GET['filter'] ?? 'pending');
$where = $filter !== 'all' ? "WHERE w.status='$filter'" : '';
$stmt = $pdo->query("SELECT w.*, u.full_name, u.email FROM withdrawals w JOIN users u ON w.user_id=u.id $where ORDER BY w.requested_at DESC LIMIT 100");
$list = $stmt->fetchAll();

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <div><h1 class="page-title">Gestion des retraits</h1></div>
  <div class="d-flex gap-2">
    <?php foreach(['pending'=>'En attente','completed'=>'Validés','rejected'=>'Refusés','all'=>'Tous'] as $f=>$label): ?>
      <a href="?filter=<?=$f?>" class="btn-sm-kotiza <?=$filter===$f?'btn-primary-sm':''?>" style="<?=$filter!==$f?'background:var(--bg-card);color:var(--text);border:1px solid var(--border);':''?>"><?=$label?></a>
    <?php endforeach; ?>
  </div>
</div>

<?php if (empty($list)): ?>
  <div class="card-kotiza text-center py-5">
    <i class="bi bi-check-circle" style="font-size:3rem;color:var(--accent);"></i>
    <h5 style="margin-top:1rem;">Aucun retrait <?= $filter ?></h5>
  </div>
<?php else: ?>
<div class="card-kotiza">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table-kotiza">
        <thead>
          <tr><th>Date</th><th>Utilisateur</th><th>Montant</th><th>Commission</th><th>Net</th><th>Opérateur</th><th>Numéro</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($list as $w): ?>
          <tr>
            <td><?= date('d/m/Y', strtotime($w['requested_at'])) ?></td>
            <td>
              <div style="font-weight:600;"><?= sanitize($w['full_name']) ?></div>
              <div style="font-size:0.78rem;color:var(--text-muted);"><?= sanitize($w['email']) ?></div>
            </td>
            <td style="font-weight:600;"><?= formatAmount($w['amount']) ?></td>
            <td style="color:var(--secondary);">-<?= formatAmount($w['commission']) ?></td>
            <td style="font-weight:700;color:var(--accent);"><?= formatAmount($w['net_amount']) ?></td>
            <td><?= sanitize($w['operator']) ?></td>
            <td>
              <div class="d-flex align-items-center gap-1">
                <?= sanitize($w['mobile_number']) ?>
                <button onclick="copyToClipboard('<?= sanitize($w['mobile_number']) ?>')" class="btn-sm-kotiza" style="background:none;border:none;color:var(--primary);padding:2px 4px;" title="Copier">
                  <i class="bi bi-clipboard"></i>
                </button>
              </div>
            </td>
            <td>
              <span class="badge-kotiza <?= match($w['status']) {'completed'=>'badge-success','rejected'=>'badge-danger',default=>'badge-warning'} ?>">
                <?= match($w['status']) {'completed'=>'✓ Validé','rejected'=>'✗ Refusé',default=>'⏳ En attente'} ?>
              </span>
            </td>
            <td>
              <?php if ($w['status'] === 'pending'): ?>
              <div class="d-flex gap-1 flex-wrap">
                <form method="POST" style="display:inline;" onsubmit="return confirm('Valider ce retrait de <?= formatAmount($w['net_amount']) ?> ?')">
                  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                  <input type="hidden" name="withdrawal_id" value="<?= $w['id'] ?>">
                  <input type="hidden" name="action" value="approve">
                  <button type="submit" class="btn-sm-kotiza btn-success-sm"><i class="bi bi-check-lg"></i> Valider</button>
                </form>
                <button onclick="showRejectModal(<?= $w['id'] ?>)" class="btn-sm-kotiza btn-danger-sm"><i class="bi bi-x-lg"></i> Refuser</button>
              </div>
              <?php elseif ($w['status'] === 'rejected' && $w['rejection_reason']): ?>
                <span style="font-size:0.78rem;color:var(--secondary);" title="<?= sanitize($w['rejection_reason']) ?>">
                  <i class="bi bi-info-circle"></i> <?= mb_substr(sanitize($w['rejection_reason']), 0, 30) ?>...
                </span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Reject modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:var(--bg-card);border:1px solid var(--border);">
      <div class="modal-header" style="border-color:var(--border);">
        <h5 class="modal-title" style="color:var(--text);">Refuser le retrait</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="withdrawal_id" id="reject-id">
          <input type="hidden" name="action" value="reject">
          <label class="form-label-kotiza">Motif du refus *</label>
          <textarea name="reason" class="form-control-kotiza form-control" rows="3" placeholder="Expliquez la raison du refus..." required></textarea>
          <div class="mt-2" style="font-size:0.82rem;color:var(--text-muted);">
            <i class="bi bi-info-circle me-1"></i>Le solde sera automatiquement remboursé à l'utilisateur.
          </div>
        </div>
        <div class="modal-footer" style="border-color:var(--border);">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn-sm-kotiza btn-danger-sm" style="padding:0.5rem 1rem;">Confirmer le refus</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function showRejectModal(id) {
  document.getElementById('reject-id').value = id;
  new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
