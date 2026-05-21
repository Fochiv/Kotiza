<?php
$pageTitle = 'Cagnotte';
require_once __DIR__ . '/../includes/functions.php';
$pdo = getDB();

$slug = sanitize($_GET['slug'] ?? '');
if (empty($slug)) { header('Location: /'); exit; }

$stmt = $pdo->prepare("SELECT c.*, u.full_name as owner_name, u.phone as owner_phone FROM campaigns c JOIN users u ON c.user_id=u.id WHERE c.slug=?");
$stmt->execute([$slug]);
$campaign = $stmt->fetch();
if (!$campaign) { header('Location: /'); exit; }

$pct = progressPercent($campaign['collected_amount'], $campaign['goal_amount']);

$donorsStmt = $pdo->prepare("SELECT donor_name, amount, created_at FROM donations WHERE campaign_id=? AND status='success' ORDER BY created_at DESC LIMIT 10");
$donorsStmt->execute([$campaign['id']]);
$donors = $donorsStmt->fetchAll();

$shareUrl = APP_URL . '/campaign/view.php?slug=' . $campaign['slug'];
$shareText = 'Soutenez "' . $campaign['title'] . '" sur Kotiza : ' . $shareUrl;

$pageTitle = $campaign['title'];
$pageDesc = mb_substr($campaign['description'], 0, 160);

