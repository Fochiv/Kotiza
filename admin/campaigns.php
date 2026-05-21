<?php
$pageTitle = 'Gestion des cagnottes';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $id = (int)($_POST['campaign_id'] ?? 0);
    $action = sanitize($_POST['action'] ?? '');
    if ($id && $action === 'delete') {
        $pdo->prepare("DELETE FROM campaigns WHERE id=?")->execute([$id]);
        redirectWithMessage('/admin/campaigns.php', 'Cagnotte supprimée.', 'success');
    }
    if ($id && in_array($action, ['active','completed'])) {
        $pdo->prepare("UPDATE campaigns SET status=? WHERE id=?")->execute([$action, $id]);
        redirectWithMessage('/admin/campaigns.php', 'Statut mis à jour.', 'success');
    }
}

$search = sanitize($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20; $offset = ($page-1)*$perPage;

$where = ''; $params = [];
if ($search) { $where = "WHERE c.title LIKE ? OR u.full_name LIKE ?"; $params = ["%$search%","%$search%"]; }

$total = $pdo->prepare("SELECT COUNT(*) FROM campaigns c JOIN users u ON c.user_id=u.id $where");
$total->execute($params);
$totalCount = $total->fetchColumn();
$pages = ceil($totalCount/$perPage);

$stmt = $pdo->prepare("SELECT c.*, u.full_name as owner FROM campaigns c JOIN users u ON c.user_id=u.id $where ORDER BY c.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$list = $stmt->fetchAll();

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <div><h1 class="page-title">Cagnottes</h1><p class="page-subtitle"><?= $totalCount ?> au total</p></div>
</div>

<div class="card-kotiza mb-4">
  <div class="card-body" style="padding:1rem 1.5rem;">
    <form method="GET" class="d-flex gap-2">
      <div class="input-group-kotiza" style="flex:1;"><i class="bi bi-search input-icon"></i>
        <input type="text" name="search" class="form-control-kotiza form-control" placeholder="Rechercher..." value="<?= $search ?>">
      </div>
      <button type="submit" class="btn-sm-kotiza btn-primary-sm" style="padding:0.6rem 1rem;">Chercher</button>
      <?php if ($search): ?><a href="/admin/campaigns.php" class="btn-sm-kotiza" style="background:var(--bg3);color:var(--text);border:1px solid var(--border);padding:0.6rem 1rem;">×</a><?php endif; ?>
    </form>
  </div>
</div>

<div class="card-kotiza">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table-kotiza">
        <thead>
          <tr><th>#</th><th>Titre</th><th>Créateur</th><th>Catégorie</th><th>Collecté</th><th>Objectif</th><th>%</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($list as $c):
            $pct = progressPercent($c['collected_amount'], $c['goal_amount']);
          ?>
          <tr>
            <td style="color:var(--text-muted);"><?= $c['id'] ?></td>
            <td style="max-width:200px;">
              <a href="/campaign/view.php?slug=<?= $c['slug'] ?>" target="_blank" style="font-weight:600;color:var(--text);">
                <?= sanitize(mb_substr($c['title'],0,40)) ?>...
              </a>
            </td>
            <td style="color:var(--text-muted);"><?= sanitize($c['owner']) ?></td>
            <td><span class="badge-kotiza badge-info"><?= sanitize($c['category']) ?></span></td>
            <td style="color:var(--accent);font-weight:600;"><?= formatAmount($c['collected_amount']) ?></td>
            <td><?= formatAmount($c['goal_amount']) ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:6px;">
                <div style="width:50px;height:5px;background:var(--border);border-radius:3px;">
                  <div style="width:<?=$pct?>%;height:100%;background:linear-gradient(90deg,#6c63ff,#43d9ad);border-radius:3px;"></div>
                </div>
                <span style="font-size:0.78rem;"><?=$pct?>%</span>
              </div>
            </td>
            <td><span class="badge-kotiza <?= $c['status']==='active'?'badge-success':'badge-warning' ?>"><?= $c['status']==='active'?'Actif':'Terminée' ?></span></td>
            <td style="color:var(--text-muted);"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
            <td>
              <div class="d-flex gap-1">
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                  <input type="hidden" name="campaign_id" value="<?= $c['id'] ?>">
                  <input type="hidden" name="action" value="<?= $c['status']==='active'?'completed':'active' ?>">
                  <button type="submit" class="btn-sm-kotiza" style="background:rgba(108,99,255,0.1);color:var(--primary);border:1px solid rgba(108,99,255,0.3);font-size:0.72rem;">
                    <?= $c['status']==='active'?'Terminer':'Activer' ?>
                  </button>
                </form>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette cagnotte ?')">
                  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                  <input type="hidden" name="campaign_id" value="<?= $c['id'] ?>">
                  <input type="hidden" name="action" value="delete">
                  <button type="submit" class="btn-sm-kotiza btn-danger-sm" style="font-size:0.72rem;"><i class="bi bi-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if ($pages > 1): ?>
<div class="d-flex justify-content-center gap-2 mt-4">
  <?php for($i=1;$i<=$pages;$i++): ?>
    <a href="?page=<?=$i?>&search=<?=$search?>" class="btn-sm-kotiza <?=$i===$page?'btn-primary-sm':''?>" style="<?=$i!==$page?'background:var(--bg-card);color:var(--text);border:1px solid var(--border);':''?>"><?=$i?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
