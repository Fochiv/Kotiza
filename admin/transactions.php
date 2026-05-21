<?php
$pageTitle = 'Transactions';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$pdo = getDB();

$filter = sanitize($_GET['filter'] ?? 'all');
$page = max(1,(int)($_GET['page'] ?? 1));
$perPage = 30; $offset = ($page-1)*$perPage;

$where = $filter !== 'all' ? "WHERE d.status='$filter'" : '';
$total = $pdo->query("SELECT COUNT(*) FROM donations d $where")->fetchColumn();
$pages = ceil($total/$perPage);

$stmt = $pdo->query("SELECT d.*, c.title as campaign_title FROM donations d JOIN campaigns c ON d.campaign_id=c.id $where ORDER BY d.created_at DESC LIMIT $perPage OFFSET $offset");
$list = $stmt->fetchAll();

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <div><h1 class="page-title">Transactions</h1><p class="page-subtitle"><?= number_format($total,0,',',' ') ?> transactions</p></div>
  <div class="d-flex gap-2 flex-wrap">
    <?php foreach(['all'=>'Toutes','success'=>'Confirmées','pending'=>'En attente','failed'=>'Échouées'] as $f=>$l): ?>
      <a href="?filter=<?=$f?>" class="btn-sm-kotiza <?=$filter===$f?'btn-primary-sm':''?>" style="<?=$filter!==$f?'background:var(--bg-card);color:var(--text);border:1px solid var(--border);':''?>"><?=$l?></a>
    <?php endforeach; ?>
    <a href="?filter=<?=$filter?>&export=csv" class="btn-sm-kotiza" style="background:rgba(67,217,173,0.1);color:var(--accent);border:1px solid rgba(67,217,173,0.3);">
      <i class="bi bi-download"></i> Exporter CSV
    </a>
  </div>
</div>

<?php
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="transactions_kotiza_'.date('Ymd').'.csv"');
    $out = fopen('php://output','w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($out, ['ID','Date','Donateur','Email','Campagne','Montant','Devise','Opérateur','Pays','Statut','Transaction ID'], ';');
    $all = $pdo->query("SELECT d.*, c.title as campaign_title FROM donations d JOIN campaigns c ON d.campaign_id=c.id $where ORDER BY d.created_at DESC")->fetchAll();
    foreach ($all as $d) {
        fputcsv($out, [$d['id'],date('d/m/Y H:i',strtotime($d['created_at'])),$d['donor_name'],$d['donor_email'],$d['campaign_title'],$d['amount'],$d['currency'],$d['operator'],$d['country_code'],$d['status'],$d['transaction_id']], ';');
    }
    fclose($out); exit;
}
?>

<div class="card-kotiza">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table-kotiza">
        <thead>
          <tr><th>Date</th><th>Donateur</th><th>Cagnotte</th><th>Opérateur</th><th>Pays</th><th class="text-end">Montant</th><th>Statut</th><th>Ref</th></tr>
        </thead>
        <tbody>
          <?php foreach ($list as $d): ?>
          <tr>
            <td style="white-space:nowrap;"><?= date('d/m/Y H:i',strtotime($d['created_at'])) ?></td>
            <td>
              <div style="font-weight:600;"><?= sanitize($d['donor_name']) ?></div>
              <?php if ($d['donor_email']): ?><div style="font-size:0.75rem;color:var(--text-muted);"><?= sanitize($d['donor_email']) ?></div><?php endif; ?>
            </td>
            <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-muted);"><?= sanitize($d['campaign_title']) ?></td>
            <td><?= sanitize($d['operator'] ?? '-') ?></td>
            <td><?= sanitize($d['country_code'] ?? '-') ?></td>
            <td class="text-end" style="font-weight:700;color:<?= $d['status']==='success'?'var(--accent)':'var(--text)' ?>;"><?= formatAmount($d['amount']) ?></td>
            <td>
              <span class="badge-kotiza <?= match($d['status']){'success'=>'badge-success','failed'=>'badge-danger',default=>'badge-warning'} ?>">
                <?= match($d['status']){'success'=>'✓ Confirmé','failed'=>'✗ Échoué',default=>'⏳ En attente'} ?>
              </span>
            </td>
            <td style="font-size:0.72rem;color:var(--text-muted);"><?= sanitize(substr($d['reference'] ?? '-',0,15)) ?>...</td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if ($pages > 1): ?>
<div class="d-flex justify-content-center gap-2 mt-4">
  <?php for($i=1;$i<=$pages;$i++): ?>
    <a href="?page=<?=$i?>&filter=<?=$filter?>" class="btn-sm-kotiza <?=$i===$page?'btn-primary-sm':''?>" style="<?=$i!==$page?'background:var(--bg-card);color:var(--text);border:1px solid var(--border);':''?>"><?=$i?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
