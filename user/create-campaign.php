<?php
$pageTitle = 'Créer une cagnotte';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$user = getCurrentUser();
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

        if (empty($title) || mb_strlen($title) > 100) $errors[] = 'Le titre est requis (max 100 caractères).';
        if (empty($description)) $errors[] = 'La description est requise.';
        if ($goal <= 0) $errors[] = 'L\'objectif doit être supérieur à 0.';
        if (empty($category)) $errors[] = 'La catégorie est requise.';

        $coverImage = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $coverImage = uploadFile($_FILES['cover_image'], UPLOAD_CAMPAIGNS_DIR, ALLOWED_IMAGE_TYPES);
            if (!$coverImage) $errors[] = 'Image invalide (JPG/PNG/WebP, max 5 Mo).';
        }

        if (empty($errors)) {
            $pdo = getDB();
            $slug = generateSlug($title);
            $pdo->prepare("INSERT INTO campaigns (user_id, title, slug, description, cover_image, goal_amount, category) VALUES (?,?,?,?,?,?,?)")
                ->execute([$user['id'], $title, $slug, $description, $coverImage, $goal, $category]);
            $id = $pdo->lastInsertId();
            redirectWithMessage('/campaign/view.php?slug=' . $slug, 'Cagnotte créée avec succès !', 'success');
        }
    }
}

$categories = ['Santé','Éducation','Urgence','Projets','Humanitaire','Religion','Sports','Arts & Culture','Autre'];
require_once __DIR__ . '/../includes/dashboard_header.php';
?>

<div class="page-header">
  <h1 class="page-title">Créer une cagnotte</h1>
  <p class="page-subtitle">Lancez votre collecte en quelques minutes</p>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger border-0 rounded-3 mb-4">
    <?php foreach ($errors as $e): ?><div><i class="bi bi-exclamation-circle me-2"></i><?= sanitize($e) ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card-kotiza">
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="campaign-form">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

          <div class="mb-4">
            <label class="form-label-kotiza">Titre de la cagnotte *</label>
            <input type="text" name="title" class="form-control-kotiza form-control" placeholder="Ex: Aide médicale pour Jean" maxlength="100" required value="<?= sanitize($_POST['title'] ?? '') ?>">
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:4px;">
              <span id="title-count">0</span>/100 caractères
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label-kotiza">Description *</label>
            <textarea name="description" class="form-control-kotiza form-control" rows="6"
              placeholder="Décrivez votre projet, pourquoi vous collectez, à quoi servira l'argent..." required><?= sanitize($_POST['description'] ?? '') ?></textarea>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-sm-6">
              <label class="form-label-kotiza">Objectif financier (FCFA) *</label>
              <div class="input-group-kotiza">
                <i class="bi bi-currency-exchange input-icon"></i>
                <input type="number" name="goal_amount" class="form-control-kotiza form-control" placeholder="100000" min="1000" required value="<?= sanitize($_POST['goal_amount'] ?? '') ?>">
              </div>
            </div>
            <div class="col-sm-6">
              <label class="form-label-kotiza">Catégorie *</label>
              <select name="category" class="form-control-kotiza form-control" id="category-select" onchange="toggleCustomCategory()" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat ?>" <?= ($_POST['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div id="custom-category-div" class="mb-4" style="display:none;">
            <label class="form-label-kotiza">Précisez votre catégorie</label>
            <input type="text" name="custom_category" class="form-control-kotiza form-control" placeholder="Ex: Agriculture, Mariage...">
          </div>

          <div class="mb-4">
            <label class="form-label-kotiza">Image de couverture</label>
            <div class="upload-area" onclick="document.getElementById('cover-input').click()">
              <input type="file" name="cover_image" id="cover-input" accept="image/*" onchange="previewImage(this,'cover-preview');document.getElementById('upload-icon-text').style.display='none'">
              <div id="upload-icon-text">
                <i class="bi bi-image upload-icon"></i>
                <div class="upload-text">Cliquez ou glissez une image ici<br><small>JPG, PNG, WebP · Max 5 Mo</small></div>
              </div>
              <img id="cover-preview" class="upload-preview d-none">
            </div>
          </div>

          <div class="d-flex gap-3">
            <button type="submit" class="btn-primary-kotiza">
              <i class="bi bi-rocket-takeoff"></i> Publier la cagnotte
            </button>
            <a href="/user/campaigns.php" class="btn-outline-kotiza">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card-kotiza">
      <div class="card-body">
        <h5 style="font-weight:700;margin-bottom:1rem;">💡 Conseils</h5>
        <div style="color:var(--text-muted);font-size:0.88rem;line-height:1.8;">
          <p>✅ <strong>Titre accrocheur</strong> — soyez précis et émouvant</p>
          <p>✅ <strong>Description détaillée</strong> — expliquez votre projet</p>
          <p>✅ <strong>Image de qualité</strong> — augmente les dons de 40%</p>
          <p>✅ <strong>Objectif réaliste</strong> — fixez un montant atteignable</p>
          <p>✅ <strong>Partagez</strong> sur WhatsApp, Facebook, Telegram</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function toggleCustomCategory() {
  const sel = document.getElementById('category-select').value;
  document.getElementById('custom-category-div').style.display = sel === 'Autre' ? 'block' : 'none';
}

const titleInput = document.querySelector('[name="title"]');
titleInput?.addEventListener('input', () => {
  document.getElementById('title-count').textContent = titleInput.value.length;
});
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
