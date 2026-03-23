<?php
require_once 'inc/init.inc.php';
require_once 'inc/fonction.inc.php';

$pageTitle = 'Réservation — ' . SALON_NOM;
$services  = getServices();

$erreurs = [];
$success = false;
$recap   = null;

// Étape 1 — validation et affichage du récap
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['etape']) && $_POST['etape'] === 'recap') {

    $service_id = (int)post('service_id');
    $nom        = post('nom');
    $prenom     = post('prenom');
    $email      = post('email');
    $telephone  = post('telephone');

    // Validation
    if ($service_id <= 0)                          $erreurs[] = "Veuillez sélectionner un service.";
    if (!validerNom($nom))                         $erreurs[] = "Le nom est invalide (2–100 caractères).";
    if (!validerNom($prenom))                      $erreurs[] = "Le prénom est invalide (2–100 caractères).";
    if (!validerEmail($email))                     $erreurs[] = "L'adresse email est invalide.";
    if (!preg_match('/^[0-9]{10}$/', $telephone))  $erreurs[] = "Le téléphone doit contenir 10 chiffres.";

    // Vérifier que le service existe
    $serviceChoisi = null;
    foreach ($services as $s) {
        if ((int)$s['id'] === $service_id) { $serviceChoisi = $s; break; }
    }
    if (!$serviceChoisi) $erreurs[] = "Service invalide.";

    if (empty($erreurs)) {
        $recap = compact('service_id', 'nom', 'prenom', 'email', 'telephone', 'serviceChoisi');
    }
}

// Étape 2 — confirmation et insertion en BDD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['etape']) && $_POST['etape'] === 'confirmer') {

    $service_id = (int)post('service_id');
    $nom        = post('nom');
    $prenom     = post('prenom');
    $email      = post('email');
    $telephone  = post('telephone');

    $pdo = getDB();
    if ($pdo) {
        $stmt = $pdo->prepare("
            INSERT INTO reservations (service_id, date_rdv, heure_rdv, nom_client, email_client, telephone, statut)
            VALUES (:service_id, CURDATE(), NOW(), :nom_client, :email_client, :telephone, 'en_attente')
        ");
        $stmt->execute([
            ':service_id'   => $service_id,
            ':nom_client'   => $prenom . ' ' . $nom,
            ':email_client' => $email,
            ':telephone'    => $telephone,
        ]);
        $success = true;
    } else {
        $erreurs[] = "Une erreur technique est survenue. Veuillez réessayer.";
    }
}

require_once 'inc/haut.inc.php';
?>

