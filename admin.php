<?php
require_once 'inc/init.inc.php';
require_once 'inc/fonction.inc.php';

if (empty($_SESSION['membre'])) {
    $_SESSION['redirect_apres_connexion'] = $_SERVER['REQUEST_URI'];
    header('Location: connexion.php');
    exit();
}

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
       && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');

    if ($action === 'service_creer') {
        $id = creerService(post('nom'), post('description'), (int)post('duree_minutes'), (int)post('prix_euros'));
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => (bool)$id, 'id' => $id, 'nom' => post('nom'), 'description' => post('description'), 'duree_minutes' => (int)post('duree_minutes'), 'prix_euros' => (int)post('prix_euros')]);
            exit();
        }
    }

    if ($action === 'service_modifier') {
        $ok = modifierService((int)post('id'), post('nom'), post('description'), (int)post('duree_minutes'), (int)post('prix_euros'));
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['ok' => $ok]); exit(); }
    }

    if ($action === 'service_supprimer') {
        $ok = supprimerService((int)post('id'));
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['ok' => $ok]); exit(); }
    }

    if ($action === 'dispo_creer') {
        $id = creerDisponibilite(post('jour_semaine'), post('heure_debut'), post('heure_fin'), post('actif'));
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => (bool)$id, 'id' => $id, 'jour_semaine' => post('jour_semaine'), 'heure_debut' => post('heure_debut'), 'heure_fin' => post('heure_fin'), 'actif' => post('actif')]);
            exit();
        }
    }

    if ($action === 'dispo_modifier') {
        $ok = modifierDisponibilite((int)post('id'), post('jour_semaine'), post('heure_debut'), post('heure_fin'), post('actif'));
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['ok' => $ok]); exit(); }
    }

    if ($action === 'dispo_supprimer') {
        $ok = supprimerDisponibilite((int)post('id'));
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['ok' => $ok]); exit(); }
    }

    if ($action === 'resa_statut')   modifierStatutReservation((int)post('id'), post('statut'));
    if ($action === 'resa_supprimer') supprimerReservation((int)post('id'));

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

$dispoParJour = [];
foreach ($dispos as $d) {
    $dispoParJour[$d['jour_semaine']] = $d;
}
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

        <form id="formServiceCreer" method="POST" class="row g-2 mb-4 align-items-end border rounded p-3 bg-white">
            <input type="hidden" name="action" value="service_creer">
            <div class="col-md-3"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Description</label><input type="text" name="description" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Durée (min)</label><input type="number" name="duree_minutes" class="form-control" required min="5"></div>
            <div class="col-md-2"><label class="form-label">Prix (€)</label><input type="number" name="prix_euros" class="form-control" required min="0"></div>
            <div class="col-md-1"><button class="btn btn-success w-100">+</button></div>
        </form>

        <table class="table table-bordered bg-white">
            <thead class="table-dark"><tr><th>Nom</th><th>Description</th><th>Durée</th><th>Prix</th><th></th></tr></thead>
            <tbody id="tbodyServices">
            <?php foreach ($services as $s): ?>
                <tr data-id="<?= $s['id'] ?>">
                    <td><input type="text"   class="form-control form-control-sm" name="nom"           value="<?= propre($s['nom']) ?>"           required></td>
                    <td><input type="text"   class="form-control form-control-sm" name="description"   value="<?= propre($s['description']) ?>"></td>
                    <td><input type="number" class="form-control form-control-sm" name="duree_minutes" value="<?= (int)$s['duree_minutes'] ?>"     required></td>
                    <td><input type="number" class="form-control form-control-sm" name="prix_euros"    value="<?= (int)$s['prix_euros'] ?>"         required></td>
                    <td class="d-flex gap-1">
                        <button class="btn btn-sm btn-primary btn-save-service">✔</button>
                        <button class="btn btn-sm btn-danger  btn-del-service">✖</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php elseif ($tab === 'dispos'): ?>

        <?php $joursManquants = array_filter($jours, fn($j) => !isset($dispoParJour[$j])); ?>

        <form id="formDispoCreer" method="POST"
              class="row g-2 mb-4 align-items-end border rounded p-3 bg-white"
              <?= empty($joursManquants) ? 'style="display:none"' : '' ?>>
            <input type="hidden" name="action" value="dispo_creer">
            <div class="col-md-3">
                <label class="form-label">Jour</label>
                <select name="jour_semaine" class="form-select" id="selectJour">
                    <?php foreach ($joursManquants as $j): ?>
                        <option value="<?= $j ?>"><?= ucfirst($j) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Heure début</label><input type="time" name="heure_debut" class="form-control" value="09:00" required></div>
            <div class="col-md-3"><label class="form-label">Heure fin</label><input type="time" name="heure_fin" class="form-control" value="18:00" required></div>
            <div class="col-md-2">
                <label class="form-label">Statut</label>
                <select name="actif" class="form-select">
                    <option value="ouvert">Ouvert</option>
                    <option value="ferme">Fermé</option>
                </select>
            </div>
            <div class="col-md-1"><button class="btn btn-success w-100">+</button></div>
        </form>

        <table class="table table-bordered bg-white">
            <thead class="table-dark"><tr><th>Jour</th><th>Début</th><th>Fin</th><th>Statut</th><th></th></tr></thead>
            <tbody id="tbodyDispos">
            <?php foreach ($jours as $jour):
                $d = $dispoParJour[$jour] ?? null;
                if (!$d) continue;
            ?>
                <tr data-id="<?= $d['id'] ?>" data-jour="<?= $d['jour_semaine'] ?>">
                    <td><?= ucfirst($d['jour_semaine']) ?></td>
                    <td><input type="time" name="heure_debut" class="form-control form-control-sm" value="<?= date('H:i', strtotime($d['heure_debut'])) ?>" required></td>
                    <td><input type="time" name="heure_fin"   class="form-control form-control-sm" value="<?= date('H:i', strtotime($d['heure_fin'])) ?>"   required></td>
                    <td>
                        <select name="actif" class="form-select form-select-sm">
                            <option value="ouvert" <?= $d['actif']==='ouvert'?'selected':'' ?>>Ouvert</option>
                            <option value="ferme"  <?= $d['actif']==='ferme' ?'selected':'' ?>>Fermé</option>
                        </select>
                    </td>
                    <td class="d-flex gap-1">
                        <button class="btn btn-sm btn-primary btn-save-dispo">✔</button>
                        <button class="btn btn-sm btn-danger  btn-del-dispo">✖</button>
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
                    <td><?= propre($r['telephone'] ?? '—') ?></td>
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
<script>
const esc = s => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
const ordreJours = ['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'];

