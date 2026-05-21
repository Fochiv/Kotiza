<footer class="footer-kotiza">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-brand">
          <img src="/logo.png" alt="Kotiza" height="32" style="border-radius:8px;margin-right:8px;">
          <span style="background:linear-gradient(135deg,#6c63ff,#43d9ad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Kotiza</span>
        </div>
        <p class="footer-desc" data-i18n="footer_desc">Plateforme de crowdfunding sécurisée pour l'Afrique francophone.</p>
        <div class="mt-3">
          <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
          <a href="#" class="social-link"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
          <a href="https://wa.me/237658547295" target="_blank" class="social-link"><i class="bi bi-whatsapp"></i></a>
          <a href="#" class="social-link"><i class="bi bi-tiktok"></i></a>
        </div>
      </div>

      <div class="col-sm-4 col-lg-2">
        <h6 class="footer-heading" data-i18n="footer_links">Liens utiles</h6>
        <a href="/" class="footer-link" data-i18n="nav_home">Accueil</a>
        <a href="/#featured" class="footer-link" data-i18n="nav_campaigns">Cagnottes</a>
        <a href="/#how" class="footer-link" data-i18n="nav_how">Comment ça marche</a>
        <a href="/auth/register.php" class="footer-link" data-i18n="nav_register">Inscription</a>
      </div>

      <div class="col-sm-4 col-lg-3">
        <h6 class="footer-heading">Paiements</h6>
        <p class="footer-desc">MTN Mobile Money<br>Orange Money<br>Wave<br>Moov Money<br>Airtel Money</p>
      </div>

      <div class="col-sm-4 col-lg-3">
        <h6 class="footer-heading" data-i18n="footer_contact">Contact</h6>
        <p class="footer-desc">
          <i class="bi bi-envelope me-2"></i>contact@kotiza.com<br>
          <a href="https://wa.me/237658547295" target="_blank" style="color:inherit;text-decoration:none;">
            <i class="bi bi-whatsapp me-2" style="color:#25d366;"></i>+237 658 547 295
          </a><br>
          <i class="bi bi-geo-alt me-2"></i>Yaoundé, Cameroun
        </p>
      </div>
    </div>

    <div class="footer-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
      <span>&copy; <?= date('Y') ?> Kotiza. <span data-i18n="footer_rights">Tous droits réservés.</span></span>
      <div class="d-flex gap-3">
        <a href="#" class="footer-link" data-i18n="footer_legal">Mentions légales</a>
        <a href="#" class="footer-link" data-i18n="footer_privacy">Confidentialité</a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/i18n.js"></script>
<script src="/assets/js/main.js"></script>
<?= isset($extraScripts) ? $extraScripts : '' ?>
</body>
</html>