// Fetch countries for donation form
$countriesJson = '[]';
$ch = curl_init(ASHTECH_BASE_URL . '/v1/countries');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . ASHTECH_API_KEY], CURLOPT_SSL_VERIFYPEER => false]);
$body = curl_exec($ch);
curl_close($ch);
if ($body) $countriesJson = $body;
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div style="padding-top:70px;">
  <div class="container py-5">
    <div class="row g-4">
      <!-- Left: Campaign info -->
      <div class="col-lg-8">
        <!-- Cover image -->
        <?php if ($campaign['cover_image']): ?>
          <img src="/uploads/campaigns/<?= $campaign['cover_image'] ?>" alt="<?= sanitize($campaign['title']) ?>" class="campaign-cover mb-4">
        <?php else: ?>
          <div class="campaign-cover-placeholder mb-4">
            <?= match($campaign['category']) { 'Santé'=>'🏥','Éducation'=>'📚','Urgence'=>'🚨','Humanitaire'=>'🤝',default=>'💡' } ?>
          </div>
        <?php endif; ?>

        <!-- Title & info -->
        <div class="mb-4">
          <span class="badge-kotiza badge-info mb-2"><?= sanitize($campaign['category']) ?></span>
          <h1 style="font-size:2rem;font-weight:800;color:var(--text);margin-bottom:0.75rem;"><?= sanitize($campaign['title']) ?></h1>

          <div class="d-flex flex-wrap gap-3 align-items-center" style="color:var(--text-muted);font-size:0.88rem;">
            <span><i class="bi bi-person-circle me-1"></i>Par <strong><?= sanitize($campaign['owner_name']) ?></strong></span>
            <span><i class="bi bi-calendar me-1"></i><?= date('d/m/Y', strtotime($campaign['created_at'])) ?></span>
            <span><i class="bi bi-people me-1"></i><?= $campaign['donors_count'] ?> donateurs</span>
          </div>
        </div>

        <!-- Progress -->
        <div class="card-kotiza mb-4" style="padding:1.5rem;">
          <div class="d-flex flex-wrap justify-content-between align-items-end mb-2">
            <div>
              <span style="font-size:2rem;font-weight:900;color:var(--accent);"><?= formatAmount($campaign['collected_amount']) ?></span>
              <span style="color:var(--text-muted);font-size:0.9rem;"> collectés sur <?= formatAmount($campaign['goal_amount']) ?></span>
            </div>
            <span style="font-size:1.5rem;font-weight:800;color:var(--primary);"><?= $pct ?>%</span>
          </div>
          <div class="progress-kotiza" style="height:12px;">
            <div class="progress-fill" style="width:<?= $pct ?>%"></div>
          </div>
          <div class="d-flex justify-content-between mt-2" style="font-size:0.85rem;color:var(--text-muted);">
            <span><i class="bi bi-people me-1"></i><?= $campaign['donors_count'] ?> donateurs</span>
            <span><?= $pct >= 100 ? '🎉 Objectif atteint !' : ($campaign['goal_amount'] - $campaign['collected_amount'] > 0 ? formatAmount($campaign['goal_amount'] - $campaign['collected_amount']) . ' restants' : '') ?></span>
          </div>
        </div>

        <!-- Description -->
        <div class="card-kotiza mb-4">
          <div class="card-body">
            <h4 style="font-weight:700;margin-bottom:1rem;">À propos de cette collecte</h4>
            <div style="color:var(--text-muted);line-height:1.9;white-space:pre-wrap;"><?= nl2br(sanitize($campaign['description'])) ?></div>
          </div>
        </div>

        <!-- Share -->
        <div class="card-kotiza mb-4">
          <div class="card-body">
            <h5 style="font-weight:700;margin-bottom:0.75rem;">📢 Partagez cette cagnotte</h5>
            <p style="color:var(--text-muted);font-size:0.88rem;">Plus vous partagez, plus vous aidez !</p>
            <div class="share-buttons">
              <button class="share-btn whatsapp" onclick="shareWhatsApp('<?= $shareUrl ?>','<?= addslashes($campaign['title']) ?>')">
                <i class="bi bi-whatsapp"></i> WhatsApp
              </button>
              <button class="share-btn facebook" onclick="shareFacebook('<?= $shareUrl ?>')">
                <i class="bi bi-facebook"></i> Facebook
              </button>
              <button class="share-btn telegram" onclick="shareTelegram('<?= $shareUrl ?>','<?= addslashes($campaign['title']) ?>')">
                <i class="bi bi-telegram"></i> Telegram
              </button>
              <button class="share-btn tiktok" onclick="shareTikTok('<?= $shareUrl ?>')">
                <i class="bi bi-tiktok"></i> TikTok
              </button>
              <button class="share-btn" style="background:var(--bg3);border:1px solid var(--border);color:var(--text);" onclick="copyToClipboard('<?= $shareUrl ?>')">
                <i class="bi bi-link-45deg"></i> Copier le lien
              </button>
            </div>
          </div>
        </div>

        <!-- Donors list -->
        <div class="card-kotiza">
          <div class="card-body">
            <h5 style="font-weight:700;margin-bottom:1rem;">❤️ Derniers dons</h5>
            <?php if (empty($donors)): ?>
              <p style="color:var(--text-muted);text-align:center;padding:1.5rem 0;">Soyez le premier à faire un don !</p>
            <?php else: ?>
              <?php foreach ($donors as $d): ?>
                <div class="donor-item">
                  <div class="donor-avatar"><?= strtoupper(substr($d['donor_name'], 0, 1)) ?></div>
                  <div style="flex:1;">
                    <div class="donor-name"><?= sanitize($d['donor_name']) ?></div>
                    <div class="donor-time"><?= timeAgo($d['created_at']) ?></div>
                  </div>
                  <div class="donor-amount"><?= formatAmount($d['amount']) ?></div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Right: Donation card -->
      <div class="col-lg-4">
        <div class="donation-card" id="donation-card">
          <h4 style="font-weight:800;margin-bottom:1.5rem;">💝 Faire un don</h4>

          <!-- Step 1: Amount & info -->
          <div id="step-1">
            <form id="donation-form" novalidate>
              <input type="hidden" name="campaign_id" value="<?= $campaign['id'] ?>">
              <input type="hidden" name="campaign_slug" value="<?= $campaign['slug'] ?>">

              <div class="mb-3">
                <label class="form-label-kotiza">Montant (FCFA) *</label>
                <div class="input-group-kotiza">
                  <i class="bi bi-currency-exchange input-icon"></i>
                  <input type="number" id="donate-amount" class="form-control-kotiza form-control" placeholder="Min. 100 FCFA" min="100" required>
                </div>
                <!-- Quick amounts -->
                <div class="d-flex flex-wrap gap-2 mt-2">
                  <?php foreach ([500,1000,2000,5000,10000] as $amt): ?>
                    <button type="button" class="quick-amount-btn btn-sm-kotiza" style="background:var(--bg3);color:var(--text-muted);border:1px solid var(--border);" data-amount="<?= $amt ?>">
                      <?= number_format($amt, 0, ',', ' ') ?>
                    </button>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label-kotiza mb-0">Votre nom (optionnel)</label>
                  <label style="font-size:0.8rem;cursor:pointer;color:var(--text-muted);">
                    <input type="checkbox" id="anon-check" onchange="toggleAnon()" style="margin-right:4px;">Anonyme
                  </label>
                </div>
                <input type="text" id="donor-name" class="form-control-kotiza form-control" placeholder="Jean Dupont">
              </div>

              <div class="mb-3">
                <label class="form-label-kotiza">Email (optionnel)</label>
                <input type="email" id="donor-email" class="form-control-kotiza form-control" placeholder="jean@email.com">
              </div>

              <div class="mb-3">
                <label class="form-label-kotiza">Pays *</label>
                <select id="country-select" class="form-control-kotiza form-control" onchange="loadOperators()" required>
                  <option value="">-- Sélectionner un pays --</option>
                </select>
              </div>

              <div id="operator-group" class="mb-3" style="display:none;">
                <label class="form-label-kotiza">Opérateur *</label>
                <select id="operator-select" class="form-control-kotiza form-control" onchange="checkOperatorFlow()" required>
                  <option value="">-- Choisir l'opérateur --</option>
                </select>
              </div>

              <div id="phone-group" class="mb-3" style="display:none;">
                <label class="form-label-kotiza">Numéro de téléphone *</label>
                <div class="input-group-kotiza">
                  <i class="bi bi-phone input-icon"></i>
                  <input type="tel" id="donor-phone" class="form-control-kotiza form-control" placeholder="+237 6XX XXX XXX">
                </div>
              </div>

              <!-- Card payment option -->
              <div id="card-payment-info" class="mb-3" style="display:none;">
                <div class="p-3 rounded-3" style="background:rgba(108,99,255,0.08);border:1px solid rgba(108,99,255,0.2);">
                  <i class="bi bi-credit-card me-2" style="color:var(--primary)"></i>
                  <strong>Paiement par carte</strong>
                  <p style="font-size:0.82rem;color:var(--text-muted);margin-top:6px;margin-bottom:8px;">Contactez directement le créateur de la cagnotte via WhatsApp pour payer par carte (Euro/Dollar).</p>
                  <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $campaign['owner_phone']) ?>?text=<?= urlencode('Bonjour, je souhaite faire un don par carte pour la cagnotte "' . $campaign['title'] . '"') ?>"
                     target="_blank" class="share-btn whatsapp" style="font-size:0.82rem;padding:6px 12px;">
                    <i class="bi bi-whatsapp"></i> Contacter via WhatsApp
                  </a>
                </div>
              </div>

              <!-- Confirmation summary -->
              <div id="summary-box" class="mb-3" style="display:none;">
                <div class="p-3 rounded-3" style="background:var(--bg2);border:1px solid var(--border);">
                  <div style="font-weight:700;margin-bottom:6px;">Résumé</div>
                  <div class="d-flex justify-content-between"><span style="color:var(--text-muted);">Montant :</span><span id="sum-amount" style="font-weight:700;color:var(--accent);"></span></div>
                  <div class="d-flex justify-content-between"><span style="color:var(--text-muted);">Opérateur :</span><span id="sum-operator"></span></div>
                  <div class="d-flex justify-content-between"><span style="color:var(--text-muted);">Téléphone :</span><span id="sum-phone"></span></div>
                </div>
              </div>

              <button type="submit" id="donate-btn" class="btn-primary-kotiza w-100 justify-content-center">
                <i class="bi bi-heart-fill"></i> Confirmer le don
              </button>
            </form>
          </div>

          <!-- Step 2: OTP -->
          <div id="step-otp" class="otp-input-section" style="display:none;">
            <div class="text-center mb-3">
              <i class="bi bi-phone-vibrate" style="font-size:3rem;color:var(--primary);"></i>
              <h5 style="margin-top:0.75rem;font-weight:700;">Code OTP requis</h5>
              <p style="color:var(--text-muted);font-size:0.88rem;" id="otp-message"></p>
            </div>
            <div class="mb-3">
              <label class="form-label-kotiza">Code OTP</label>
              <input type="text" id="otp-input" class="form-control-kotiza form-control text-center" placeholder="123456" maxlength="8" style="font-size:1.3rem;letter-spacing:4px;font-weight:700;">
            </div>
            <button type="button" id="otp-submit-btn" class="btn-primary-kotiza w-100 justify-content-center">
              Valider le paiement
            </button>
          </div>

          <!-- Step 3: Wave link -->
          <div id="step-wave" class="wave-link-section" style="display:none;">
            <div class="text-center py-2">
              <div style="font-size:3rem;">🌊</div>
              <h5 style="font-weight:700;margin-top:0.75rem;">Payer avec Wave</h5>
              <p style="color:var(--text-muted);font-size:0.88rem;">Cliquez ci-dessous pour ouvrir Wave et confirmer le paiement.</p>
              <a id="wave-link" href="#" target="_blank" class="btn-primary-kotiza w-100 justify-content-center mb-3">
                <i class="bi bi-arrow-up-right-circle"></i> Ouvrir Wave
              </a>
              <p style="font-size:0.82rem;color:var(--text-muted);">Après avoir payé, attendez la confirmation automatique.</p>
            </div>
          </div>

          <!-- Step 4: Pending timer -->
          <div id="step-pending" style="display:none;">
            <div class="text-center py-2">
              <i class="bi bi-phone-vibrate" style="font-size:3rem;color:var(--warning);"></i>
              <h5 style="font-weight:700;margin-top:0.75rem;">Validez sur votre téléphone</h5>
              <p style="color:var(--text-muted);font-size:0.88rem;">Vous avez reçu une demande sur votre téléphone. Validez-la dans :</p>
              <div class="payment-timer-display" id="payment-timer">08:00</div>
              <p style="color:var(--text-muted);font-size:0.82rem;margin-top:0.5rem;">Recherche automatique du paiement...</p>
              <div class="spinner-border text-primary mt-2" style="width:1.5rem;height:1.5rem;"></div>
            </div>
          </div>

          <!-- Success -->
          <div id="step-success" style="display:none;">
            <div class="text-center py-3">
              <div style="font-size:4rem;">🎉</div>
              <h5 style="font-weight:800;margin-top:1rem;color:var(--accent);">Merci pour votre don !</h5>
              <p style="color:var(--text-muted);">Votre paiement a été confirmé avec succès.</p>
              <button onclick="location.reload()" class="btn-primary-kotiza mt-2 justify-content-center">Retour à la cagnotte</button>
            </div>
          </div>

          <!-- Failed -->
          <div id="step-failed" style="display:none;">
            <div class="text-center py-3">
              <div style="font-size:4rem;">😔</div>
              <h5 style="font-weight:800;margin-top:1rem;color:var(--secondary);">Paiement échoué</h5>
              <p style="color:var(--text-muted);">Le paiement n'a pas pu être confirmé.</p>
              <button onclick="resetPayment()" class="btn-primary-kotiza mt-2 justify-content-center">Réessayer</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$extraScripts = '<script>