async function ajaxPost(data) {
    const resp = await fetch('admin.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: data,
    });
    return resp.json();
}

function flashGreen(tr) {
    tr.style.transition = 'background .3s';
    tr.style.background = '#d1e7dd';
    setTimeout(() => tr.style.background = '', 900);
}

const formServiceCreer = document.getElementById('formServiceCreer');
if (formServiceCreer) {
    formServiceCreer.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!this.checkValidity()) { this.classList.add('was-validated'); return; }
        const json = await ajaxPost(new FormData(this));
        if (!json.ok) return;
        document.getElementById('tbodyServices').appendChild(creerLigneService(json));
        this.reset();
        this.classList.remove('was-validated');
    });
}

function creerLigneService(s) {
    const tr = document.createElement('tr');
    tr.dataset.id = s.id;
    tr.innerHTML = `
        <td><input type="text"   class="form-control form-control-sm" name="nom"           value="${esc(s.nom)}"           required></td>
        <td><input type="text"   class="form-control form-control-sm" name="description"   value="${esc(s.description)}"></td>
        <td><input type="number" class="form-control form-control-sm" name="duree_minutes" value="${s.duree_minutes}"       required></td>
        <td><input type="number" class="form-control form-control-sm" name="prix_euros"    value="${s.prix_euros}"          required></td>
        <td class="d-flex gap-1">
            <button class="btn btn-sm btn-primary btn-save-service">✔</button>
            <button class="btn btn-sm btn-danger  btn-del-service">✖</button>
        </td>`;
    return tr;
}

const tbodyServices = document.getElementById('tbodyServices');
if (tbodyServices) {
    tbodyServices.addEventListener('click', async function(e) {
        const tr = e.target.closest('tr');
        if (!tr) return;
        const id = tr.dataset.id;

        if (e.target.closest('.btn-save-service')) {
            e.preventDefault();
            const fd = new FormData();
            fd.append('action',        'service_modifier');
            fd.append('id',            id);
            fd.append('nom',           tr.querySelector('[name=nom]').value);
            fd.append('description',   tr.querySelector('[name=description]').value);
            fd.append('duree_minutes', tr.querySelector('[name=duree_minutes]').value);
            fd.append('prix_euros',    tr.querySelector('[name=prix_euros]').value);
            const json = await ajaxPost(fd);
            if (json.ok) flashGreen(tr);
        }

        if (e.target.closest('.btn-del-service')) {
            e.preventDefault();
            if (!confirm('Supprimer ce service ?')) return;
            const fd = new FormData();
            fd.append('action', 'service_supprimer');
            fd.append('id', id);
            const json = await ajaxPost(fd);
            if (json.ok) tr.remove();
        }
    });
}

