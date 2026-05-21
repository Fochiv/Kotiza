<?php
$pageTitle = 'Dons reçus';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$user = getCurrentUser();
$pdo = getDB();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM donations d JOIN campaigns c ON d.campaign_id=c.id WHERE c.user_id=? AND d.status='success'");
$totalStmt->execute([$user['id']]);
$total = $totalStmt->fetchColumn();
$pages = ceil($total / $perPage);

$stmt = $pdo->prepare("
  SELECT d.*, c.title as campaign_title FROM donations d
  JOIN campaigns c ON d.campaign_id=c.id
  WHERE c.user_id=? AND d.status='success'
  ORDER BY d.created_at DESC
  LIMIT ? OFFSET ?
");
$stmt->execute([$user['id'], $perPage, $offset]);
$donations = $stmt->fetchAll();
require_once __DIR__ . '/../includes/dashboard_header.php';
?>

<div class="page-header">
  <h1 class="page-title">Dons reçus</h1>
  <p class="page-subtitle"><?= number_format($total, 0, ',', ' ') ?> don(s) reçu(s) au total</p>
</div>

<?php if (empty($donations)): ?>
  <div class="card-kotiza text-center py-5">
    <i class="bi bi-heart" style="font-size:3.5rem;color:var(--text-muted);"></i>
    <h5 style="margin-top:1rem;">Aucun don pour l'instant</h5>
    <p style="color:var(--text-muted);">Partagez vos cagnottes pour commencer à recevoir des dons.</p>
  </div>
<?php else: ?>
  <div class="card-kotiza">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table-kotiza">
          <thead>
            <tr>
              <th>Date</th>
              <th>Donateur</th>
              <th>Cagnotte</th>
              <th>Opérateur</th>
              <th class="text-end">Montant</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($donations as $d): ?>
            <tr>
              <td style="white-space:nowrap;"><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#6c63ff,#43d9ad);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.8rem;flex-shrink:0;">
                    <?= strtoupper(substr($d['donor_name'], 0, 1)) ?>
                  </div>
                  <?= sanitize($d['donor_name']) ?>
                </div>
              </td>
              <td style="color:var(--text-muted);font-size:0.85rem;"><?= sanitize($d['campaign_title']) ?></td>
              <td><?= sanitize($d['operator'] ?? '-') ?></td>
              <td class="text-end" style="font-weight:700;color:var(--accent);"><?= formatAmount($d['amount']) ?></td>
              <td><span class="badge-kotiza badge-success">Confirmé</span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php if ($pages > 1): ?>
  <div class="d-flex justify-content-center gap-2 mt-4">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
      <a href="?page=<?= $i ?>" class="btn-sm-kotiza <?= $i === $page ? 'btn-primary-sm' : '' ?>" style="<?= $i !== $page ? 'background:var(--bg-card);color:var(--text);border:1px solid var(--border);' : '' ?>">
        <?= $i ?>
      </a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