const COUNTRIES_DATA = ' . $countriesJson . ';
let currentTransactionId = null;
let currentReference = null;
let pollingInterval = null;
let pendingPayload = null;

// Populate countries
const countrySelect = document.getElementById("country-select");
COUNTRIES_DATA.forEach(c => {
  const opt = document.createElement("option");
  opt.value = c.code;
  opt.textContent = c.name + " (" + c.currency + ")";
  opt.dataset.currency = c.currency;
  opt.dataset.operators = JSON.stringify(c.operators);
  countrySelect.appendChild(opt);
});

function loadOperators() {
  const opt = countrySelect.options[countrySelect.selectedIndex];
  const opGroup = document.getElementById("operator-group");
  const opSelect = document.getElementById("operator-select");
  const phoneGroup = document.getElementById("phone-group");
  const cardInfo = document.getElementById("card-payment-info");
  
  cardInfo.style.display = "none";
  opGroup.style.display = "none";
  phoneGroup.style.display = "none";
  
  if (!opt.value) return;
  
  opSelect.innerHTML = "<option value=\"\">-- Choisir l\'opérateur --</option>";
  const operators = JSON.parse(opt.dataset.operators || "[]");
  operators.forEach(o => {
    const el = document.createElement("option");
    el.value = o; el.textContent = o;
    opSelect.appendChild(el);
  });
  opGroup.style.display = "block";
  phoneGroup.style.display = "block";
}

