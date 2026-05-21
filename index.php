<?php
$pageTitle = 'Collectons Ensemble';
$pageDesc = 'Créez votre cagnotte, partagez votre cause et recevez des dons via Mobile Money en toute sécurité.';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();
$featured = $pdo->query("SELECT * FROM campaigns WHERE status='completed' ORDER BY collected_amount DESC LIMIT 12")->fetchAll();
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- HERO -->
<section class="hero-section">
  <div class="container position-relative z-1">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <span class="section-badge"><i class="bi bi-rocket-takeoff-fill me-1"></i> Plateforme N°1 en Afrique</span>
        <h1 class="hero-title" data-i18n="hero_title">Collectons Ensemble</h1>
        <p class="hero-subtitle" data-i18n="hero_subtitle">Créez votre cagnotte, partagez votre cause et recevez des dons via Mobile Money en toute sécurité.</p>
        <div class="d-flex flex-wrap gap-3">
          <a href="/auth/register.php" class="btn-primary-kotiza">
            <i class="bi bi-plus-circle-fill"></i>
            <span data-i18n="hero_cta1">Créer ma cagnotte</span>
          </a>
          <a href="#featured" class="btn-outline-kotiza">
            <i class="bi bi-collection-fill"></i>
            <span data-i18n="hero_cta2">Voir les cagnottes</span>
          </a>
        </div>

        <div class="d-flex flex-wrap gap-4 mt-4">
          <div>
            <div style="font-size:1.4rem;font-weight:900;color:var(--primary);">16</div>
            <div style="font-size:0.8rem;color:var(--text-muted);">Pays couverts</div>
          </div>
          <div>
            <div style="font-size:1.4rem;font-weight:900;color:var(--accent);">MTN • Orange • Wave</div>
            <div style="font-size:0.8rem;color:var(--text-muted);">Opérateurs supportés</div>
          </div>
        </div>
      </div>

      <div class="col-lg-6 hero-visual">
        <div class="hero-card">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#6c63ff,#43d9ad);display:flex;align-items:center;justify-content:center;">
              <i class="bi bi-heart-fill" style="font-size:1.4rem;color:#fff;"></i>
            </div>
            <div>
              <div style="font-weight:700;color:var(--text);">Aide médicale</div>
              <div style="font-size:0.8rem;color:var(--text-muted);">Santé · Yaoundé</div>
            </div>
            <span class="badge-kotiza badge-success ms-auto">Actif</span>
          </div>
          <div class="progress-kotiza"><div class="progress-fill" style="width:87%"></div></div>
          <div class="progress-info">
            <span class="progress-amount">435 000 FCFA</span>
            <span>87% · <strong style="color:var(--text);">500 000 FCFA</strong></span>
          </div>
          <div class="d-flex justify-content-between mt-3" style="font-size:0.82rem;color:var(--text-muted);">
            <span><i class="bi bi-people-fill me-1" style="color:var(--primary)"></i>42 donateurs</span>
            <span><i class="bi bi-clock me-1"></i>8 jours restants</span>
          </div>
        </div>

        <div style="margin-top:-20px;margin-left:40px;" class="hero-card">
          <div class="d-flex align-items-center gap-2">
            <div class="donor-avatar" style="width:36px;height:36px;font-size:0.85rem;">M</div>
            <div>
              <div style="font-size:0.85rem;font-weight:600;color:var(--text);">Marie Ngono</div>
              <div style="font-size:0.75rem;color:var(--accent);">+5 000 FCFA via MTN</div>
            </div>
            <i class="bi bi-check-circle-fill ms-auto" style="color:var(--accent)"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<section class="stats-section">
  <div class="container">
    <div class="row g-0">
      <div class="col-6 col-md-3 fade-in">
        <div class="stat-card">
          <span class="stat-number counter" data-target="200" data-suffix="+">0</span>
          <div class="stat-label" data-i18n="stats_campaigns">Cagnottes créées</div>
        </div>
      </div>
      <div class="col-6 col-md-3 fade-in">
        <div class="stat-card">
          <span class="stat-number counter" data-target="5000" data-suffix="+">0</span>
          <div class="stat-label" data-i18n="stats_donors">Donateurs</div>
        </div>
      </div>
      <div class="col-6 col-md-3 fade-in">
        <div class="stat-card">
          <span class="stat-number counter" data-target="98" data-suffix="%">0</span>
          <div class="stat-label" data-i18n="stats_satisfaction">de satisfaction</div>
        </div>
      </div>
      <div class="col-6 col-md-3 fade-in">
        <div class="stat-card">
          <span class="stat-number counter" data-target="15" data-prefix="" data-suffix="M FCFA">0</span>
          <div class="stat-label" data-i18n="stats_collected">FCFA collectés</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="how-section" id="how">
  <div class="container text-center">
    <span class="section-badge">Simple &amp; Rapide</span>
    <h2 class="section-title" data-i18n="how_title">Comment ça marche ?</h2>
    <p class="section-subtitle" data-i18n="how_subtitle">3 étapes pour lancer votre collecte</p>

    <div class="row g-4 mt-2">
      <div class="col-md-4 fade-in">
        <div class="step-card">
          <div class="step-number">1</div>
          <div class="step-icon"><i class="bi bi-pencil-fill"></i></div>
          <h4 class="step-title" data-i18n="how_step1_title">Créez</h4>
          <p class="step-desc" data-i18n="how_step1_desc">Créez votre cagnotte en quelques minutes avec une description et un objectif.</p>
        </div>
      </div>
      <div class="col-md-4 fade-in">
        <div class="step-card">
          <div class="step-number">2</div>
          <div class="step-icon"><i class="bi bi-share-fill"></i></div>
          <h4 class="step-title" data-i18n="how_step2_title">Partagez</h4>
          <p class="step-desc" data-i18n="how_step2_desc">Partagez votre lien unique sur WhatsApp, Facebook, Telegram.</p>
        </div>
      </div>
      <div class="col-md-4 fade-in">
        <div class="step-card">
          <div class="step-number">3</div>
          <div class="step-icon"><i class="bi bi-cash-coin"></i></div>
          <h4 class="step-title" data-i18n="how_step3_title">Collectez</h4>
          <p class="step-desc" data-i18n="how_step3_desc">Recevez les dons via Mobile Money et retirez votre argent facilement.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURED CAMPAIGNS -->
