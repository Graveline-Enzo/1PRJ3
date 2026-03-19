<?php
require_once 'inc/init.inc.php';
require_once 'inc/fonction.inc.php';

if (empty($_SESSION['membre'])) {
    header('Location: connexion.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');

    if ($action === 'service_creer')
        creerService(post('nom'), post('description'), (int)post('duree_minutes'), (int)post('prix_euros'));
    if ($action === 'service_modifier')
        modifierService((int)post('id'), post('nom'), post('description'), (int)post('duree_minutes'), (int)post('prix_euros'));
    if ($action === 'service_supprimer')
        supprimerService((int)post('id'));
    if ($action === 'dispo_creer')
        creerDisponibilite(post('jour_semaine'), post('heure_debut'), post('heure_fin'), post('actif'));
    if ($action === 'dispo_modifier')
        modifierDisponibilite((int)post('id'), post('jour_semaine'), post('heure_debut'), post('heure_fin'), post('actif'));
    if ($action === 'dispo_supprimer')
        supprimerDisponibilite((int)post('id'));
    if ($action === 'resa_statut')
        modifierStatutReservation((int)post('id'), post('statut'));
    if ($action === 'resa_supprimer')
        supprimerReservation((int)post('id'));

    if ($action === 'deconnexion') {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Location: connexion.php');
        exit();
    }

    header('Location: admin.php?tab=' . ($_GET['tab'] ?? 'services'));
    exit();
}

