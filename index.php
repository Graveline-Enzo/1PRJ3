<?php
/* =============================================================
   index.php — Page principale du salon
   Utilise les includes : init → fonctions → haut → contenu → bas
============================================================= */

require_once 'inc/init.inc.php';

// 🔒 Connexion obligatoire — redirige si pas connecté
if (empty($_SESSION['membre'])) {
    header('Location: connexion.php');
    exit();
}

$contenu = '';

require_once 'inc/fonction.inc.php';

// Titre de la page (utilisé dans haut.inc.php)
$pageTitle = SALON_NOM . ' — Salon de Coiffure Paris';

// ── Traitement du formulaire de contact (section #contact) ──
$contactSuccess = false;
$contactErreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_contact'])) {

    $nom     = post('nom');
    $email   = post('email');
    $message = post('message');

    // Validation serveur
    if (!validerNom($nom))       $contactErreurs[] = "Le nom est invalide (2–100 caractères).";
    if (!validerEmail($email))   $contactErreurs[] = "L'adresse email est invalide.";
    if (!validerMessage($message)) $contactErreurs[] = "Le message doit faire entre 10 et 1000 caractères.";

    if (empty($contactErreurs)) {
        if (sauvegarderContact($nom, $email, $message)) {
            $contactSuccess = true;
        } else {
            $contactErreurs[] = "Une erreur technique est survenue. Veuillez réessayer.";
        }
    }
}

// ── En-tête HTML ───────────────────────────────────────────
require_once 'inc/haut.inc.php';
?>

<!-- ============================================================
     HERO — ACCUEIL
============================================================ -->
<section id="accueil" class="hero-section d-flex align-items-center">
  <div class="hero-overlay"></div>
  <div class="container position-relative text-center text-white">
    <p class="hero-tagline">Bienvenue dans notre salon</p>
    <h1 class="hero-title">L'Art de la Coiffure</h1>
    <p class="hero-subtitle">Un espace dédié à votre beauté, à Paris depuis 2010.</p>
    <div class="mt-4 d-flex gap-3 justify-content-center flex-wrap">
      <a href="reservation.php" class="btn btn-gold btn-lg">Prendre rendez-vous</a>
      <a href="#services"       class="btn btn-outline-light btn-lg">Nos services</a>
    </div>
  </div>
</section>

<!-- ============================================================
     SERVICES
============================================================ -->
<section id="services" class="py-5 section-light">
  <div class="container">
    <div class="section-header text-center mb-5">
      <span class="section-label">Ce que nous proposons</span>
      <h2 class="section-title">Nos Services</h2>
    </div>

    <div class="row g-4">
      <?php if (!empty($services)): ?>
        <?php foreach ($services as $s): ?>
          <div class="col-md-6 col-lg-4">
            <div class="service-card h-100">
              <div class="service-icon"><i class="bi bi-scissors"></i></div>
              <h5><?= propre($s['nom']) ?></h5>
              <p><?= propre($s['description']) ?></p>
              <span class="price-tag">
                À partir de <?= number_format((float)$s['prix'], 2, ',', ' ') ?> €
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- Services statiques si la BDD n'est pas encore disponible -->
        <?php
        $servicesStatiques = [
          ['✂', 'Coupe Femme',     'Coupe sur-mesure adaptée à votre morphologie et style.',     '35'],
          ['✂', 'Coupe Homme',     'Coupe classique ou moderne, avec finitions soignées.',         '20'],
          ['🎨','Coloration',       'Balayage, mèches, ombré hair — des couleurs qui subliment.',  '60'],
          ['✨','Brushing',         'Lissage, ondulations, chignon — pour chaque occasion.',        '25'],
          ['💧','Soin Capillaire',  'Masques, kératine, soins hydratants pour des cheveux sains.', '30'],
          ['👶','Coupe Enfant',     'Coupe douce et rapide pour les plus jeunes.',                  '15'],
        ];
        foreach ($servicesStatiques as [$icone, $nom, $desc, $prix]): ?>
          <div class="col-md-6 col-lg-4">
            <div class="service-card h-100">
              <div class="service-icon"><i class="bi bi-scissors"></i></div>
              <h5><?= $nom ?></h5>
              <p><?= $desc ?></p>
              <span class="price-tag">À partir de <?= $prix ?> €</span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ============================================================
     HORAIRES