function checkOperatorFlow() {
  const op = document.getElementById("operator-select").value;
  const phoneGroup = document.getElementById("phone-group");
  const cardInfo = document.getElementById("card-payment-info");
  
  if (op === "Card") {
    cardInfo.style.display = "block";
    phoneGroup.style.display = "none";
  } else {
    phoneGroup.style.display = op ? "block" : "none";
    cardInfo.style.display = "none";
  }
  updateSummary();
}

function toggleAnon() {
  const chk = document.getElementById("anon-check");
  const nameInput = document.getElementById("donor-name");
  nameInput.disabled = chk.checked;
  if (chk.checked) nameInput.value = "Anonyme";
  else nameInput.value = "";
}

function updateSummary() {
  const amount = document.getElementById("donate-amount").value;
  const op = document.getElementById("operator-select").value;
  const phone = document.getElementById("donor-phone").value;
  const sumBox = document.getElementById("summary-box");
  
  if (amount && op) {
    document.getElementById("sum-amount").textContent = parseFloat(amount).toLocaleString("fr-FR") + " FCFA";
    document.getElementById("sum-operator").textContent = op;
    document.getElementById("sum-phone").textContent = phone || "-";
    sumBox.style.display = "block";
  } else {
    sumBox.style.display = "none";
  }
}

