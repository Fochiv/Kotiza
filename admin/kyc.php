<?php
$pageTitle = 'Vérification KYC';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $kycId = (int)($_POST['kyc_id'] ?? 0);
    $action = sanitize($_POST['action'] ?? '');
    $reason = sanitize($_POST['reason'] ?? '');

    if ($kycId && in_array($action, ['approve', 'reject'])) {
        if ($action === 'approve') {
            $pdo->prepare("UPDATE kyc SET status='approved', reviewed_at=CURRENT_TIMESTAMP, reviewed_by=?, rejection_reason=NULL WHERE id=?")
                ->execute([$_SESSION['user_id'], $kycId]);
            redirectWithMessage('/admin/kyc.php', 'KYC validé.', 'success');
        } elseif ($action === 'reject') {
            if (empty($reason)) redirectWithMessage('/admin/kyc.php', 'Motif de refus obligatoire.', 'error');
            $pdo->prepare("UPDATE kyc SET status='rejected', rejection_reason=?, reviewed_at=CURRENT_TIMESTAMP, reviewed_by=? WHERE id=?")
                ->execute([$reason, $_SESSION['user_id'], $kycId]);
            redirectWithMessage('/admin/kyc.php', 'KYC refusé.', 'success');
        }
    }
}

$filter = sanitize($_GET['filter'] ?? 'pending');
$where = $filter !== 'all' ? "WHERE k.status='$filter'" : '';
$stmt = $pdo->query("SELECT k.*, u.full_name, u.email FROM kyc k JOIN users u ON k.user_id=u.id $where ORDER BY k.submitted_at DESC LIMIT 100");
$list = $stmt->fetchAll();

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <div><h1 class="page-title">Vérification KYC</h1></div>
  <div class="d-flex gap-2">
    <?php foreach(['pending'=>'En attente','approved'=>'Validés','rejected'=>'Refusés','all'=>'Tous'] as $f=>$label): ?>
      <a href="?filter=<?=$f?>" class="btn-sm-kotiza <?=$filter===$f?'btn-primary-sm':''?>" style="<?=$filter!==$f?'background:var(--bg-card);color:var(--text);border:1px solid var(--border);':''?>"><?=$label?></a>
    <?php endforeach; ?>
  </div>
</div>

<?php if (empty($list)): ?>
  <div class="card-kotiza text-center py-5">
    <i class="bi bi-shield-check" style="font-size:3rem;color:var(--accent);"></i>
    <h5 style="margin-top:1rem;">Aucun KYC <?= $filter ?></h5>
  </div>
<?php else: ?>
<div class="row g-4">
  <?php foreach ($list as $k): ?>
  <div class="col-lg-6">
    <div class="card-kotiza">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <div style="font-weight:700;font-size:1rem;"><?= sanitize($k['full_name']) ?></div>
            <div style="font-size:0.82rem;color:var(--text-muted);"><?= sanitize($k['email']) ?> · Soumis le <?= date('d/m/Y', strtotime($k['submitted_at'])) ?></div>
          </div>
          <span class="badge-kotiza <?= match($k['status']){'approved'=>'badge-success','rejected'=>'badge-danger',default=>'badge-warning'} ?>">
            <?= match($k['status']){'approved'=>'✓ Validé','rejected'=>'✗ Refusé',default=>'⏳ En attente'} ?>
          </span>
        </div>

        <div class="row g-2 mb-3">
          <?php foreach ([
            ['id_card_front','🪪 Recto CNI'],
            ['id_card_back','🪪 Verso CNI'],
            ['selfie','🤳 Selfie'],
          ] as [$field, $label]): ?>
          <div class="col-4">
            <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:4px;"><?= $label ?></div>
            <?php if ($k[$field] && file_exists(UPLOAD_KYC_DIR . $k[$field])): ?>
              <?php $ext = strtolower(pathinfo($k[$field], PATHINFO_EXTENSION)); ?>
              <?php if ($ext === 'pdf'): ?>
                <a href="/uploads/kyc/<?= $k[$field] ?>" target="_blank" class="btn-sm-kotiza btn-primary-sm w-100 justify-content-center" style="font-size:0.72rem;">
                  <i class="bi bi-file-pdf"></i> PDF
                </a>
              <?php else: ?>
                <a href="/uploads/kyc/<?= $k[$field] ?>" target="_blank">
                  <img src="/uploads/kyc/<?= $k[$field] ?>" alt="<?= $label ?>" style="width:100%;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                </a>
              <?php endif; ?>
            <?php else: ?>
              <div style="height:80px;background:var(--bg2);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:0.75rem;border:1px solid var(--border);">Absent</div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if ($k['rejection_reason']): ?>
          <div style="background:rgba(255,101,132,0.1);border-radius:8px;padding:0.6rem;margin-bottom:1rem;font-size:0.82rem;color:var(--secondary);">
            <i class="bi bi-x-circle me-1"></i><strong>Motif :</strong> <?= sanitize($k['rejection_reason']) ?>
          </div>
        <?php endif; ?>

        <?php if ($k['status'] === 'pending'): ?>
        <div class="d-flex gap-2">
          <form method="POST" style="flex:1;" onsubmit="return confirm('Valider ce KYC ?')">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="kyc_id" value="<?= $k['id'] ?>">
            <input type="hidden" name="action" value="approve">
            <button type="submit" class="btn-sm-kotiza btn-success-sm w-100 justify-content-center">
              <i class="bi bi-check-lg"></i> Valider
            </button>
          </form>
          <button onclick="showKycRejectModal(<?= $k['id'] ?>)" class="btn-sm-kotiza btn-danger-sm" style="flex:1;">
            <i class="bi bi-x-lg"></i> Refuser
          </button>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Reject modal -->
<div class="modal fade" id="kycRejectModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:var(--bg-card);border:1px solid var(--border);">
      <div class="modal-header" style="border-color:var(--border);">
        <h5 class="modal-title" style="color:var(--text);">Refuser le KYC</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="kyc_id" id="kyc-reject-id">
          <input type="hidden" name="action" value="reject">
          <label class="form-label-kotiza">Motif de refus *</label>
          <textarea name="reason" class="form-control-kotiza form-control" rows="3" placeholder="Photo floue, document expiré, selfie non valide..." required></textarea>
        </div>
        <div class="modal-footer" style="border-color:var(--border);">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn-sm-kotiza btn-danger-sm" style="padding:0.5rem 1rem;">Confirmer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function showKycRejectModal(id) {
  document.getElementById('kyc-reject-id').value = id;
  new bootstrap.Modal(document.getElementById('kycRejectModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
