<?php
$pageTitle = 'Connexion';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: /user/dashboard.php');
    exit;
}

$errors = [];
$loginValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide. Rechargez la page.';
    } else {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        $loginValue = sanitize($login);

        if (empty($login) || empty($password)) {
            $errors[] = 'Tous les champs sont obligatoires.';
        } else {
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR phone = ?) AND role != 'admin'");
            $stmt->execute([$login, $login]);
            $user = $stmt->fetch();

            if ($user) {
                if ($user['status'] === 'inactive') {
                    $errors[] = 'Votre compte est désactivé. Contactez l\'administrateur.';
                } elseif ($user['locked_until'] && time() < $user['locked_until']) {
                    $remaining = ceil(($user['locked_until'] - time()) / 60);
                    $errors[] = "Compte temporairement bloqué. Réessayez dans {$remaining} minute(s).";
                } elseif (!password_verify($password, $user['password'])) {
                    $attempts = $user['login_attempts'] + 1;
                    $lockedUntil = null;
                    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                        $lockedUntil = time() + LOCKOUT_TIME;
                        $errors[] = 'Trop de tentatives. Compte bloqué 15 minutes.';
                    } else {
                        $errors[] = 'Identifiants incorrects. ' . (MAX_LOGIN_ATTEMPTS - $attempts) . ' tentative(s) restante(s).';
                    }
                    $pdo->prepare("UPDATE users SET login_attempts=?, locked_until=? WHERE id=?")
                        ->execute([$attempts, $lockedUntil, $user['id']]);
                } else {
                    $pdo->prepare("UPDATE users SET login_attempts=0, locked_until=NULL WHERE id=?")
                        ->execute([$user['id']]);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['user_name'] = $user['full_name'];
                    redirectWithMessage('/user/dashboard.php', 'Bienvenue, ' . $user['full_name'] . ' !', 'success');
                }
            } else {
                $errors[] = 'Aucun compte trouvé avec ces identifiants.';
            }
        }
    }
}
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="auth-page">
  <div class="container">
    <div class="auth-card">
      <div class="auth-logo">
        <img src="/logo.png" alt="Kotiza">
        <div style="font-size:1.5rem;font-weight:900;margin-top:8px;background:linear-gradient(135deg,#6c63ff,#43d9ad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Kotiza</div>
      </div>

      <h2 class="auth-title" data-i18n="login_title">Connexion</h2>
      <p class="auth-subtitle">Content de vous revoir 👋</p>

      <?php if ($errors): ?>
        <div class="alert alert-danger border-0 rounded-3">
          <?php foreach ($errors as $e): ?>
            <div><i class="bi bi-exclamation-circle me-2"></i><?= sanitize($e) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" id="login-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="mb-3">
          <label class="form-label-kotiza" data-i18n="login_email">Email ou téléphone</label>
          <div class="input-group-kotiza">
            <i class="bi bi-person input-icon"></i>
            <input type="text" name="login" class="form-control-kotiza form-control"
                   value="<?= $loginValue ?>" placeholder="Email ou numéro de téléphone"
                   data-i18n-placeholder="login_email" required>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label-kotiza" data-i18n="login_password">Mot de passe</label>
          <div class="input-group-kotiza" style="position:relative;">
            <i class="bi bi-lock input-icon"></i>
            <input type="password" name="password" id="password-input" class="form-control-kotiza form-control" placeholder="••••••••" required>
            <button type="button" onclick="togglePass()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;">
              <i class="bi bi-eye" id="pass-eye"></i>
            </button>
          </div>
          <div class="text-end mt-1">
            <a href="#" style="font-size:0.82rem;" data-i18n="login_forgot">Mot de passe oublié ?</a>
          </div>
        </div>

        <button type="submit" class="btn-primary-kotiza w-100 justify-content-center" data-i18n="login_btn">
          Se connecter
        </button>
      </form>

      <div class="text-center mt-3" style="color:var(--text-muted);font-size:0.9rem;">
        <span data-i18n="login_no_account">Pas encore de compte ?</span>
        <a href="/auth/register.php" class="fw-600 ms-1" data-i18n="nav_register">Inscription</a>
      </div>
    </div>
  </div>
</div>

<script>
function togglePass() {
  const input = document.getElementById('password-input');
  const eye = document.getElementById('pass-eye');
  input.type = input.type === 'password' ? 'text' : 'password';
  eye.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