document.getElementById("donate-amount")?.addEventListener("input", updateSummary);
document.getElementById("donor-phone")?.addEventListener("input", updateSummary);

// Quick amount buttons
document.querySelectorAll(".quick-amount-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    document.getElementById("donate-amount").value = btn.dataset.amount;
    updateSummary();
    document.querySelectorAll(".quick-amount-btn").forEach(b => b.style.color = "var(--text-muted)");
    btn.style.color = "var(--primary)";
    btn.style.borderColor = "var(--primary)";
  });
});

function showStep(step) {
  ["step-1","step-otp","step-wave","step-pending","step-success","step-failed"].forEach(id => {
    document.getElementById(id).style.display = "none";
  });
  document.getElementById(step).style.display = "block";
}

function resetPayment() {
  clearInterval(pollingInterval);
  currentTransactionId = null;
  currentReference = null;
  pendingPayload = null;
  showStep("step-1");
}

document.getElementById("donation-form")?.addEventListener("submit", async (e) => {
  e.preventDefault();
  const btn = document.getElementById("donate-btn");
  const amount = parseFloat(document.getElementById("donate-amount").value);
  const op = document.getElementById("operator-select").value;
  const countryOpt = document.getElementById("country-select");
  const country = countryOpt.value;
  const currency = countryOpt.options[countryOpt.selectedIndex]?.dataset.currency || "XAF";
  const phone = document.getElementById("donor-phone").value;
  const donorName = document.getElementById("anon-check").checked ? "Anonyme" : (document.getElementById("donor-name").value || "Anonyme");
  const donorEmail = document.getElementById("donor-email").value;

  if (!amount || amount < 100) { showToast("Montant minimum : 100 FCFA", "error"); return; }
  if (!country) { showToast("Sélectionnez un pays", "error"); return; }
  if (!op) { showToast("Sélectionnez un opérateur", "error"); return; }
  if (!phone && op !== "Wave") { showToast("Numéro de téléphone requis", "error"); return; }

  btn.disabled = true;
  btn.innerHTML = "<span class=\"spinner-border spinner-border-sm\"></span> Traitement...";

  const payload = {
    campaign_id: <?= $campaign['id'] ?>,
    campaign_slug: "<?= $campaign['slug'] ?>",
    amount, currency, phone, operator: op, country_code: country,
    donor_name: donorName, donor_email: donorEmail
  };

  try {
    const res = await fetch("/api/payment.php", {
      method: "POST", headers: {"Content-Type":"application/json"},
      body: JSON.stringify(payload)
    });
    const data = await res.json();

    btn.disabled = false;
    btn.innerHTML = "<i class=\"bi bi-heart-fill\"></i> Confirmer le don";

    if (data.flow === "wave" && data.wave_url) {
      document.getElementById("wave-link").href = data.wave_url;
      showStep("step-wave");
      currentTransactionId = data.transaction_id;
      currentReference = data.reference;
      startPolling();
    } else if (data.otp_required) {
      pendingPayload = payload;
      const msgEl = document.getElementById("otp-message");
      if (data.ussd_code) {
        msgEl.innerHTML = "Composez <strong>" + data.ussd_code + "</strong> sur votre téléphone pour obtenir votre OTP.";
      } else {
        msgEl.textContent = "Saisissez le code OTP reçu par SMS.";
      }
      showStep("step-otp");
    } else if (data.transaction_id) {
      currentTransactionId = data.transaction_id;
      currentReference = data.reference;
      showStep("step-pending");
      startPaymentTimer(480, () => {
        clearInterval(pollingInterval);
        updateDonationStatus(currentReference, "failed");
        showStep("step-failed");
      });
      startPolling();
    } else {
      showToast(data.error || "Erreur de paiement. Réessayez.", "error");
    }
  } catch(err) {
    btn.disabled = false;
    btn.innerHTML = "<i class=\"bi bi-heart-fill\"></i> Confirmer le don";
    showToast("Erreur réseau. Vérifiez votre connexion.", "error");
  }
});

