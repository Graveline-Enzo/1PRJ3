<?php
require_once 'inc/init.inc.php';
require_once 'inc/fonction.inc.php';

$pageTitle = 'Réservation — ' . SALON_NOM;
$services  = getServices();
$dispos    = getDisponibilites();

$erreurs = [];
$success = false;
$recap   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['etape']) && $_POST['etape'] === 'recap') {

    $service_id = (int)post('service_id');
    $date_rdv   = post('date_rdv');
    $heure_rdv  = post('heure_rdv');
    $nom        = post('nom');
    $prenom     = post('prenom');
    $email      = post('email');
    $telephone  = post('telephone');

    if ($service_id <= 0)                                        $erreurs[] = "Veuillez sélectionner un service.";
    if (!$date_rdv || !strtotime($date_rdv))                     $erreurs[] = "Veuillez sélectionner une date.";
    if (!$heure_rdv || !preg_match('/^\d{2}:\d{2}$/', $heure_rdv)) $erreurs[] = "Veuillez sélectionner un créneau.";
    if (!validerNom($nom))                                       $erreurs[] = "Le nom est invalide (2–100 caractères).";
    if (!validerNom($prenom))                                    $erreurs[] = "Le prénom est invalide (2–100 caractères).";
    if (!validerEmail($email))                                   $erreurs[] = "L'adresse email est invalide.";
    if (!preg_match('/^[0-9]{10}$/', $telephone))                $erreurs[] = "Le téléphone doit contenir 10 chiffres.";


    $serviceChoisi = null;
    foreach ($services as $s) {
        if ((int)$s['id'] === $service_id) { $serviceChoisi = $s; break; }
    }
    if (!$serviceChoisi) $erreurs[] = "Service invalide.";

    if (empty($erreurs)) {
        $recap = compact('service_id', 'date_rdv', 'heure_rdv', 'nom', 'prenom', 'email', 'telephone', 'serviceChoisi');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['etape']) && $_POST['etape'] === 'confirmer') {

    $service_id = (int)post('service_id');
    $date_rdv   = post('date_rdv');
    $heure_rdv  = post('heure_rdv');
    $nom        = post('nom');
    $prenom     = post('prenom');
    $email      = post('email');
    $telephone  = post('telephone');

    $pdo = getDB();
    if ($pdo) {
        $stmt = $pdo->prepare("
            INSERT INTO reservations (service_id, date_rdv, heure_rdv, nom_client, email_client, telephone, statut)
            VALUES (:service_id, :date_rdv, :heure_rdv, :nom_client, :email_client, :telephone, 'en_attente')
        ");
        $stmt->execute([
            ':service_id'   => $service_id,
            ':date_rdv'     => $date_rdv,
            ':heure_rdv'    => $heure_rdv,
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
              <span class="text-muted">Date</span>
              <strong><?= date('d/m/Y', strtotime($recap['date_rdv'])) ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Heure</span>
              <strong><?= propre($recap['heure_rdv']) ?></strong>
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
            <form method="POST" class="w-50">
              <input type="hidden" name="etape"      value="retour">
              <input type="hidden" name="service_id" value="<?= $recap['service_id'] ?>">
              <input type="hidden" name="date_rdv"   value="<?= propre($recap['date_rdv']) ?>">
              <input type="hidden" name="heure_rdv"  value="<?= propre($recap['heure_rdv']) ?>">
              <input type="hidden" name="nom"        value="<?= propre($recap['nom']) ?>">
              <input type="hidden" name="prenom"     value="<?= propre($recap['prenom']) ?>">
              <input type="hidden" name="email"      value="<?= propre($recap['email']) ?>">
              <input type="hidden" name="telephone"  value="<?= propre($recap['telephone']) ?>">
              <button class="btn btn-outline-secondary rounded-pill w-100">Modifier</button>
            </form>

            <form method="POST" class="w-50">
              <input type="hidden" name="etape"      value="confirmer">
              <input type="hidden" name="service_id" value="<?= $recap['service_id'] ?>">
              <input type="hidden" name="date_rdv"   value="<?= propre($recap['date_rdv']) ?>">
              <input type="hidden" name="heure_rdv"  value="<?= propre($recap['heure_rdv']) ?>">
              <input type="hidden" name="nom"        value="<?= propre($recap['nom']) ?>">
              <input type="hidden" name="prenom"     value="<?= propre($recap['prenom']) ?>">
              <input type="hidden" name="email"      value="<?= propre($recap['email']) ?>">
              <input type="hidden" name="telephone"  value="<?= propre($recap['telephone']) ?>">
              <button class="btn btn-warning fw-bold rounded-pill w-100">Confirmer</button>
            </form>
          </div>
        </div>

      <?php else: ?>

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
                          data-duree="<?= (int)$s['duree_minutes'] ?>"
                    <?= (int)post('service_id') === (int)$s['id'] ? 'selected' : '' ?>>
                    <?= propre($s['nom']) ?> — <?= (int)$s['duree_minutes'] ?> min — <?= number_format((float)$s['prix_euros'], 2, ',', ' ') ?> €
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Veuillez sélectionner un service.</div>
            </div>

            <div class="mb-3" id="bloc-calendrier" style="display:none;">
              <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
              <div id="calendrier" class="border rounded-3 p-3 bg-light">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="prev-mois">‹</button>
                  <span class="fw-bold" id="label-mois"></span>
                  <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="next-mois">›</button>
                </div>
                <div class="row g-1 text-center mb-1">
                  <?php foreach (['Lu','Ma','Me','Je','Ve','Sa','Di'] as $j): ?>
                    <div class="col"><small class="text-muted fw-semibold"><?= $j ?></small></div>
                  <?php endforeach; ?>
                </div>
                <div id="grille-jours" class="row g-1 text-center"></div>
              </div>
              <input type="hidden" name="date_rdv" id="date_rdv" value="<?= propre(post('date_rdv')) ?>">
              <div class="text-danger small mt-1" id="error-date"></div>
            </div>

            <div class="mb-3" id="bloc-creneaux" style="display:none;">
              <label class="form-label fw-semibold">Créneau <span class="text-danger">*</span></label>
              <div id="liste-creneaux" class="d-flex flex-wrap gap-2"></div>
              <input type="hidden" name="heure_rdv" id="heure_rdv" value="<?= propre(post('heure_rdv')) ?>">
              <div class="text-danger small mt-1" id="error-creneau"></div>
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
// ===== CALENDRIER =====
let moisCourant = new Date();
moisCourant.setDate(1);
let dateSelectionnee = null;
let dureeSelectionnee = 0;

const joursDispoSalon = <?php
    $joursOuverts = array_filter($dispos, fn($d) => $d['actif'] === 'ouvert');
    $noms = ['lundi'=>1,'mardi'=>2,'mercredi'=>3,'jeudi'=>4,'vendredi'=>5,'samedi'=>6,'dimanche'=>0];
    $indices = array_map(fn($d) => $noms[$d['jour_semaine']], $joursOuverts);
    echo json_encode(array_values($indices));
?>;

function renderCalendrier() {
    const mois = moisCourant.getMonth();
    const annee = moisCourant.getFullYear();
    const noms = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    document.getElementById('label-mois').textContent = noms[mois] + ' ' + annee;

    const grille = document.getElementById('grille-jours');
    grille.innerHTML = '';

    const today = new Date(); today.setHours(0,0,0,0);
    const dernier = new Date(annee, mois + 1, 0);

    let jourDebut = new Date(annee, mois, 1).getDay();
    jourDebut = jourDebut === 0 ? 6 : jourDebut - 1;
    for (let i = 0; i < jourDebut; i++) grille.innerHTML += `<div class="col"></div>`;

    for (let j = 1; j <= dernier.getDate(); j++) {
        const date = new Date(annee, mois, j);
        const dateStr = annee + '-' + String(mois+1).padStart(2,'0') + '-' + String(j).padStart(2,'0');
        const passe = date < today;
        const ouvert = joursDispoSalon.includes(date.getDay());
        const selectionne = dateStr === dateSelectionnee;

        let classe = 'btn btn-sm w-100 ';
        if (selectionne)            classe += 'btn-warning fw-bold';
        else if (passe || !ouvert)  classe += 'btn-light text-muted';
        else                        classe += 'btn-outline-secondary';

        grille.innerHTML += `
          <div class="col">
            <button type="button" class="${classe}"
              ${passe || !ouvert ? 'disabled' : ''}
              onclick="selectionnerDate('${dateStr}')">${j}</button>
          </div>`;
    }
}

function selectionnerDate(dateStr) {
    dateSelectionnee = dateStr;
    document.getElementById('date_rdv').value = dateStr;
    document.getElementById('heure_rdv').value = '';
    document.getElementById('error-date').textContent = '';
    renderCalendrier();
    chargerCreneaux(dateStr);
}

function chargerCreneaux(dateStr) {
    const bloc = document.getElementById('bloc-creneaux');
    const liste = document.getElementById('liste-creneaux');
    liste.innerHTML = '<span class="text-muted small">Chargement...</span>';
    bloc.style.display = 'block';

    fetch(`creneaux.php?date=${dateStr}&duree=${dureeSelectionnee}`)
        .then(r => r.json())
        .then(creneaux => {
            liste.innerHTML = '';
            if (creneaux.length === 0) {
                liste.innerHTML = '<span class="text-muted small">Aucun créneau disponible ce jour.</span>';
                return;
            }
            creneaux.forEach(h => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline-secondary btn-sm rounded-pill px-3';
                btn.textContent = h;
                btn.onclick = () => {
                    document.querySelectorAll('#liste-creneaux .btn').forEach(b => {
                        b.classList.remove('btn-warning');
                        b.classList.add('btn-outline-secondary');
                    });
                    btn.classList.remove('btn-outline-secondary');
                    btn.classList.add('btn-warning');
                    document.getElementById('heure_rdv').value = h;
                    document.getElementById('error-creneau').textContent = '';
                };
                liste.appendChild(btn);
            });
        });
}

document.getElementById('service_id').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    dureeSelectionnee = parseInt(option.dataset.duree) || 0;
    dateSelectionnee = null;
    document.getElementById('date_rdv').value = '';
    document.getElementById('heure_rdv').value = '';
    document.getElementById('bloc-creneaux').style.display = 'none';

    if (dureeSelectionnee > 0) {
        document.getElementById('bloc-calendrier').style.display = 'block';
        renderCalendrier();
    } else {
        document.getElementById('bloc-calendrier').style.display = 'none';
    }
});

document.getElementById('prev-mois').addEventListener('click', () => {
    moisCourant.setMonth(moisCourant.getMonth() - 1);
    renderCalendrier();
});
document.getElementById('next-mois').addEventListener('click', () => {
    moisCourant.setMonth(moisCourant.getMonth() + 1);
    renderCalendrier();
});

const form = document.getElementById('resaForm');
if (form) {
    form.addEventListener('submit', e => {
        let ok = true;
        if (!document.getElementById('date_rdv').value) {
            document.getElementById('error-date').textContent = 'Veuillez sélectionner une date.';
            ok = false;
        }
        if (!document.getElementById('heure_rdv').value) {
            document.getElementById('error-creneau').textContent = 'Veuillez sélectionner un créneau.';
            ok = false;
        }
        if (!form.checkValidity()) ok = false;
        if (!ok) { e.preventDefault(); e.stopPropagation(); }
        form.classList.add('was-validated');
    });
}
</script>

<?php require_once 'inc/bas.inc.php'; ?>