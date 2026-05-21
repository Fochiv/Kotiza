<?php
$pageTitle = 'Mon portefeuille';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$user = getCurrentUser();
$pdo = getDB();

$transactions = $pdo->prepare("SELECT * FROM wallet_transactions WHERE user_id=? ORDER BY created_at DESC LIMIT 50");
$transactions->execute([$user['id']]);
$txList = $transactions->fetchAll();
require_once __DIR__ . '/../includes/dashboard_header.php';
?>

<div class="page-header">
  <h1 class="page-title">Mon portefeuille</h1>
  <p class="page-subtitle">Vos soldes et mouvements financiers</p>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="stat-widget" style="border-color:rgba(67,217,173,0.4);">
      <div class="stat-widget-icon icon-green"><i class="bi bi-wallet2"></i></div>
      <div>
        <div class="stat-widget-value" style="color:var(--accent);"><?= formatAmount($user['balance_available']) ?></div>
        <div class="stat-widget-label">Solde disponible</div>
        <div style="font-size:0.75rem;color:var(--text-muted);">Prêt pour retrait</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-widget" style="border-color:rgba(255,209,102,0.4);">
      <div class="stat-widget-icon icon-yellow"><i class="bi bi-hourglass-split"></i></div>
      <div>
        <div class="stat-widget-value" style="color:var(--warning);"><?= formatAmount($user['balance_pending']) ?></div>
        <div class="stat-widget-label">Solde en attente</div>
        <div style="font-size:0.75rem;color:var(--text-muted);">Paiements non confirmés</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-widget" style="border-color:rgba(108,99,255,0.4);">
      <div class="stat-widget-icon icon-purple"><i class="bi bi-graph-up-arrow"></i></div>
      <div>
        <div class="stat-widget-value"><?= formatAmount($user['total_collected']) ?></div>
        <div class="stat-widget-label">Total collecté</div>
        <div style="font-size:0.75rem;color:var(--text-muted);">Depuis le début</div>
      </div>
    </div>
  </div>
</div>

<div class="card-kotiza">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 style="font-weight:700;margin:0;">Historique des mouvements</h5>
      <a href="/user/withdrawal.php" class="btn-sm-kotiza btn-primary-sm">
        <i class="bi bi-arrow-up-circle"></i> Retirer
      </a>
    </div>

    <?php if (empty($txList)): ?>
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-clock-history" style="font-size:3rem;"></i>
        <p class="mt-3">Aucun mouvement pour l'instant.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table-kotiza">
          <thead>
            <tr>
              <th>Date</th>
              <th>Description</th>
              <th>Type</th>
              <th class="text-end">Montant</th>
              <th class="text-end">Solde après</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($txList as $tx): ?>
            <tr>
              <td style="white-space:nowrap;"><?= date('d/m/Y H:i', strtotime($tx['created_at'])) ?></td>
              <td><?= sanitize($tx['description'] ?? '-') ?></td>
              <td>
                <span class="badge-kotiza <?= $tx['type'] === 'credit' ? 'badge-success' : ($tx['type'] === 'debit' ? 'badge-danger' : 'badge-info') ?>">
                  <?= $tx['type'] === 'credit' ? '+ Crédit' : ($tx['type'] === 'debit' ? '− Débit' : 'En attente') ?>
                </span>
              </td>
              <td class="text-end" style="font-weight:700;color:<?= $tx['type'] === 'credit' ? 'var(--accent)' : 'var(--secondary)' ?>;">
                <?= ($tx['type'] === 'credit' ? '+' : '-') . formatAmount(abs($tx['amount'])) ?>
              </td>
              <td class="text-end" style="color:var(--text-muted);"><?= formatAmount($tx['balance_after']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
