<?php
$pageTitle = 'Modifier la cagnotte';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$user = getCurrentUser();
$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id=? AND user_id=?");
$stmt->execute([$id, $user['id']]);
$campaign = $stmt->fetch();

if (!$campaign) {
    redirectWithMessage('/user/campaigns.php', 'Cagnotte introuvable.', 'error');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token invalide.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $goal = floatval($_POST['goal_amount'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $customCategory = trim($_POST['custom_category'] ?? '');
        if ($category === 'Autre' && !empty($customCategory)) $category = sanitize($customCategory);

        if (empty($title) || mb_strlen($title) > 100) $errors[] = 'Titre requis (max 100 caractères).';
        if (empty($description)) $errors[] = 'Description requise.';
        if ($goal <= 0) $errors[] = 'Objectif doit être supérieur à 0.';
        if (empty($category)) $errors[] = 'Catégorie requise.';

        $coverImage = $campaign['cover_image'];
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $newImg = uploadFile($_FILES['cover_image'], UPLOAD_CAMPAIGNS_DIR, ALLOWED_IMAGE_TYPES);
            if (!$newImg) { $errors[] = 'Image invalide.'; }
            else { $coverImage = $newImg; }
        }

        if (empty($errors)) {
            $pdo->prepare("UPDATE campaigns SET title=?,description=?,cover_image=?,goal_amount=?,category=?,updated_at=CURRENT_TIMESTAMP WHERE id=?")
                ->execute([$title, $description, $coverImage, $goal, $category, $id]);
            redirectWithMessage('/user/campaigns.php', 'Cagnotte mise à jour.', 'success');
        }
    }
}

$categories = ['Santé','Éducation','Urgence','Projets','Humanitaire','Religion','Sports','Arts & Culture','Autre'];
require_once __DIR__ . '/../includes/dashboard_header.php';
?>

<div class="page-header">
  <h1 class="page-title">Modifier la cagnotte</h1>
  <p class="page-subtitle"><?= sanitize($campaign['title']) ?></p>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger border-0 rounded-3 mb-4">
    <?php foreach ($errors as $e): ?><div><i class="bi bi-exclamation-circle me-2"></i><?= sanitize($e) ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="card-kotiza" style="max-width:700px;">
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div class="mb-3">
        <label class="form-label-kotiza">Titre *</label>
        <input type="text" name="title" class="form-control-kotiza form-control" maxlength="100" required value="<?= sanitize($campaign['title']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label-kotiza">Description *</label>
        <textarea name="description" class="form-control-kotiza form-control" rows="6" required><?= sanitize($campaign['description']) ?></textarea>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label-kotiza">Objectif (FCFA) *</label>
          <input type="number" name="goal_amount" class="form-control-kotiza form-control" min="1000" required value="<?= $campaign['goal_amount'] ?>">
        </div>
        <div class="col-sm-6">
          <label class="form-label-kotiza">Catégorie *</label>
          <select name="category" class="form-control-kotiza form-control" id="category-select" onchange="toggleCustomCategory()" required>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat ?>" <?= $campaign['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div id="custom-category-div" class="mb-3" style="display:none;">
        <label class="form-label-kotiza">Catégorie personnalisée</label>
        <input type="text" name="custom_category" class="form-control-kotiza form-control" placeholder="Précisez...">
      </div>

      <div class="mb-4">
        <label class="form-label-kotiza">Nouvelle image (optionnel)</label>
        <div class="upload-area" onclick="document.getElementById('cover-input').click()">
          <input type="file" name="cover_image" id="cover-input" accept="image/*" onchange="previewImage(this,'cover-preview')">
          <?php if ($campaign['cover_image']): ?>
            <img src="/uploads/campaigns/<?= $campaign['cover_image'] ?>" class="upload-preview" id="cover-preview">
          <?php else: ?>
            <i class="bi bi-image upload-icon"></i>
            <div class="upload-text">Changer l'image</div>
            <img id="cover-preview" class="upload-preview d-none">
          <?php endif; ?>
        </div>
      </div>

      <div class="d-flex gap-3">
        <button type="submit" class="btn-primary-kotiza">
          <i class="bi bi-check-circle"></i> Sauvegarder
        </button>
        <a href="/user/campaigns.php" class="btn-outline-kotiza">Annuler</a>
      </div>
    </form>
  </div>
</div>

<script>
function toggleCustomCategory() {
  document.getElementById('custom-category-div').style.display =
    document.getElementById('category-select').value === 'Autre' ? 'block' : 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
