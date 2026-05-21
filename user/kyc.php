<?php
$pageTitle = 'Vérification KYC';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$user = getCurrentUser();
$pdo = getDB();
$errors = [];

$kycStmt = $pdo->prepare("SELECT * FROM kyc WHERE user_id=?");
$kycStmt->execute([$user['id']]);
$kyc = $kycStmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token invalide.';
    } elseif ($kyc && $kyc['status'] === 'approved') {
        $errors[] = 'Votre identité est déjà vérifiée.';
    } else {
        $front = uploadFile($_FILES['id_card_front'] ?? [], UPLOAD_KYC_DIR, ALLOWED_KYC_TYPES);
        $back = uploadFile($_FILES['id_card_back'] ?? [], UPLOAD_KYC_DIR, ALLOWED_KYC_TYPES);
        $selfie = uploadFile($_FILES['selfie'] ?? [], UPLOAD_KYC_DIR, ALLOWED_KYC_TYPES);

        if (!$front) $errors[] = 'Photo recto de la carte d\'identité invalide ou manquante.';
        if (!$back) $errors[] = 'Photo verso de la carte d\'identité invalide ou manquante.';
        if (!$selfie) $errors[] = 'Selfie avec carte invalide ou manquant.';

        if (empty($errors)) {
            if ($kyc) {
                $pdo->prepare("UPDATE kyc SET id_card_front=?,id_card_back=?,selfie=?,status='pending',rejection_reason=NULL,submitted_at=CURRENT_TIMESTAMP,reviewed_at=NULL WHERE user_id=?")
                    ->execute([$front, $back, $selfie, $user['id']]);
            } else {
                $pdo->prepare("INSERT INTO kyc (user_id, id_card_front, id_card_back, selfie) VALUES (?,?,?,?)")
                    ->execute([$user['id'], $front, $back, $selfie]);
            }
            redirectWithMessage('/user/kyc.php', 'Documents soumis avec succès. Validation sous 24-48h.', 'success');
        }
    }
}

$kycStmt->execute([$user['id']]);
$kyc = $kycStmt->fetch();
require_once __DIR__ . '/../includes/dashboard_header.php';
?>

<div class="page-header">
  <h1 class="page-title">Vérification d'identité (KYC)</h1>
  <p class="page-subtitle">Obligatoire avant tout retrait de fonds</p>
</div>

<?php if ($kyc): ?>
  <div class="kyc-status-card <?= $kyc['status'] ?> mb-4" style="max-width:500px;">
    <?php if ($kyc['status'] === 'approved'): ?>
      <i class="bi bi-shield-fill-check" style="font-size:3rem;color:var(--accent);"></i>
      <h4 style="margin-top:1rem;color:var(--accent);">✅ Identité vérifiée</h4>
      <p style="color:var(--text-muted);">Votre KYC est validé. Vous pouvez effectuer des retraits.</p>
    <?php elseif ($kyc['status'] === 'pending'): ?>
      <i class="bi bi-hourglass-split" style="font-size:3rem;color:var(--warning);"></i>
      <h4 style="margin-top:1rem;color:var(--warning);">⏳ En attente de validation</h4>
      <p style="color:var(--text-muted);">Vos documents ont été soumis et sont en cours d'examen.</p>
      <p style="font-size:0.82rem;color:var(--text-muted);">Soumis le <?= date('d/m/Y', strtotime($kyc['submitted_at'])) ?></p>
    <?php elseif ($kyc['status'] === 'rejected'): ?>
      <i class="bi bi-shield-x" style="font-size:3rem;color:var(--secondary);"></i>
      <h4 style="margin-top:1rem;color:var(--secondary);">❌ Dossier refusé</h4>
      <?php if ($kyc['rejection_reason']): ?>
        <div style="background:rgba(255,101,132,0.1);border-radius:8px;padding:0.75rem;margin-top:0.5rem;">
          <strong>Motif :</strong> <?= sanitize($kyc['rejection_reason']) ?>
        </div>
      <?php endif; ?>
      <p class="mt-3" style="color:var(--text-muted);">Veuillez soumettre à nouveau vos documents.</p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if (!$kyc || $kyc['status'] !== 'approved'): ?>
<?php if (!empty($errors)): ?>
  <div class="alert alert-danger border-0 rounded-3 mb-4">
    <?php foreach ($errors as $e): ?><div><i class="bi bi-exclamation-circle me-2"></i><?= sanitize($e) ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="card-kotiza" style="max-width:700px;">
  <div class="card-body">
    <div class="mb-3 p-3 rounded-3" style="background:rgba(108,99,255,0.08);border:1px solid rgba(108,99,255,0.2);">
      <i class="bi bi-info-circle me-2" style="color:var(--primary)"></i>
      <strong>Documents acceptés :</strong> JPG, PNG, PDF · <strong>Max :</strong> 5 Mo par fichier
    </div>

    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label-kotiza">Recto carte d'identité *</label>
          <div class="upload-area" onclick="this.querySelector('input').click()">
            <input type="file" name="id_card_front" accept="image/*,.pdf" required onchange="previewImage(this,'prev-front');updateUploadLabel(this,'lbl-front')">
            <i class="bi bi-credit-card upload-icon"></i>
            <div class="upload-text" id="lbl-front">Recto CNI / Passeport</div>
            <img id="prev-front" class="upload-preview d-none">
          </div>
        </div>
        <div class="col-sm-6">
          <label class="form-label-kotiza">Verso carte d'identité *</label>
          <div class="upload-area" onclick="this.querySelector('input').click()">
            <input type="file" name="id_card_back" accept="image/*,.pdf" required onchange="previewImage(this,'prev-back');updateUploadLabel(this,'lbl-back')">
            <i class="bi bi-credit-card-2-back upload-icon"></i>
            <div class="upload-text" id="lbl-back">Verso CNI</div>
            <img id="prev-back" class="upload-preview d-none">
          </div>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label-kotiza">Selfie avec votre carte d'identité *</label>
        <div class="upload-area" onclick="this.querySelector('input').click()">
          <input type="file" name="selfie" accept="image/*" required onchange="previewImage(this,'prev-selfie');updateUploadLabel(this,'lbl-selfie')">
          <i class="bi bi-camera upload-icon"></i>
          <div class="upload-text" id="lbl-selfie">Photo de vous tenant votre CNI visible</div>
          <img id="prev-selfie" class="upload-preview d-none">
        </div>
      </div>

      <button type="submit" class="btn-primary-kotiza">
        <i class="bi bi-send-check"></i> Soumettre pour vérification
      </button>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
function updateUploadLabel(input, labelId) {
  const label = document.getElementById(labelId);
  if (label && input.files && input.files[0]) {
    label.textContent = input.files[0].name;
  }
}
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
