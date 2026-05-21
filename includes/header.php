<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
$flash = getFlashMessage();
$isLogged = isLoggedIn();
$currentUser = $isLogged ? getCurrentUser() : null;
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>
  <meta name="description" content="<?= isset($pageDesc) ? sanitize($pageDesc) : 'Plateforme de crowdfunding sécurisée pour l\'Afrique francophone.' ?>">
  <link rel="icon" href="/logo.png" type="image/png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <?= isset($extraHead) ? $extraHead : '' ?>
</head>
<body>

<nav class="navbar navbar-kotiza navbar-expand-lg fixed-top" id="main-navbar">
  <div class="container">
    <a class="navbar-brand" href="/">
      <img src="/logo.png" alt="Kotiza">
      <span class="brand-name"><?= APP_NAME ?></span>
    </a>

    <div class="d-flex align-items-center gap-2 d-lg-none">
      <button id="theme-toggle" title="Changer le thème"><i class="bi bi-sun-fill" id="theme-icon"></i></button>
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" style="color:var(--text)">
        <i class="bi bi-list fs-4"></i>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="navbarMain">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item"><a class="nav-link <?= $currentPath === '/' ? 'active' : '' ?>" href="/" data-i18n="nav_home">Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="/#featured" data-i18n="nav_campaigns">Cagnottes</a></li>
        <li class="nav-item"><a class="nav-link" href="/#how" data-i18n="nav_how">Comment ça marche</a></li>
        <li class="nav-item"><a class="nav-link" href="/#testimonials" data-i18n="nav_testimonials">Témoignages</a></li>
      </ul>

      <div class="d-flex align-items-center gap-2 flex-wrap mt-2 mt-lg-0">
        <div class="d-flex gap-1">
          <button class="lang-btn" data-lang="fr">FR</button>
          <button class="lang-btn" data-lang="en">EN</button>
        </div>
        <button id="theme-toggle" class="d-none d-lg-flex" title="Changer le thème"><i class="bi bi-sun-fill" id="theme-icon"></i></button>

        <?php if ($isLogged): ?>
          <a href="/user/dashboard.php" class="nav-link" data-i18n="nav_dashboard">Mon espace</a>
          <a href="/auth/logout.php" class="btn btn-sm btn-outline-secondary rounded-3" data-i18n="nav_logout">Déconnexion</a>
        <?php else: ?>
          <a href="/auth/login.php" class="nav-link" data-i18n="nav_login">Connexion</a>
          <a href="/auth/register.php" class="btn-nav-primary nav-link" data-i18n="nav_register">Inscription</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<?php if ($flash): ?>
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
  <div class="toast align-items-center text-bg-<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'danger' : 'warning') ?> border-0 show" role="alert">
    <div class="d-flex">
      <div class="toast-body"><?= sanitize($flash['message']) ?></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
<?php endif; ?>
