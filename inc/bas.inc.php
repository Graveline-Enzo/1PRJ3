<?php ?>

<?php if (basename($_SERVER['PHP_SELF']) !== 'connexion.php'): ?>

<footer id="contact" class="bg-dark text-white pt-5 border-top border-warning border-3">
  <div class="container">
    <div class="row g-5 pb-4">

      <div class="col-12 col-md-5">
        <div class="d-flex align-items-center gap-2 mb-3">
          <i class="bi bi-scissors text-warning fs-3"></i>
          <span class="fw-bold fs-5 text-white"><?= propre(SALON_NOM) ?></span>
        </div>
        <p class="text-secondary small lh-lg">
          Depuis plus de 15 ans, nous sublimions votre beauté naturelle avec passion et expertise.
        </p>
        <div class="d-flex gap-2 mt-3">
          <a href="https://instagram.com" target="_blank" aria-label="Instagram"
             class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center footer-social">
            <i class="bi bi-instagram"></i>
          </a>
          <a href="https://facebook.com" target="_blank" aria-label="Facebook"
             class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center footer-social">
            <i class="bi bi-facebook"></i>
          </a>
          <a href="https://tiktok.com" target="_blank" aria-label="TikTok"
             class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center footer-social">
            <i class="bi bi-tiktok"></i>
          </a>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <h6 class="text-warning text-uppercase fw-bold small letter-spacing mb-3">Navigation</h6>
        <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
          <li><a href="index.php#accueil"  class="text-secondary text-decoration-none small footer-link"><i class="bi bi-house me-2"></i>Accueil</a></li>
          <li><a href="index.php#services" class="text-secondary text-decoration-none small footer-link"><i class="bi bi-scissors me-2"></i>Nos services</a></li>
          <li><a href="index.php#horaires" class="text-secondary text-decoration-none small footer-link"><i class="bi bi-clock me-2"></i>Horaires</a></li>
          <li><a href="reservation.php"    class="text-secondary text-decoration-none small footer-link"><i class="bi bi-calendar-check me-2"></i>Réservation</a></li>
        </ul>
      </div>

      <div class="col-6 col-md-4">
        <h6 class="text-warning text-uppercase fw-bold small letter-spacing mb-3">Contact</h6>
        <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
          <li class="d-flex align-items-start gap-2 text-secondary small">
            <i class="bi bi-geo-alt-fill text-warning mt-1 flex-shrink-0"></i>
            <span><?= propre(SALON_ADRESSE) ?></span>
          </li>
          <li class="d-flex align-items-center gap-2 small">
            <i class="bi bi-telephone-fill text-warning flex-shrink-0"></i>
            <a href="tel:<?= preg_replace('/\s+/', '', SALON_TEL) ?>"
               class="text-secondary text-decoration-none footer-link"><?= propre(SALON_TEL) ?></a>
          </li>
          <li class="d-flex align-items-center gap-2 small">
            <i class="bi bi-envelope-fill text-warning flex-shrink-0"></i>
            <a href="mailto:<?= propre(SALON_EMAIL) ?>"
               class="text-secondary text-decoration-none footer-link"><?= propre(SALON_EMAIL) ?></a>
          </li>
        </ul>
      </div>

    </div>

    <div class="border-top border-secondary d-flex flex-wrap justify-content-between align-items-center py-3 gap-2">
      <p class="text-secondary small mb-0">
        &copy; <?= date('Y') ?> <?= propre(SALON_NOM) ?> — Tous droits réservés.
      </p>
      <a href="connexion.php" class="text-secondary text-decoration-none small footer-link">
        <i class="bi bi-shield-lock me-1"></i>Espace admin
      </a>
    </div>

  </div>
</footer>

<?php endif; ?>

<style>
.footer-link:hover   { color: #ffc107 !important; }
.footer-social       { width: 34px; height: 34px; }
.footer-social:hover { background-color: #ffc107 !important; border-color: #ffc107 !important; color: #111 !important; }
.letter-spacing      { letter-spacing: .08em; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>