$tab          = $_GET['tab'] ?? 'services';
$services     = getServices();
$dispos       = getDisponibilites();
$reservations = getReservations();
$jours        = ['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — <?= SALON_NOM ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-3">
    <span class="navbar-brand mb-0">✂ Admin — <?= SALON_NOM ?></span>
    <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-sm btn-outline-light">Site</a>
        <form method="POST">
            <input type="hidden" name="action" value="deconnexion">
            <button class="btn btn-sm btn-danger">Déconnexion</button>
        </form>
    </div>
</nav>

<div class="container py-4">

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item"><a class="nav-link <?= $tab==='services'     ?'active':'' ?>" href="?tab=services">Services</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab==='dispos'       ?'active':'' ?>" href="?tab=dispos">Disponibilités</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab==='reservations' ?'active':'' ?>" href="?tab=reservations">Réservations</a></li>
    </ul>

    <?php if ($tab === 'services'): ?>

        <form method="POST" class="row g-2 mb-4 align-items-end border rounded p-3 bg-white">
            <input type="hidden" name="action" value="service_creer">
            <div class="col-md-3"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Description</label><input type="text" name="description" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Durée (min)</label><input type="number" name="duree_minutes" class="form-control" required min="5"></div>
            <div class="col-md-2"><label class="form-label">Prix (€)</label><input type="number" name="prix_euros" class="form-control" required min="0"></div>
            <div class="col-md-1"><button class="btn btn-success w-100">+</button></div>
        </form>

        <table class="table table-bordered bg-white">
            <thead class="table-dark"><tr><th>Nom</th><th>Description</th><th>Durée</th><th>Prix</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($services as $s): ?>
                <tr>
                    <form method="POST">
                    <input type="hidden" name="action" value="service_modifier">
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                    <td><input type="text" name="nom" class="form-control form-control-sm" value="<?= propre($s['nom']) ?>" required></td>
                    <td><input type="text" name="description" class="form-control form-control-sm" value="<?= propre($s['description']) ?>"></td>
                    <td><input type="number" name="duree_minutes" class="form-control form-control-sm" value="<?= (int)$s['duree_minutes'] ?>" required></td>
                    <td><input type="number" name="prix_euros" class="form-control form-control-sm" value="<?= (int)$s['prix_euros'] ?>" required></td>
                    <td class="d-flex gap-1">
                        <button class="btn btn-sm btn-primary">✔</button>
                    </form>
                    <form method="POST" onsubmit="return confirm('Supprimer ?')">
                        <input type="hidden" name="action" value="service_supprimer">
                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                        <button class="btn btn-sm btn-danger">✖</button>
                    </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php elseif ($tab === 'dispos'): ?>

        <form method="POST" class="row g-2 mb-4 align-items-end border rounded p-3 bg-white">
            <input type="hidden" name="action" value="dispo_creer">
            <div class="col-md-3">
                <label class="form-label">Jour</label>
                <select name="jour_semaine" class="form-select">
                    <?php foreach ($jours as $j): ?><option value="<?= $j ?>"><?= ucfirst($j) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Heure début</label><input type="time" name="heure_debut" class="form-control" value="09:00" required></div>
            <div class="col-md-3"><label class="form-label">Heure fin</label><input type="time" name="heure_fin" class="form-control" value="19:00" required></div>
            <div class="col-md-2">
                <label class="form-label">Statut</label>
                <select name="actif" class="form-select"><option value="ouvert">Ouvert</option><option value="ferme">Fermé</option></select>
            </div>
            <div class="col-md-1"><button class="btn btn-success w-100">+</button></div>
        </form>

        <table class="table table-bordered bg-white">
            <thead class="table-dark"><tr><th>Jour</th><th>Début</th><th>Fin</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($dispos as $d): ?>
                <tr>
                    <form method="POST">
                    <input type="hidden" name="action" value="dispo_modifier">
                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                    <td>
                        <select name="jour_semaine" class="form-select form-select-sm">
                            <?php foreach ($jours as $j): ?>
                                <option value="<?= $j ?>" <?= $d['jour_semaine']===$j?'selected':'' ?>><?= ucfirst($j) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="time" name="heure_debut" class="form-control form-control-sm" value="<?= substr($d['heure_debut'],0,5) ?>" required></td>
                    <td><input type="time" name="heure_fin"   class="form-control form-control-sm" value="<?= substr($d['heure_fin'],0,5) ?>" required></td>
                    <td>
                        <select name="actif" class="form-select form-select-sm">
                            <option value="ouvert" <?= $d['actif']==='ouvert'?'selected':'' ?>>Ouvert</option>
                            <option value="ferme"  <?= $d['actif']==='ferme' ?'selected':'' ?>>Fermé</option>
                        </select>
                    </td>
                    <td class="d-flex gap-1">
                        <button class="btn btn-sm btn-primary">✔</button>
                    </form>
                    <form method="POST" onsubmit="return confirm('Supprimer ?')">
                        <input type="hidden" name="action" value="dispo_supprimer">
                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                        <button class="btn btn-sm btn-danger">✖</button>
                    </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php elseif ($tab === 'reservations'): ?>

        <table class="table table-bordered bg-white">
            <thead class="table-dark"><tr><th>Client</th><th>Email</th><th>Tél</th><th>Service</th><th>Date</th><th>Heure</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($reservations as $r): ?>
                <tr>
                    <td><?= propre($r['nom_client']) ?></td>
                    <td><?= propre($r['email_client']) ?></td>
                    <td><?= propre($r['telephone']) ?></td>
                    <td><?= propre($r['service_nom'] ?? '—') ?></td>
                    <td><?= date('d/m/Y', strtotime($r['date_rdv'])) ?></td>
                    <td><?= substr($r['heure_rdv'], 0, 5) ?></td>
                    <td>
                        <form method="POST" class="d-flex gap-1">
                            <input type="hidden" name="action" value="resa_statut">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <select name="statut" class="form-select form-select-sm">
                                <option value="en_attente" <?= $r['statut']==='en_attente'?'selected':'' ?>>En attente</option>
                                <option value="confirme"   <?= $r['statut']==='confirme'  ?'selected':'' ?>>Confirmé</option>
                                <option value="annule"     <?= $r['statut']==='annule'    ?'selected':'' ?>>Annulé</option>
                            </select>
                            <button class="btn btn-sm btn-primary">✔</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Supprimer ?')">
                            <input type="hidden" name="action" value="resa_supprimer">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button class="btn btn-sm btn-danger">✖</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>