<div class="container py-5 mt-5">
  <div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">

      <?php if ($success): ?>

        <!-- Confirmation -->
        <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
          <div class="mb-3">
            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:72px;height:72px;">
              <i class="bi bi-check-lg text-success fs-1"></i>
            </div>
          </div>
          <h3 class="fw-bold mb-2">Réservation envoyée !</h3>
          <p class="text-muted mb-4">Votre demande a bien été reçue. Nous vous contacterons par email pour confirmer votre rendez-vous.</p>
          <a href="index.php" class="btn btn-warning fw-bold rounded-pill px-4">Retour à l'accueil</a>
        </div>

      <?php elseif ($recap): ?>

        <!-- Récapitulatif -->
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h4 class="fw-bold mb-4 text-center">Confirmer votre réservation</h4>

          <ul class="list-group list-group-flush mb-4">
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Service</span>
              <strong><?= propre($recap['serviceChoisi']['nom']) ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Durée</span>
              <strong><?= (int)$recap['serviceChoisi']['duree_minutes'] ?> min</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Prix</span>
              <strong class="text-warning"><?= number_format((float)$recap['serviceChoisi']['prix_euros'], 2, ',', ' ') ?> €</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Nom</span>
              <strong><?= propre($recap['prenom']) ?> <?= propre($recap['nom']) ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Email</span>
              <strong><?= propre($recap['email']) ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Téléphone</span>
              <strong><?= propre($recap['telephone']) ?></strong>
            </li>
          </ul>

          <div class="d-flex gap-3">
            <!-- Bouton retour — repopule le formulaire -->
            <form method="POST" class="w-50">
              <input type="hidden" name="etape"      value="retour">
              <input type="hidden" name="service_id" value="<?= $recap['service_id'] ?>">
              <input type="hidden" name="nom"        value="<?= propre($recap['nom']) ?>">
              <input type="hidden" name="prenom"     value="<?= propre($recap['prenom']) ?>">
              <input type="hidden" name="email"      value="<?= propre($recap['email']) ?>">
              <input type="hidden" name="telephone"  value="<?= propre($recap['telephone']) ?>">
              <button class="btn btn-outline-secondary rounded-pill w-100">Modifier</button>
            </form>

            <!-- Bouton confirmation -->
            <form method="POST" class="w-50">
              <input type="hidden" name="etape"      value="confirmer">
              <input type="hidden" name="service_id" value="<?= $recap['service_id'] ?>">
              <input type="hidden" name="nom"        value="<?= propre($recap['nom']) ?>">
              <input type="hidden" name="prenom"     value="<?= propre($recap['prenom']) ?>">
              <input type="hidden" name="email"      value="<?= propre($recap['email']) ?>">
              <input type="hidden" name="telephone"  value="<?= propre($recap['telephone']) ?>">
              <button class="btn btn-warning fw-bold rounded-pill w-100">Confirmer</button>
            </form>
          </div>
        </div>

      <?php else: ?>

        <!-- Formulaire -->
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <div class="text-center mb-4">
            <div class="bg-warning bg-opacity-10 rounded-3 d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
              <i class="bi bi-calendar-check fs-4 text-warning"></i>
            </div>
            <h3 class="fw-bold">Prendre rendez-vous</h3>
            <p class="text-muted small">Remplissez le formulaire, nous confirmerons par email.</p>
          </div>

          <?php if (!empty($erreurs)): ?>
            <div class="alert alert-danger">
              <ul class="mb-0 ps-3">
                <?php foreach ($erreurs as $e): ?>
                  <li><?= propre($e) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="POST" id="resaForm" novalidate>
            <input type="hidden" name="etape" value="recap">

            <div class="mb-3">
              <label for="service_id" class="form-label fw-semibold">Service <span class="text-danger">*</span></label>
              <select name="service_id" id="service_id" class="form-select" required>
                <option value="">— Choisir un service —</option>
                <?php foreach ($services as $s): ?>
                  <option value="<?= $s['id'] ?>"
                    <?= (int)post('service_id') === (int)$s['id'] ? 'selected' : '' ?>>
                    <?= propre($s['nom']) ?> — <?= (int)$s['duree_minutes'] ?> min — <?= number_format((float)$s['prix_euros'], 2, ',', ' ') ?> €
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Veuillez sélectionner un service.</div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-sm-6">
                <label for="prenom" class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="prenom" name="prenom"
                       value="<?= propre(post('prenom')) ?>"
                       placeholder="Jean" required minlength="2" maxlength="100">
                <div class="invalid-feedback">Prénom invalide.</div>
              </div>
              <div class="col-sm-6">
                <label for="nom" class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nom" name="nom"
                       value="<?= propre(post('nom')) ?>"
                       placeholder="Dupont" required minlength="2" maxlength="100">
                <div class="invalid-feedback">Nom invalide.</div>
              </div>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="email" name="email"
                     value="<?= propre(post('email')) ?>"
                     placeholder="jean@exemple.fr" required maxlength="150">
              <div class="invalid-feedback">Email invalide.</div>
            </div>

            <div class="mb-4">
              <label for="telephone" class="form-label fw-semibold">Téléphone <span class="text-danger">*</span></label>
              <input type="tel" class="form-control" id="telephone" name="telephone"
                     value="<?= propre(post('telephone')) ?>"
                     placeholder="0612345678" required pattern="[0-9]{10}" maxlength="10">
              <div class="invalid-feedback">Téléphone invalide (10 chiffres).</div>
            </div>

            <button type="submit" class="btn btn-warning fw-bold rounded-pill w-100 py-2">
              <i class="bi bi-arrow-right-circle me-2"></i>Voir le récapitulatif
            </button>
          </form>
        </div>

      <?php endif; ?>

    </div>
  </div>
</div>

<script>
const form = document.getElementById('resaForm');
if (form) {
  form.addEventListener('submit', e => {
    if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    form.classList.add('was-validated');
  });
}
</script>

<?php require_once 'inc/bas.inc.php'; ?>