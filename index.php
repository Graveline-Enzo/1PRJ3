<?php
require_once 'inc/init.inc.php';
require_once 'inc/fonction.inc.php';

$pageTitle = SALON_NOM . ' — Salon de Coiffure Paris';

$contactSuccess = false;
$contactErreurs = [];

$services = getServices();
$dispos   = getDisponibilites();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_contact'])) {

    $nom     = post('nom');
    $email   = post('email');
    $message = post('message');
    
    if (!validerNom($nom))         $contactErreurs[] = "Le nom est invalide (2–100 caractères).";
    if (!validerEmail($email))     $contactErreurs[] = "L'adresse email est invalide.";
    if (!validerMessage($message)) $contactErreurs[] = "Le message doit faire entre 10 et 1000 caractères.";

    if (empty($contactErreurs)) {
        if (sauvegarderContact($nom, $email, $message)) {
            $contactSuccess = true;
        } else {
            $contactErreurs[] = "Une erreur technique est survenue. Veuillez réessayer.";
        }
    }
}

require_once 'inc/haut.inc.php';
?>

<section id="accueil" class="salon-presentation d-flex align-items-center" style="background-image: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)), url('./ressource/salon-background.jpg'); background-size: cover; background-position: center; height: 100vh; margin-top: -56px; padding-top: 56px;">
  <div class="salon-content px-5 text-white" style="max-width: 700px;">
    <h1 class="display-1 fw-black text-white mb-4">Hair it</h1>
    <p class="fs-5 lh-lg mb-4">Depuis plus de 15 ans, nous sublimions votre beauté naturelle avec passion et expertise. Notre équipe de coiffeurs professionnels vous accueille dans un cadre élégant et chaleureux pour une expérience unique.</p>
    <div class="d-flex gap-3 mb-5 flex-wrap">
      <a href="reservation.php" class="btn rounded-pill px-4 py-3 fw-bold text-white" style="background-color: var(--orange);">Prendre rendez-vous</a>
      <a href="#services" class="btn btn-outline-light rounded-pill px-4 py-3 fw-bold">Découvrir nos services</a>
    </div>
    <div class="d-flex gap-5 pt-3 border-top border-secondary">
      <div>
        <strong class="fs-4 d-block">15+</strong>
        <p class="mb-0 small text-white-50">Années d'expérience</p>
      </div>
      <div>
        <strong class="fs-4 d-block">5000+</strong>
        <p class="mb-0 small text-white-50">Clients satisfaits</p>
      </div>
    </div>
  </div>
</section>

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
                À partir de <?= number_format((float)$s['prix_euros'], 2, ',', ' ') ?> €
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <?php
        $servicesStatiques = [
          ['✂', 'Coupe Femme',    'Coupe sur-mesure adaptée à votre morphologie et style.',     '35'],
          ['✂', 'Coupe Homme',    'Coupe classique ou moderne, avec finitions soignées.',         '20'],
          ['🎨','Coloration',      'Balayage, mèches, ombré hair — des couleurs qui subliment.',  '60'],
          ['✨','Brushing',        'Lissage, ondulations, chignon — pour chaque occasion.',        '25'],
          ['💧','Soin Capillaire', 'Masques, kératine, soins hydratants pour des cheveux sains.', '30'],
          ['👶','Coupe Enfant',    'Coupe douce et rapide pour les plus jeunes.',                  '15'],
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
          $joursOrdre   = ['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'];
          $dispoParJour = [];
          foreach ($dispos as $d) {
              $dispoParJour[$d['jour_semaine']] = $d;
          }
          foreach ($joursOrdre as $jour):
              $d      = $dispoParJour[$jour] ?? null;
              $ouvert = $d && $d['actif'] === 'ouvert';
          ?>
            <div class="horaire-row">
              <span><?= ucfirst($jour) ?></span>
              <?php if ($ouvert): ?>
                <span><?= date('G\hi', strtotime($d['heure_debut'])) ?> – <?= date('G\hi', strtotime($d['heure_fin'])) ?></span>
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

<section id="temoignages" class="py-5 section-light">
  <div class="container">
    <div class="section-header text-center mb-5">
      <span class="section-label">Ce qu'ils disent</span>
      <h2 class="section-title">Avis de nos clients</h2>
    </div>

    <div class="row g-4">
      <?php
      $temoignages = [
        ['S', 'Sophie M.',  'Cliente fidèle',  5, "Je suis cliente depuis 3 ans, la qualité est toujours au rendez-vous. Merci à toute l'équipe !"],
        ['L', 'Laura B.',   'Mariée en 2024',  5, "Excellent accueil, coiffure parfaite pour mon mariage. Je recommande vivement ce salon !"],
        ['T', 'Thomas K.',  'Nouveau client',  4, "Super salon, ambiance chaleureuse et résultat impeccable. Je reviendrai sans hésitation."],
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

<section id="contact" class="py-5 section-dark">
  <div class="container">
    <div class="section-header text-center mb-5">
      <span class="section-label">Écrivez-nous</span>
      <h2 class="section-title text-white">Contact</h2>
    </div>

    <div class="row g-5 align-items-start">

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

      <div class="col-md-7">
        <form id="contactForm" method="POST" action="index.php#contact" novalidate class="contact-form">

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
            <label for="nom" class="form-label text-white">Nom complet <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-dark" id="nom" name="nom"
                   value="<?= propre(post('nom')) ?>" placeholder="Jean Dupont" required minlength="2" maxlength="100">
            <div class="invalid-feedback">Veuillez entrer votre nom.</div>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label text-white">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control form-control-dark" id="email" name="email"
                   value="<?= propre(post('email')) ?>" placeholder="jean@exemple.fr" required maxlength="150">
            <div class="invalid-feedback">Veuillez entrer un email valide.</div>
          </div>

          <div class="mb-3">
            <label for="message" class="form-label text-white">Message <span class="text-danger">*</span></label>
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