// OTP submit
document.getElementById("otp-submit-btn")?.addEventListener("click", async () => {
  const otp = document.getElementById("otp-input").value.trim();
  if (!otp) { showToast("Saisissez le code OTP", "error"); return; }
  if (!pendingPayload) return;

  const btn = document.getElementById("otp-submit-btn");
  btn.disabled = true;
  btn.innerHTML = "<span class=\"spinner-border spinner-border-sm\"></span> Validation...";

  try {
    const res = await fetch("/api/payment.php", {
      method: "POST", headers: {"Content-Type":"application/json"},
      body: JSON.stringify({...pendingPayload, otp})
    });
    const data = await res.json();
    btn.disabled = false;
    btn.innerHTML = "Valider le paiement";

    if (data.transaction_id) {
      currentTransactionId = data.transaction_id;
      currentReference = data.reference;
      showStep("step-pending");
      startPaymentTimer(480, () => { clearInterval(pollingInterval); showStep("step-failed"); });
      startPolling();
    } else {
      showToast(data.error || "OTP invalide.", "error");
    }
  } catch(e) {
    btn.disabled = false;
    btn.innerHTML = "Valider le paiement";
    showToast("Erreur réseau.", "error");
  }
});

function startPolling() {
  clearInterval(pollingInterval);
  pollingInterval = setInterval(async () => {
    if (!currentTransactionId || !currentReference) return;
    try {
      const res = await fetch("/api/poll-status.php?transaction_id=" + encodeURIComponent(currentTransactionId) + "&reference=" + encodeURIComponent(currentReference));
      const data = await res.json();
      if (data.status === "success" || data.status === "completed") {
        clearInterval(pollingInterval);
        showStep("step-success");
      } else if (data.status === "failed") {
        clearInterval(pollingInterval);
        showStep("step-failed");
      }
    } catch(e) {}
  }, 3000);
}

async function updateDonationStatus(reference, status) {
  try {
    await fetch("/api/poll-status.php?reference=" + encodeURIComponent(reference) + "&force_status=" + status);
  } catch(e) {}
}
</script>';
?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
