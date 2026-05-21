<?php
$pageTitle = 'Tableau de bord';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';
requireAdmin();
$pdo = getDB();
$stats = getCampaignStats();

$recentDonations = $pdo->query("SELECT d.*, c.title as campaign_title, u.full_name as owner_name FROM donations d JOIN campaigns c ON d.campaign_id=c.id LEFT JOIN users u ON c.user_id=u.id WHERE d.status='success' ORDER BY d.created_at DESC LIMIT 8")->fetchAll();
$pendingWithdrawals = $pdo->query("SELECT COUNT(*) FROM withdrawals WHERE status='pending'")->fetchColumn();
$pendingKyc = $pdo->query("SELECT COUNT(*) FROM kyc WHERE status='pending'")->fetchColumn();

$monthDonations = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE status='success' AND strftime('%Y-%m',created_at)=strftime('%Y-%m','now')")->fetchColumn();
$monthUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user' AND strftime('%Y-%m',created_at)=strftime('%Y-%m','now')")->fetchColumn();

// Chart data - last 7 days
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $amt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM donations WHERE status='success' AND date(created_at)=?");
    $amt->execute([$date]);
    $chartData[] = ['date' => date('d/m', strtotime($date)), 'amount' => (float)$amt->fetchColumn()];
}

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <div class="stat-widget">
      <div class="stat-widget-icon icon-purple"><i class="bi bi-graph-up-arrow"></i></div>
      <div>
        <div class="stat-widget-value"><?= formatAmount($stats['total_donations']) ?></div>
        <div class="stat-widget-label">Total collecté</div>
        <div style="font-size:0.75rem;color:var(--accent);">+<?= formatAmount($monthDonations) ?> ce mois</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="stat-widget">
      <div class="stat-widget-icon icon-green"><i class="bi bi-people"></i></div>
      <div>
        <div class="stat-widget-value"><?= $stats['total_users'] ?></div>
        <div class="stat-widget-label">Utilisateurs</div>
        <div style="font-size:0.75rem;color:var(--accent);">+<?= $monthUsers ?> ce mois</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="stat-widget">
      <div class="stat-widget-icon icon-yellow"><i class="bi bi-collection"></i></div>
      <div>
        <div class="stat-widget-value"><?= $stats['total_campaigns'] ?></div>
        <div class="stat-widget-label">Cagnottes totales</div>
        <div style="font-size:0.75rem;color:var(--accent);"><?= $stats['active_campaigns'] ?> actives</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="stat-widget">
      <div class="stat-widget-icon icon-red"><i class="bi bi-coin"></i></div>
      <div>
        <div class="stat-widget-value"><?= formatAmount($stats['commissions']) ?></div>
        <div class="stat-widget-label">Commissions gagnées</div>
      </div>
    </div>
  </div>
</div>

<!-- Alerts -->
<div class="row g-3 mb-4">
  <?php if ($pendingWithdrawals > 0): ?>
  <div class="col-md-6">
    <div class="card-kotiza" style="border-color:var(--warning);">
      <div class="card-body d-flex align-items-center justify-content-between">
        <div>
          <div style="font-weight:700;color:var(--warning);">⏳ <?= $pendingWithdrawals ?> retrait(s) en attente</div>
          <div style="font-size:0.82rem;color:var(--text-muted);">À traiter manuellement</div>
        </div>
        <a href="/admin/withdrawals.php" class="btn-sm-kotiza btn-primary-sm">Traiter →</a>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <?php if ($pendingKyc > 0): ?>
  <div class="col-md-6">
    <div class="card-kotiza" style="border-color:var(--primary);">
      <div class="card-body d-flex align-items-center justify-content-between">
        <div>
          <div style="font-weight:700;color:var(--primary);">🔍 <?= $pendingKyc ?> KYC en attente</div>
          <div style="font-size:0.82rem;color:var(--text-muted);">Documents à vérifier</div>
        </div>
        <a href="/admin/kyc.php" class="btn-sm-kotiza btn-primary-sm">Vérifier →</a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Chart + Recent donations -->
<div class="row g-4">
  <div class="col-lg-7">
    <div class="card-kotiza">
      <div class="card-body">
        <h5 style="font-weight:700;margin-bottom:1.5rem;">Dons — 7 derniers jours</h5>
        <canvas id="donations-chart" height="200"></canvas>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card-kotiza">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 style="font-weight:700;margin:0;">Dons récents</h5>
          <a href="/admin/transactions.php" style="font-size:0.82rem;color:var(--primary);">Voir tout →</a>
        </div>
        <?php foreach ($recentDonations as $d): ?>
        <div class="donor-item">
          <div class="donor-avatar"><?= strtoupper(substr($d['donor_name'], 0, 1)) ?></div>
          <div style="flex:1;min-width:0;">
            <div class="donor-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= sanitize($d['donor_name']) ?></div>
            <div style="font-size:0.75rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= sanitize($d['campaign_title']) ?></div>
          </div>
          <div class="text-end">
            <div class="donor-amount"><?= formatAmount($d['amount']) ?></div>
            <div class="donor-time"><?= timeAgo($d['created_at']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php
$extraScripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById("donations-chart");
new Chart(ctx, {
  type: "bar",
  data: {
    labels: ' . json_encode(array_column($chartData, 'date')) . ',
    datasets: [{
      label: "Dons (FCFA)",
      data: ' . json_encode(array_column($chartData, 'amount')) . ',
      backgroundColor: "rgba(108,99,255,0.3)",
      borderColor: "#6c63ff",
      borderWidth: 2,
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: "rgba(255,255,255,0.05)" }, ticks: { color: "#a7a9be" } },
      y: { grid: { color: "rgba(255,255,255,0.05)" }, ticks: { color: "#a7a9be", callback: v => v.toLocaleString("fr-FR") + " F" } }
    }
  }
});
</script>';
require_once __DIR__ . '/../includes/admin_footer.php';
?>
