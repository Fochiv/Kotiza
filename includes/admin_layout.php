<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$currentUser = getCurrentUser();
$flash = getFlashMessage();
$currentPath = $_SERVER['REQUEST_URI'] ?? '/admin/';
$stats = getCampaignStats();
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — Admin Kotiza' : 'Admin Kotiza' ?></title>
  <link rel="icon" href="/logo.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <?= isset($extraHead) ? $extraHead : '' ?>
</head>
<body>

<!-- Admin sidebar -->
<aside class="sidebar admin-sidebar">
  <div class="sidebar-brand">
    <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#6c63ff,#43d9ad);display:flex;align-items:center;justify-content:center;font-size:1.1rem;">🛡️</div>
    <span class="sidebar-brand-name">Admin</span>
  </div>

  <ul class="sidebar-nav">
    <li><span class="sidebar-section">Tableau de bord</span></li>
    <li><a href="/admin/index.php" class="sidebar-link <?= str_contains($currentPath,'admin/index') || $currentPath==='/admin/' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i> Vue d'ensemble
    </a></li>

    <li><span class="sidebar-section">Gestion</span></li>
    <li><a href="/admin/users.php" class="sidebar-link <?= str_contains($currentPath,'users') ? 'active' : '' ?>">
      <i class="bi bi-people"></i> Utilisateurs
      <span style="margin-left:auto;background:rgba(108,99,255,0.2);color:var(--primary);padding:1px 7px;border-radius:10px;font-size:0.72rem;"><?= $stats['total_users'] ?></span>
    </a></li>
    <li><a href="/admin/campaigns.php" class="sidebar-link <?= str_contains($currentPath,'admin/campaigns') ? 'active' : '' ?>">
      <i class="bi bi-collection"></i> Cagnottes
      <span style="margin-left:auto;background:rgba(67,217,173,0.15);color:var(--accent);padding:1px 7px;border-radius:10px;font-size:0.72rem;"><?= $stats['active_campaigns'] ?></span>
    </a></li>
    <li><a href="/admin/transactions.php" class="sidebar-link <?= str_contains($currentPath,'transactions') ? 'active' : '' ?>">
      <i class="bi bi-arrow-left-right"></i> Transactions
    </a></li>
    <li><a href="/admin/withdrawals.php" class="sidebar-link <?= str_contains($currentPath,'admin/withdrawals') ? 'active' : '' ?>">
      <i class="bi bi-cash-stack"></i> Retraits
    </a></li>
    <li><a href="/admin/kyc.php" class="sidebar-link <?= str_contains($currentPath,'admin/kyc') ? 'active' : '' ?>">
      <i class="bi bi-shield-check"></i> KYC
    </a></li>

    <li><span class="sidebar-section">Système</span></li>
    <li><a href="/" target="_blank" class="sidebar-link"><i class="bi bi-globe"></i> Voir le site</a></li>
    <li><a href="/auth/logout.php" class="sidebar-link" style="color:var(--secondary);">
      <i class="bi bi-box-arrow-right"></i> Déconnexion
    </a></li>
  </ul>
</aside>

<!-- Top bar -->
<div class="admin-header">
  <div class="d-flex align-items-center gap-3">
    <button class="d-lg-none border-0" style="background:none;color:var(--text);font-size:1.4rem;" onclick="document.querySelector('.sidebar').classList.toggle('open')">
      <i class="bi bi-list"></i>
    </button>
    <h5 style="margin:0;font-weight:700;color:var(--text);"><?= $pageTitle ?? 'Dashboard' ?></h5>
  </div>
  <div class="d-flex align-items-center gap-3">
    <button id="theme-toggle"><i class="bi bi-sun-fill" id="theme-icon"></i></button>
    <div style="display:flex;align-items:center;gap:8px;padding:6px 12px;background:var(--bg3);border-radius:10px;border:1px solid var(--border);">
      <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#6c63ff,#43d9ad);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.85rem;">
        <?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>
      </div>
      <span style="font-size:0.88rem;font-weight:600;color:var(--text);"><?= sanitize($currentUser['full_name']) ?></span>
    </div>
  </div>
</div>

<main class="main-content" style="margin-top:70px;">

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'danger' : 'warning') ?> alert-dismissible border-0 rounded-3 fade show mb-4">
  <?= sanitize($flash['message']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