<section class="py-5" id="featured" style="background:var(--bg2);">
  <div class="container">
    <div class="text-center mb-5">
      <span class="section-badge"><i class="bi bi-check-circle-fill me-1"></i> Succès</span>
      <h2 class="section-title" data-i18n="featured_title">Cagnottes terminées</h2>
      <p class="section-subtitle" data-i18n="featured_subtitle">Des projets financés avec succès grâce à votre générosité</p>
    </div>

    <div class="row g-4">
      <?php foreach ($featured as $c):
        $pct = progressPercent($c['collected_amount'], $c['goal_amount']);
        $catIcon = match($c['category']) {
          'Santé'        => 'bi-hospital',
          'Éducation'    => 'bi-book-fill',
          'Urgence'      => 'bi-exclamation-triangle-fill',
          'Humanitaire'  => 'bi-people-fill',
          'Projets'      => 'bi-lightbulb-fill',
          default        => 'bi-star-fill'
        };
      ?>
      <div class="col-sm-6 col-lg-4 fade-in">
        <div class="card-kotiza">
          <?php if (!empty($c['cover_image']) && file_exists(__DIR__ . '/uploads/campaigns/' . $c['cover_image'])): ?>
            <div style="height:200px;overflow:hidden;border-radius:12px 12px 0 0;">
              <img src="/uploads/campaigns/<?= sanitize($c['cover_image']) ?>" alt="<?= sanitize($c['title']) ?>" style="width:100%;height:100%;object-fit:cover;">
            </div>
          <?php else: ?>
            <div class="card-img-placeholder"><i class="bi <?= $catIcon ?>" style="font-size:2.5rem;color:var(--primary);"></i></div>
          <?php endif; ?>
          <div class="card-body">
            <span class="badge-kotiza badge-info mb-2"><?= sanitize($c['category']) ?></span>
            <h5 class="card-title"><?= sanitize($c['title']) ?></h5>
            <p class="card-text mb-2"><?= mb_substr(sanitize($c['description']), 0, 80) ?>...</p>

            <div class="progress-kotiza"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
            <div class="progress-info">
              <span class="progress-amount"><?= formatAmount($c['collected_amount']) ?></span>
              <span><?= $pct ?>%</span>
            </div>
            <div class="d-flex justify-content-between mt-2" style="font-size:0.8rem;color:var(--text-muted);">
              <span><i class="bi bi-people me-1"></i><?= $c['donors_count'] ?> dons</span>
              <span class="badge-kotiza badge-success">Terminée</span>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-5">
      <a href="/auth/register.php" class="btn-primary-kotiza">
        <i class="bi bi-plus-circle"></i> Créer ma propre cagnotte
      </a>
    </div>
  </div>
</section>

