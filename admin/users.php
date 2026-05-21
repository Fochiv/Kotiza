<?php
$pageTitle = 'Utilisateurs';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $userId = (int)($_POST['user_id'] ?? 0);
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'toggle_status' && $userId) {
        $u = $pdo->prepare("SELECT status FROM users WHERE id=? AND role='user'");
        $u->execute([$userId]);
        $user = $u->fetch();
        if ($user) {
            $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
            $pdo->prepare("UPDATE users SET status=? WHERE id=?")->execute([$newStatus, $userId]);
            redirectWithMessage('/admin/users.php', 'Statut mis à jour.', 'success');
        }
    }
}

$search = sanitize($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = "WHERE role='user'";
$params = [];
if ($search) {
    $where .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$total = $pdo->prepare("SELECT COUNT(*) FROM users $where");
$total->execute($params);
$totalCount = $total->fetchColumn();
$pages = ceil($totalCount / $perPage);

$stmt = $pdo->prepare("SELECT u.*, (SELECT status FROM kyc WHERE user_id=u.id) as kyc_status FROM users u $where ORDER BY u.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <div><h1 class="page-title">Utilisateurs</h1><p class="page-subtitle"><?= $totalCount ?> utilisateur(s)</p></div>
</div>

<div class="card-kotiza mb-4">
  <div class="card-body" style="padding:1rem 1.5rem;">
    <form method="GET" class="d-flex gap-2">
      <div class="input-group-kotiza" style="flex:1;">
        <i class="bi bi-search input-icon"></i>
        <input type="text" name="search" class="form-control-kotiza form-control" placeholder="Rechercher par nom, email, téléphone..." value="<?= $search ?>">
      </div>
      <button type="submit" class="btn-sm-kotiza btn-primary-sm" style="padding:0.6rem 1rem;">Chercher</button>
      <?php if ($search): ?><a href="/admin/users.php" class="btn-sm-kotiza" style="background:var(--bg3);color:var(--text);border:1px solid var(--border);padding:0.6rem 1rem;">×</a><?php endif; ?>
    </form>
  </div>
</div>

<div class="card-kotiza">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table-kotiza">
        <thead>
          <tr>
            <th>#</th><th>Nom</th><th>Email</th><th>Téléphone</th>
            <th>Solde dispo</th><th>KYC</th><th>Statut</th><th>Inscription</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td style="color:var(--text-muted);"><?= $u['id'] ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#6c63ff,#43d9ad);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.8rem;flex-shrink:0;">
                  <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                </div>
                <?= sanitize($u['full_name']) ?>
              </div>
            </td>
            <td><?= sanitize($u['email']) ?></td>
            <td><?= sanitize($u['phone']) ?></td>
            <td style="font-weight:600;color:var(--accent);"><?= formatAmount($u['balance_available']) ?></td>
            <td>
              <?php if ($u['kyc_status'] === 'approved'): ?>
                <span class="badge-kotiza badge-success">✓ Validé</span>
              <?php elseif ($u['kyc_status'] === 'pending'): ?>
                <span class="badge-kotiza badge-warning">⏳ En attente</span>
              <?php elseif ($u['kyc_status'] === 'rejected'): ?>
                <span class="badge-kotiza badge-danger">✗ Refusé</span>
              <?php else: ?>
                <span class="badge-kotiza badge-info">—</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge-kotiza <?= $u['status']==='active' ? 'badge-success' : 'badge-danger' ?>">
                <?= $u['status'] === 'active' ? '● Actif' : '○ Inactif' ?>
              </span>
            </td>
            <td style="color:var(--text-muted);"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
            <td>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <input type="hidden" name="action" value="toggle_status">
                <button type="submit" class="btn-sm-kotiza <?= $u['status']==='active' ? 'btn-danger-sm' : 'btn-success-sm' ?>" style="font-size:0.75rem;">
                  <?= $u['status']==='active' ? 'Désactiver' : 'Activer' ?>
                </button>
              </form>
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
  <?php for ($i=1; $i<=$pages; $i++): ?>
    <a href="?page=<?=$i?>&search=<?=$search?>" class="btn-sm-kotiza <?=$i===$page?'btn-primary-sm':''?>" style="<?=$i!==$page?'background:var(--bg-card);color:var(--text);border:1px solid var(--border);':''?>"><?=$i?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
