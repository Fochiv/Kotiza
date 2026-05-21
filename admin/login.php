<?php
$pageTitle = 'Admin — Connexion';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn() && isAdmin()) {
    header('Location: /admin/index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token invalide.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $errors[] = 'Tous les champs sont requis.';
        } else {
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND role='admin'");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['role'] = 'admin';
                $_SESSION['user_name'] = $admin['full_name'];
                header('Location: /admin/index.php');
                exit;
            } else {
                $errors[] = 'Identifiants administrateur incorrects.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Kotiza</title>
  <link rel="icon" href="/logo.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="container">
    <div class="auth-card" style="max-width:420px;">
      <div class="auth-logo">
        <img src="/logo.png" alt="Kotiza" style="width:72px;height:72px;border-radius:18px;object-fit:cover;margin:0 auto 12px;display:block;box-shadow:0 8px 24px rgba(108,99,255,0.3);">
        <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#6c63ff,#43d9ad);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
          <i class="bi bi-shield-lock-fill" style="font-size:1.6rem;color:#fff;"></i>
        </div>
        <div style="font-size:1.5rem;font-weight:900;background:linear-gradient(135deg,#6c63ff,#43d9ad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Administration</div>
        <div style="color:var(--text-muted);font-size:0.85rem;">Accès réservé aux administrateurs</div>
      </div>

      <?php if ($errors): ?>
        <div class="alert alert-danger border-0 rounded-3">
          <?php foreach ($errors as $e): ?><div><i class="bi bi-exclamation-circle me-2"></i><?= sanitize($e) ?></div><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="mb-3">
          <label class="form-label-kotiza">Email administrateur</label>
          <div class="input-group-kotiza">
            <i class="bi bi-shield input-icon"></i>
            <input type="email" name="email" class="form-control-kotiza form-control" placeholder="admin@kotiza.com" required>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label-kotiza">Mot de passe</label>
          <div class="input-group-kotiza">
            <i class="bi bi-lock input-icon"></i>
            <input type="password" name="password" class="form-control-kotiza form-control" placeholder="••••••••" required>
          </div>
        </div>

        <button type="submit" class="btn-primary-kotiza w-100 justify-content-center">
          <i class="bi bi-shield-lock-fill"></i> Accéder au panneau admin
        </button>
      </form>

      <div class="text-center mt-3">
        <a href="/" style="font-size:0.85rem;color:var(--text-muted);">← Retour au site</a>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body>
</html>
