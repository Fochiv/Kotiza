<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$currentUser = getCurrentUser();
$flash = getFlashMessage();
$kycStatus = getKYCStatus($currentUser['id']);
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>
  <link rel="icon" href="/logo.png" type="image/png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <?= isset($extraHead) ? $extraHead : '' ?>
</head>
<body>

<!-- Top navbar -->
<nav style="position:fixed;top:0;left:0;right:0;height:70px;background:var(--bg2);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 1.5rem;z-index:200;gap:1rem;">
  <button class="d-lg-none border-0" style="background:none;color:var(--text);font-size:1.4rem;" onclick="document.querySelector('.sidebar').classList.toggle('open')">
    <i class="bi bi-list"></i>
  </button>
  <a href="/" style="display:flex;align-items:center;gap:8px;text-decoration:none;">
    <img src="/logo.png" height="32" style="border-radius:8px;">
    <span style="font-weight:800;font-size:1.2rem;background:linear-gradient(135deg,#6c63ff,#43d9ad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;"><?= APP_NAME ?></span>
  </a>

  <div style="margin-left:auto;display:flex;align-items:center;gap:1rem;">
    <div class="d-flex gap-1">
      <button class="lang-btn" data-lang="fr">FR</button>
      <button class="lang-btn" data-lang="en">EN</button>
    </div>
    <button id="theme-toggle"><i class="bi bi-sun-fill" id="theme-icon"></i></button>

    <div style="display:flex;align-items:center;gap:10px;padding:6px 12px;background:var(--bg3);border-radius:10px;border:1px solid var(--border);">
      <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#6c63ff,#43d9ad);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.9rem;">
        <?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>
      </div>
      <div class="d-none d-sm-block">
        <div style="font-weight:600;font-size:0.88rem;color:var(--text);"><?= sanitize($currentUser['full_name']) ?></div>
        <div style="font-size:0.75rem;color:var(--text-muted);"><?= sanitize($currentUser['email']) ?></div>
      </div>
    </div>

    <a href="/auth/logout.php" style="color:var(--text-muted);font-size:1.2rem;" title="Déconnexion">
      <i class="bi bi-box-arrow-right"></i>
    </a>
  </div>
</nav>

<!-- Sidebar -->
<aside class="sidebar">
  <ul class="sidebar-nav">
    <li><span class="sidebar-section">Principal</span></li>
    <li><a href="/user/dashboard.php" class="sidebar-link <?= str_contains($currentPath,'dashboard') ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i> Vue d'ensemble
    </a></li>
    <li><a href="/user/campaigns.php" class="sidebar-link <?= str_contains($currentPath,'campaigns') ? 'active' : '' ?>">
      <i class="bi bi-collection"></i> Mes cagnottes
    </a></li>
    <li><a href="/user/create-campaign.php" class="sidebar-link <?= str_contains($currentPath,'create') ? 'active' : '' ?>">
      <i class="bi bi-plus-circle"></i> <span data-i18n="create_campaign">Créer une cagnotte</span>
    </a></li>
    <li><a href="/user/donations.php" class="sidebar-link <?= str_contains($currentPath,'donations') ? 'active' : '' ?>">
      <i class="bi bi-heart"></i> Dons reçus
    </a></li>

    <li><span class="sidebar-section">Finances</span></li>
    <li><a href="/user/wallet.php" class="sidebar-link <?= str_contains($currentPath,'wallet') ? 'active' : '' ?>">
      <i class="bi bi-wallet2"></i> Mon portefeuille
    </a></li>
    <li><a href="/user/withdrawal.php" class="sidebar-link <?= str_contains($currentPath,'withdrawal') && !str_contains($currentPath,'withdrawals') ? 'active' : '' ?>">
      <i class="bi bi-arrow-up-circle"></i> Demande de retrait
    </a></li>
    <li><a href="/user/withdrawals.php" class="sidebar-link <?= str_contains($currentPath,'withdrawals') ? 'active' : '' ?>">
      <i class="bi bi-clock-history"></i> Historique retraits
    </a></li>

    <li><span class="sidebar-section">Compte</span></li>
    <li><a href="/user/kyc.php" class="sidebar-link <?= str_contains($currentPath,'kyc') ? 'active' : '' ?>">
      <i class="bi bi-shield-check"></i> Vérification KYC
      <?php if ($kycStatus === 'approved'): ?>
        <i class="bi bi-check-circle-fill ms-auto" style="color:var(--accent);font-size:0.75rem;"></i>
      <?php elseif ($kycStatus === 'pending'): ?>
        <i class="bi bi-clock-fill ms-auto" style="color:var(--warning);font-size:0.75rem;"></i>
      <?php endif; ?>
    </a></li>
    <li><a href="/" class="sidebar-link"><i class="bi bi-arrow-left"></i> Retour au site</a></li>
    <li><a href="/auth/logout.php" class="sidebar-link" style="color:var(--secondary);">
      <i class="bi bi-box-arrow-right"></i> Déconnexion
    </a></li>
  </ul>
</aside>

<!-- Overlay sidebar mobile -->
<div style="display:none;" id="sidebar-overlay" onclick="document.querySelector('.sidebar').classList.remove('open');this.style.display='none';" class="d-lg-none" style="position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99;"></div>

<main class="main-content">

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'danger' : 'warning') ?> alert-dismissible border-0 rounded-3 fade show mb-4">
  <?= sanitize($flash['message']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
