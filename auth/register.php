<?php
$pageTitle = 'Inscription';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: /user/dashboard.php');
    exit;
}

$errors = [];
$values = ['full_name'=>'','email'=>'','phone'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide.';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim(strtolower($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $values = ['full_name'=>sanitize($full_name),'email'=>sanitize($email),'phone'=>sanitize($phone)];

        if (empty($full_name)) $errors[] = 'Le nom complet est obligatoire.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Adresse email invalide.';
        if (empty($phone) || !preg_match('/^\+?[0-9]{8,15}$/', $phone)) $errors[] = 'Numéro de téléphone invalide.';
        if (strlen($password) < 8) $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        if ($password !== $confirm) $errors[] = 'Les mots de passe ne correspondent pas.';

        if (empty($errors)) {
            $pdo = getDB();
            $check = $pdo->prepare("SELECT id FROM users WHERE email=? OR phone=?");
            $check->execute([$email, $phone]);
            if ($check->fetch()) {
                $errors[] = 'Un compte avec cet email ou ce téléphone existe déjà.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $pdo->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?,?,?,?)")
                    ->execute([$full_name, $email, $phone, $hash]);
                $userId = $pdo->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['role'] = 'user';
                $_SESSION['user_name'] = $full_name;
                redirectWithMessage('/user/dashboard.php', 'Bienvenue sur Kotiza, ' . $full_name . ' !', 'success');
            }
        }
    }
}
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="auth-page">
  <div class="container">
    <div class="auth-card" style="max-width:520px;">
      <div class="auth-logo">
        <img src="/logo.png" alt="Kotiza">
        <div style="font-size:1.5rem;font-weight:900;margin-top:8px;background:linear-gradient(135deg,#6c63ff,#43d9ad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Kotiza</div>
      </div>

      <h2 class="auth-title" data-i18n="register_title">Créer un compte</h2>
      <p class="auth-subtitle">Rejoignez notre communauté 🌍</p>

      <?php if ($errors): ?>
        <div class="alert alert-danger border-0 rounded-3">
          <?php foreach ($errors as $e): ?>
            <div><i class="bi bi-exclamation-circle me-2"></i><?= sanitize($e) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" id="register-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="mb-3">
          <label class="form-label-kotiza" data-i18n="register_name">Nom complet</label>
          <div class="input-group-kotiza">
            <i class="bi bi-person input-icon"></i>
            <input type="text" name="full_name" class="form-control-kotiza form-control"
                   value="<?= $values['full_name'] ?>" placeholder="Jean Dupont" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label-kotiza" data-i18n="register_email">Adresse email</label>
          <div class="input-group-kotiza">
            <i class="bi bi-envelope input-icon"></i>
            <input type="email" name="email" class="form-control-kotiza form-control"
                   value="<?= $values['email'] ?>" placeholder="jean@email.com" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label-kotiza" data-i18n="register_phone">Numéro de téléphone</label>
          <div class="input-group-kotiza">
            <i class="bi bi-telephone input-icon"></i>
            <input type="tel" name="phone" class="form-control-kotiza form-control"
                   value="<?= $values['phone'] ?>" placeholder="+237 6XX XXX XXX" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label-kotiza" data-i18n="register_password">Mot de passe</label>
          <div class="input-group-kotiza" style="position:relative;">
            <i class="bi bi-lock input-icon"></i>
            <input type="password" name="password" id="pass1" class="form-control-kotiza form-control" placeholder="Min. 8 caractères" required>
            <button type="button" onclick="togglePass('pass1','eye1')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;"><i class="bi bi-eye" id="eye1"></i></button>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label-kotiza" data-i18n="register_confirm">Confirmer le mot de passe</label>
          <div class="input-group-kotiza" style="position:relative;">
            <i class="bi bi-lock-fill input-icon"></i>
            <input type="password" name="confirm_password" id="pass2" class="form-control-kotiza form-control" placeholder="Répétez le mot de passe" required>
            <button type="button" onclick="togglePass('pass2','eye2')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;"><i class="bi bi-eye" id="eye2"></i></button>
          </div>
          <div id="pass-match" class="mt-1" style="font-size:0.82rem;"></div>
        </div>

        <button type="submit" class="btn-primary-kotiza w-100 justify-content-center" data-i18n="register_btn">
          S'inscrire
        </button>
      </form>

      <div class="text-center mt-3" style="color:var(--text-muted);font-size:0.9rem;">
        <span data-i18n="register_have_account">Déjà un compte ?</span>
        <a href="/auth/login.php" class="fw-600 ms-1" data-i18n="nav_login">Connexion</a>
      </div>
    </div>
  </div>
</div>

<script>
function togglePass(id, eyeId) {
  const input = document.getElementById(id);
  const eye = document.getElementById(eyeId);
  input.type = input.type === 'password' ? 'text' : 'password';
  eye.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

document.getElementById('pass2')?.addEventListener('input', () => {
  const p1 = document.getElementById('pass1').value;
  const p2 = document.getElementById('pass2').value;
  const el = document.getElementById('pass-match');
  if (!p2) { el.textContent = ''; return; }
  if (p1 === p2) {
    el.innerHTML = '<span style="color:var(--accent)"><i class="bi bi-check-circle"></i> Les mots de passe correspondent</span>';
  } else {
    el.innerHTML = '<span style="color:var(--secondary)"><i class="bi bi-x-circle"></i> Les mots de passe ne correspondent pas</span>';
  }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
