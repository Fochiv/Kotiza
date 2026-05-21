<?php
$pageTitle = 'Historique des retraits';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$user = getCurrentUser();
$pdo = getDB();

$list = $pdo->prepare("SELECT * FROM withdrawals WHERE user_id=? ORDER BY requested_at DESC");
$list->execute([$user['id']]);
$withdrawals = $list->fetchAll();
require_once __DIR__ . '/../includes/dashboard_header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <div>
    <h1 class="page-title">Historique des retraits</h1>
    <p class="page-subtitle">Suivi de vos demandes de retrait</p>
  </div>
  <a href="/user/withdrawal.php" class="btn-primary-kotiza">
    <i class="bi bi-plus-circle"></i> Nouveau retrait
  </a>
</div>

<?php if (empty($withdrawals)): ?>
  <div class="card-kotiza text-center py-5">
    <i class="bi bi-arrow-up-circle" style="font-size:3.5rem;color:var(--text-muted);"></i>
    <h5 style="margin-top:1rem;color:var(--text);">Aucun retrait</h5>
    <p style="color:var(--text-muted);">Vous n'avez effectué aucune demande de retrait.</p>
    <a href="/user/withdrawal.php" class="btn-primary-kotiza mt-2">
      <i class="bi bi-arrow-up-circle"></i> Faire un retrait
    </a>
  </div>
<?php else: ?>
  <div class="card-kotiza">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table-kotiza">
          <thead>
            <tr>
              <th>Date</th>
              <th>Montant brut</th>
              <th>Commission</th>
              <th>Montant net</th>
              <th>Opérateur</th>
              <th>Numéro</th>
              <th>Statut</th>
              <th>Traité le</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($withdrawals as $w): ?>
            <tr>
              <td><?= date('d/m/Y', strtotime($w['requested_at'])) ?></td>
              <td style="font-weight:600;"><?= formatAmount($w['amount']) ?></td>
              <td style="color:var(--secondary);">-<?= formatAmount($w['commission']) ?></td>
              <td style="font-weight:700;color:var(--accent);"><?= formatAmount($w['net_amount']) ?></td>
              <td><?= sanitize($w['operator']) ?></td>
              <td><?= sanitize($w['mobile_number']) ?></td>
              <td>
                <span class="badge-kotiza <?= match($w['status']) { 'completed'=>'badge-success','rejected'=>'badge-danger',default=>'badge-warning' } ?>">
                  <?= match($w['status']) { 'completed'=>'✓ Validé','rejected'=>'✗ Refusé',default=>'⏳ En attente' } ?>
                </span>
                <?php if ($w['status']==='rejected' && $w['rejection_reason']): ?>
                  <i class="bi bi-info-circle ms-1" style="color:var(--secondary);cursor:help;" title="<?= sanitize($w['rejection_reason']) ?>"></i>
                <?php endif; ?>
              </td>
              <td style="color:var(--text-muted);">
                <?= $w['processed_at'] ? date('d/m/Y', strtotime($w['processed_at'])) : '-' ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