============================================================ -->
<section id="horaires" class="py-5 section-dark">
  <div class="container">
    <div class="section-header text-center mb-5">
      <span class="section-label">Quand nous trouver</span>
      <h2 class="section-title text-white">Horaires d'Ouverture</h2>
    </div>

    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="horaires-card">
          <?php
          $horaires = [
            'Lundi'    => null,
            'Mardi'    => '9h00 – 19h00',
            'Mercredi' => '9h00 – 19h00',
            'Jeudi'    => '9h00 – 20h00',
            'Vendredi' => '9h00 – 20h00',
            'Samedi'   => '8h30 – 18h30',
            'Dimanche' => null,
          ];
          foreach ($horaires as $jour => $heure): ?>
            <div class="horaire-row">
              <span><?= $jour ?></span>
              <?php if ($heure): ?>
                <span><?= $heure ?></span>
              <?php else: ?>
                <span class="text-muted fst-italic">Fermé</span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
          <a href="reservation.php" class="btn btn-gold btn-lg">
            <i class="bi bi-calendar-check me-2"></i>Prendre rendez-vous
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     TÉMOIGNAGES
============================================================ -->
<section id="temoignages" class="py-5 section-light">
  <div class="container">
    <div class="section-header text-center mb-5">
      <span class="section-label">Ce qu'ils disent</span>
      <h2 class="section-title">Avis de nos clients</h2>
    </div>

    <div class="row g-4">
      <?php
      $temoignages = [
        ['S', 'Sophie M.',  'Cliente fidèle',   5, "Je suis cliente depuis 3 ans, la qualité est toujours au rendez-vous. Merci à toute l'équipe !"],
        ['L', 'Laura B.',   'Mariée en 2024',   5, "Excellent accueil, coiffure parfaite pour mon mariage. Je recommande vivement ce salon !"],
        ['T', 'Thomas K.',  'Nouveau client',   4, "Super salon, ambiance chaleureuse et résultat impeccable. Je reviendrai sans hésitation."],
      ];
      foreach ($temoignages as [$initiale, $nom, $role, $note, $texte]): ?>
        <div class="col-md-4">
          <div class="temoignage-card">
            <div class="stars mb-2">
              <?= str_repeat('★', $note) . str_repeat('☆', 5 - $note) ?>
            </div>
            <p class="temoignage-text">"<?= propre($texte) ?>"</p>
            <div class="temoignage-auteur">
              <div class="avatar"><?= $initiale ?></div>
              <div>
                <strong><?= propre($nom) ?></strong>
                <small class="d-block text-muted"><?= propre($role) ?></small>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================================================
     CONTACT
============================================================ -->
<section id="contact" class="py-5 section-dark">
  <div class="container">
    <div class="section-header text-center mb-5">
      <span class="section-label">Écrivez-nous</span>
      <h2 class="section-title text-white">Contact</h2>
    </div>

    <div class="row g-5 align-items-start">

      <!-- Informations -->
      <div class="col-md-5">
        <div class="contact-info-box">
          <h5 class="text-gold mb-4">Informations</h5>
          <p><i class="bi bi-geo-alt-fill me-2 text-gold"></i><?= propre(SALON_ADRESSE) ?></p>
          <p><i class="bi bi-telephone-fill me-2 text-gold"></i><?= propre(SALON_TEL) ?></p>
          <p><i class="bi bi-envelope-fill me-2 text-gold"></i><?= propre(SALON_EMAIL) ?></p>
          <div class="mt-4 d-flex gap-3">
            <a href="https://instagram.com" target="_blank" class="social-icon"><i class="bi bi-instagram"></i></a>
            <a href="https://facebook.com"  target="_blank" class="social-icon"><i class="bi bi-facebook"></i></a>
            <a href="https://tiktok.com"    target="_blank" class="social-icon"><i class="bi bi-tiktok"></i></a>
          </div>
        </div>
      </div>

      <!-- Formulaire de contact -->
      <div class="col-md-7">
        <form id="contactForm" method="POST" action="index.php#contact"
              novalidate class="contact-form">

          <!-- Champ caché pour identifier ce formulaire -->
          <input type="hidden" name="form_contact" value="1">

          <?php if ($contactSuccess): ?>
            <div class="alert alert-success d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill"></i>
              Votre message a bien été envoyé. Merci !
            </div>

          <?php elseif (!empty($contactErreurs)): ?>
            <div class="alert alert-danger">
              <ul class="mb-0 ps-3">
                <?php foreach ($contactErreurs as $err): ?>
                  <li><?= propre($err) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <div class="mb-3">
            <label for="nom" class="form-label text-white">
              Nom complet <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control form-control-dark" id="nom" name="nom"
                   value="<?= propre(post('nom')) ?>"
                   placeholder="Jean Dupont" required minlength="2" maxlength="100">
            <div class="invalid-feedback">Veuillez entrer votre nom.</div>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label text-white">
              Email <span class="text-danger">*</span>
            </label>
            <input type="email" class="form-control form-control-dark" id="email" name="email"
                   value="<?= propre(post('email')) ?>"
                   placeholder="jean@exemple.fr" required maxlength="150">
            <div class="invalid-feedback">Veuillez entrer un email valide.</div>
          </div>

          <div class="mb-3">
            <label for="message" class="form-label text-white">
              Message <span class="text-danger">*</span>
            </label>
            <textarea class="form-control form-control-dark" id="message" name="message"
                      rows="5" placeholder="Votre message..." required
                      minlength="10" maxlength="1000"><?= propre(post('message')) ?></textarea>
            <div class="invalid-feedback">Le message doit faire entre 10 et 1000 caractères.</div>
          </div>

          <button type="submit" class="btn btn-gold w-100">
            <i class="bi bi-send me-2"></i>Envoyer le message
          </button>

        </form>
      </div>
    </div>
  </div>
</section>

<?php require_once 'inc/bas.inc.php'; ?>