<!-- CATEGORIES -->
<section class="categories-section" id="categories">
  <div class="container">
    <div class="text-center mb-5">
      <span class="section-badge"><i class="bi bi-grid-fill me-1"></i> Catégories</span>
      <h2 class="section-title" data-i18n="categories_title">Explorez par catégorie</h2>
    </div>
    <div class="row g-3">
      <?php
      $cats = [
        ['Santé',        'bi-hospital',                  'cat_health'],
        ['Éducation',    'bi-book-fill',                 'cat_education'],
        ['Urgence',      'bi-exclamation-triangle-fill', 'cat_emergency'],
        ['Projets',      'bi-lightbulb-fill',            'cat_projects'],
        ['Humanitaire',  'bi-people-fill',               'cat_humanitarian'],
        ['Religion',     'bi-moon-stars-fill',           'cat_religion'],
        ['Sports',       'bi-trophy-fill',               'cat_sports'],
        ['Arts & Culture','bi-palette-fill',             'cat_arts'],
      ];
      foreach ($cats as [$name, $icon, $key]):
      ?>
      <div class="col-6 col-sm-4 col-md-3 col-lg-3 fade-in">
        <a href="/auth/register.php" class="category-card">
          <span class="category-icon"><i class="bi <?= $icon ?>"></i></span>
          <span class="category-name" data-i18n="<?= $key ?>"><?= $name ?></span>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="testimonials-section" id="testimonials">
  <div class="container">
    <div class="text-center mb-5">
      <span class="section-badge"><i class="bi bi-chat-quote-fill me-1"></i> Témoignages</span>
      <h2 class="section-title" data-i18n="testimonials_title">Ce qu'ils disent de nous</h2>
    </div>

    <div style="position:relative;min-height:200px;">
      <?php
      $testimonials = [
        ['name'=>'Amina Diallo','role'=>'Créatrice de cagnotte · Dakar','text'=>'Grâce à Kotiza, j\'ai pu financer l\'opération de ma fille en moins de 48h. La plateforme est simple, rapide et les paiements via Orange Money sont instantanés !','avatar'=>'A'],
        ['name'=>'Jean-Baptiste Mvogo','role'=>'Donateur · Douala','text'=>'J\'ai fait un don en 2 minutes via MTN Mobile Money. C\'est incroyablement facile et on reçoit une confirmation immédiate. Je recommande vivement !','avatar'=>'J'],
        ['name'=>'Fatou Koné','role'=>'Organisatrice · Abidjan','text'=>'Notre association a collecté 800 000 FCFA en une semaine pour les inondés. L\'équipe Kotiza nous a aidés à chaque étape. Merci !','avatar'=>'F'],
        ['name'=>'Paul Nkemdirim','role'=>'Entrepreneur · Lagos','text'=>'La procédure KYC est rapide et rassurante. J\'ai pu retirer mes fonds en toute transparence. Une plateforme de confiance !','avatar'=>'P'],
        ['name'=>'Sylvie Ahouandjinou','role'=>'Enseignante · Cotonou','text'=>'J\'ai lancé une cagnotte pour mon école et les dons ont afflué de partout. Les partages WhatsApp ont vraiment amplifié la collecte !','avatar'=>'S'],
      ];
      foreach ($testimonials as $i => $t):
      ?>
      <div class="testimonial-slide <?= $i === 0 ? 'active' : '' ?>">
        <div class="testimonial-card">
          <div class="stars">★★★★★</div>
          <p class="testimonial-text">"<?= sanitize($t['text']) ?>"</p>
          <div class="d-flex align-items-center gap-3">
            <div class="testimonial-avatar"><?= $t['avatar'] ?></div>
            <div>
              <div class="testimonial-name"><?= sanitize($t['name']) ?></div>
              <div class="testimonial-role"><?= sanitize($t['role']) ?></div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
      <?php for ($i = 0; $i < count($testimonials); $i++): ?>
        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--border);margin:0 3px;" id="dot-<?= $i ?>"></span>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section style="background:linear-gradient(135deg,var(--bg2),rgba(108,99,255,0.1));padding:80px 0;border-top:1px solid var(--border);">
  <div class="container text-center">
    <h2 class="section-title">Prêt à collecter ?</h2>
    <p class="section-subtitle">Rejoignez des milliers de personnes qui font confiance à Kotiza</p>
    <a href="/auth/register.php" class="btn-primary-kotiza" style="font-size:1.1rem;padding:1rem 2.5rem;">
      <i class="bi bi-rocket-takeoff-fill"></i> Commencer gratuitement
    </a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