const formDispoCreer = document.getElementById('formDispoCreer');
if (formDispoCreer) {
    formDispoCreer.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!this.checkValidity()) { this.classList.add('was-validated'); return; }
        const json = await ajaxPost(new FormData(this));
        if (!json.ok) return;

        const tbody  = document.getElementById('tbodyDispos');
        const tr     = creerLigneDispo(json);
        const newIdx = ordreJours.indexOf(json.jour_semaine);
        const rows   = Array.from(tbody.querySelectorAll('tr'));
        let inserted = false;
        for (const row of rows) {
            if (ordreJours.indexOf(row.dataset.jour) > newIdx) {
                tbody.insertBefore(tr, row); inserted = true; break;
            }
        }
        if (!inserted) tbody.appendChild(tr);

        const sel = document.getElementById('selectJour');
        if (sel) {
            const opt = sel.querySelector(`option[value="${json.jour_semaine}"]`);
            if (opt) opt.remove();
            if (sel.options.length === 0) formDispoCreer.style.display = 'none';
        }
        this.reset();
        this.classList.remove('was-validated');
    });
}

function creerLigneDispo(d) {
    const tr = document.createElement('tr');
    tr.dataset.id   = d.id;
    tr.dataset.jour = d.jour_semaine;
    const label = d.jour_semaine.charAt(0).toUpperCase() + d.jour_semaine.slice(1);
    const h = v => String(v).substring(0, 5);
    tr.innerHTML = `
        <td>${esc(label)}</td>
        <td><input type="time" name="heure_debut" class="form-control form-control-sm" value="${h(d.heure_debut)}" required></td>
        <td><input type="time" name="heure_fin"   class="form-control form-control-sm" value="${h(d.heure_fin)}"   required></td>
        <td>
            <select name="actif" class="form-select form-select-sm">
                <option value="ouvert" ${d.actif==='ouvert'?'selected':''}>Ouvert</option>
                <option value="ferme"  ${d.actif==='ferme' ?'selected':''}>Fermé</option>
            </select>
        </td>
        <td class="d-flex gap-1">
            <button class="btn btn-sm btn-primary btn-save-dispo">✔</button>
            <button class="btn btn-sm btn-danger  btn-del-dispo">✖</button>
        </td>`;
    return tr;
}

const tbodyDispos = document.getElementById('tbodyDispos');
if (tbodyDispos) {
    tbodyDispos.addEventListener('click', async function(e) {
        const tr = e.target.closest('tr');
        if (!tr) return;
        const id   = tr.dataset.id;
        const jour = tr.dataset.jour;

        if (e.target.closest('.btn-save-dispo')) {
            e.preventDefault();
            const fd = new FormData();
            fd.append('action',       'dispo_modifier');
            fd.append('id',           id);
            fd.append('jour_semaine', jour);
            fd.append('heure_debut',  tr.querySelector('[name=heure_debut]').value);
            fd.append('heure_fin',    tr.querySelector('[name=heure_fin]').value);
            fd.append('actif',        tr.querySelector('[name=actif]').value);
            const json = await ajaxPost(fd);
            if (json.ok) flashGreen(tr);
        }

        if (e.target.closest('.btn-del-dispo')) {
            e.preventDefault();
            if (!confirm('Supprimer cette disponibilité ?')) return;
            const fd = new FormData();
            fd.append('action', 'dispo_supprimer');
            fd.append('id', id);
            const json = await ajaxPost(fd);
            if (json.ok) {
                const sel = document.getElementById('selectJour');
                if (sel && jour) {
                    const opt    = document.createElement('option');
                    opt.value    = jour;
                    opt.textContent = jour.charAt(0).toUpperCase() + jour.slice(1);
                    const jourIdx   = ordreJours.indexOf(jour);
                    const opts      = Array.from(sel.options);
                    let ins         = false;
                    for (const o of opts) {
                        if (ordreJours.indexOf(o.value) > jourIdx) {
                            sel.insertBefore(opt, o); ins = true; break;
                        }
                    }
                    if (!ins) sel.appendChild(opt);
                    formDispoCreer.style.display = '';
                }
                tr.remove();
            }
        }
    });
}
</script>
</body>
</html>