<?php
$pageTitle = 'Mes cagnottes';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$user = getCurrentUser();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']) && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $delId = (int)$_POST['delete_id'];
    $check = $pdo->prepare("SELECT id FROM campaigns WHERE id=? AND user_id=?");
    $check->execute([$delId, $user['id']]);
    if ($check->fetch()) {
        $pdo->prepare("DELETE FROM campaigns WHERE id=?")->execute([$delId]);
        redirectWithMessage('/user/campaigns.php', 'Cagnotte supprimée.', 'success');
    }
}

$campaigns = $pdo->prepare("SELECT * FROM campaigns WHERE user_id=? ORDER BY created_at DESC");
$campaigns->execute([$user['id']]);
$list = $campaigns->fetchAll();
require_once __DIR__ . '/../includes/dashboard_header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <div>
    <h1 class="page-title">Mes cagnottes</h1>
    <p class="page-subtitle">Gérez vos collectes de fonds</p>
  </div>
  <a href="/user/create-campaign.php" class="btn-primary-kotiza">
    <i class="bi bi-plus-circle"></i> <span class="d-none d-sm-inline">Nouvelle cagnotte</span>
  </a>
</div>

<?php if (empty($list)): ?>
  <div class="card-kotiza text-center py-5">
    <div style="font-size:4rem;">📭</div>
    <h4 style="margin-top:1rem;color:var(--text);">Aucune cagnotte</h4>
    <p style="color:var(--text-muted);">Créez votre première cagnotte pour commencer à collecter.</p>
    <a href="/user/create-campaign.php" class="btn-primary-kotiza mt-2">
      <i class="bi bi-plus-circle"></i> Créer une cagnotte
    </a>
  </div>
<?php else: ?>
  <div class="row g-4">
    <?php foreach ($list as $c):
      $pct = progressPercent($c['collected_amount'], $c['goal_amount']);
      $shareUrl = APP_URL . '/campaign/view.php?slug=' . $c['slug'];
    ?>
    <div class="col-md-6 col-xl-4">
      <div class="card-kotiza">
        <div class="card-img-placeholder" style="height:140px;"><?= match($c['category']) { 'Santé'=>'🏥','Éducation'=>'📚','Urgence'=>'🚨','Humanitaire'=>'🤝',default=>'💡' } ?></div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-1">
            <span class="badge-kotiza badge-info"><?= sanitize($c['category']) ?></span>
            <span class="badge-kotiza <?= $c['status']==='active' ? 'badge-success' : 'badge-warning' ?>"><?= $c['status'] === 'active' ? 'Active' : 'Terminée' ?></span>
          </div>
          <h5 class="card-title mt-2"><?= sanitize($c['title']) ?></h5>

          <div class="progress-kotiza"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
          <div class="progress-info">
            <span class="progress-amount"><?= formatAmount($c['collected_amount']) ?></span>
            <span><?= $pct ?>% · <?= formatAmount($c['goal_amount']) ?></span>
          </div>
          <div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px;">
            <i class="bi bi-people me-1"></i><?= $c['donors_count'] ?> donateurs
          </div>

          <div class="divider"></div>

          <div class="d-flex gap-2 flex-wrap">
            <a href="/campaign/view.php?slug=<?= $c['slug'] ?>" class="btn-sm-kotiza btn-primary-sm">
              <i class="bi bi-eye"></i> Voir
            </a>
            <a href="/user/edit-campaign.php?id=<?= $c['id'] ?>" class="btn-sm-kotiza" style="background:rgba(108,99,255,0.1);color:var(--primary);border:1px solid rgba(108,99,255,0.3);">
              <i class="bi bi-pencil"></i> Modifier
            </a>
            <button onclick="copyToClipboard('<?= $shareUrl ?>')" class="btn-sm-kotiza" style="background:rgba(67,217,173,0.1);color:var(--accent);border:1px solid rgba(67,217,173,0.3);">
              <i class="bi bi-link-45deg"></i> Lien
            </button>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette cagnotte ?')">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
              <button type="submit" class="btn-sm-kotiza btn-danger-sm"><i class="bi bi-trash"></i></button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
