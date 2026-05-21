<?php
$pageTitle = 'Tableau de bord';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$user = getCurrentUser();
$pdo = getDB();

$campaigns = $pdo->prepare("SELECT COUNT(*) FROM campaigns WHERE user_id=?");
$campaigns->execute([$user['id']]);
$campaignCount = $campaigns->fetchColumn();

$donationsCount = $pdo->prepare("SELECT COUNT(*) FROM donations d JOIN campaigns c ON d.campaign_id=c.id WHERE c.user_id=? AND d.status='success'");
$donationsCount->execute([$user['id']]);
$totalDonations = $donationsCount->fetchColumn();

$recentDonations = $pdo->prepare("
  SELECT d.*, c.title as campaign_title FROM donations d
  JOIN campaigns c ON d.campaign_id = c.id
  WHERE c.user_id = ? AND d.status='success'
  ORDER BY d.created_at DESC LIMIT 5
");
$recentDonations->execute([$user['id']]);
$recent = $recentDonations->fetchAll();

$recentCampaigns = $pdo->prepare("SELECT * FROM campaigns WHERE user_id=? ORDER BY created_at DESC LIMIT 3");
$recentCampaigns->execute([$user['id']]);
$myCampaigns = $recentCampaigns->fetchAll();

$kycStatus = getKYCStatus($user['id']);
require_once __DIR__ . '/../includes/dashboard_header.php';
?>

<div class="page-header">
  <h1 class="page-title">👋 <span data-i18n="dashboard_welcome">Bienvenue</span>, <?= sanitize($user['full_name']) ?></h1>
  <p class="page-subtitle">Voici un résumé de votre activité</p>
</div>

<!-- KYC Alert -->
<?php if (!$kycStatus): ?>
<div class="alert border-0 rounded-3 mb-4" style="background:rgba(255,209,102,0.1);border-left:4px solid var(--warning) !important;border-left-width:4px;padding-left:1.25rem;">
  <i class="bi bi-shield-exclamation me-2" style="color:var(--warning)"></i>
  <strong>Vérification d'identité requise</strong> pour effectuer des retraits.
  <a href="/user/kyc.php" class="ms-2 fw-bold" style="color:var(--warning)">Vérifier maintenant →</a>
</div>
<?php elseif ($kycStatus === 'rejected'): ?>
<div class="alert border-0 rounded-3 mb-4" style="background:rgba(255,101,132,0.1);">
  <i class="bi bi-x-circle me-2" style="color:var(--secondary)"></i>
  <strong>KYC refusé.</strong> Veuillez soumettre à nouveau vos documents.
  <a href="/user/kyc.php" class="ms-2 fw-bold" style="color:var(--secondary)">Resoumettre →</a>
</div>
<?php endif; ?>

<!-- Stat Widgets -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="stat-widget">
      <div class="stat-widget-icon icon-green"><i class="bi bi-wallet2"></i></div>
      <div>
        <div class="stat-widget-value"><?= formatAmount($user['balance_available']) ?></div>
        <div class="stat-widget-label" data-i18n="dashboard_balance">Solde disponible</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-widget">
      <div class="stat-widget-icon icon-yellow"><i class="bi bi-hourglass-split"></i></div>
      <div>
        <div class="stat-widget-value"><?= formatAmount($user['balance_pending']) ?></div>
        <div class="stat-widget-label" data-i18n="dashboard_pending">En attente</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-widget">
      <div class="stat-widget-icon icon-purple"><i class="bi bi-graph-up-arrow"></i></div>
      <div>
        <div class="stat-widget-value"><?= formatAmount($user['total_collected']) ?></div>
        <div class="stat-widget-label" data-i18n="dashboard_collected">Total collecté</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-widget">
      <div class="stat-widget-icon icon-red"><i class="bi bi-heart-fill"></i></div>
      <div>
        <div class="stat-widget-value"><?= $totalDonations ?></div>
        <div class="stat-widget-label" data-i18n="dashboard_donations_received">Dons reçus</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Recent campaigns -->
  <div class="col-lg-7">
    <div class="card-kotiza">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 style="font-weight:700;margin:0;">Mes cagnottes récentes</h5>
          <a href="/user/campaigns.php" style="font-size:0.85rem;color:var(--primary);">Voir tout →</a>
        </div>
        <?php if (empty($myCampaigns)): ?>
          <div class="text-center py-4" style="color:var(--text-muted);">
            <i class="bi bi-collection" style="font-size:2.5rem;"></i>
            <p class="mt-2">Aucune cagnotte pour l'instant.</p>
            <a href="/user/create-campaign.php" class="btn-primary-kotiza" style="font-size:0.85rem;">
              <i class="bi bi-plus-circle"></i> Créer ma première cagnotte
            </a>
          </div>
        <?php else: ?>
          <?php foreach ($myCampaigns as $c): ?>
            <div style="padding:1rem 0;border-bottom:1px solid var(--border);" class="d-flex gap-3 align-items-center">
              <div style="width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,rgba(108,99,255,0.2),rgba(67,217,173,0.2));display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
                <?= match($c['category']) { 'Santé'=>'🏥','Éducation'=>'📚','Urgence'=>'🚨','Humanitaire'=>'🤝',default=>'💡' } ?>
              </div>
              <div style="flex:1;min-width:0;">
                <div style="font-weight:600;font-size:0.9rem;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= sanitize($c['title']) ?></div>
                <div class="progress-kotiza mt-1" style="height:5px;">
                  <div class="progress-fill" style="width:<?= progressPercent($c['collected_amount'], $c['goal_amount']) ?>%"></div>
                </div>
                <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;">
                  <?= formatAmount($c['collected_amount']) ?> / <?= formatAmount($c['goal_amount']) ?>
                </div>
              </div>
              <a href="/campaign/view.php?slug=<?= $c['slug'] ?>" class="btn-sm-kotiza btn-primary-sm">Voir</a>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Recent donations & quick actions -->
  <div class="col-lg-5">
    <div class="card-kotiza mb-3">
      <div class="card-body">
        <h5 style="font-weight:700;margin-bottom:1rem;">Dons récents reçus</h5>
        <?php if (empty($recent)): ?>
          <div class="text-center py-3" style="color:var(--text-muted);font-size:0.9rem;">
            <i class="bi bi-heart" style="font-size:2rem;"></i><p class="mt-2">Aucun don reçu.</p>
          </div>
        <?php else: ?>
          <?php foreach ($recent as $d): ?>
            <div class="donor-item">
              <div class="donor-avatar"><?= strtoupper(substr($d['donor_name'], 0, 1)) ?></div>
              <div style="flex:1;">
                <div class="donor-name"><?= sanitize($d['donor_name']) ?></div>
                <div style="font-size:0.78rem;color:var(--text-muted);"><?= sanitize($d['campaign_title']) ?></div>
              </div>
              <div class="text-end">
                <div class="donor-amount"><?= formatAmount($d['amount']) ?></div>
                <div class="donor-time"><?= timeAgo($d['created_at']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="card-kotiza">
      <div class="card-body">
        <h5 style="font-weight:700;margin-bottom:1rem;">Actions rapides</h5>
        <div class="d-flex flex-column gap-2">
          <a href="/user/create-campaign.php" class="btn-sm-kotiza btn-primary-sm w-100 justify-content-center" style="padding:0.6rem;">
            <i class="bi bi-plus-circle"></i> Nouvelle cagnotte
          </a>
          <a href="/user/withdrawal.php" class="btn-sm-kotiza w-100 justify-content-center" style="padding:0.6rem;background:rgba(67,217,173,0.1);color:var(--accent);border:1px solid rgba(67,217,173,0.3);">
            <i class="bi bi-arrow-up-circle"></i> Demande de retrait
          </a>
          <a href="/user/kyc.php" class="btn-sm-kotiza w-100 justify-content-center" style="padding:0.6rem;background:rgba(108,99,255,0.1);color:var(--primary);border:1px solid rgba(108,99,255,0.3);">
            <i class="bi bi-shield-check"></i> Vérification KYC
            <?php if ($kycStatus === 'approved'): ?><i class="bi bi-check-circle-fill ms-1" style="color:var(--accent)"></i><?php endif; ?>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
