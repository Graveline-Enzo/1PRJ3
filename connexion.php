<?php
require_once 'inc/init.inc.php';
require_once 'inc/fonction.inc.php';

if (!empty($_SESSION['membre'])) {
    header('Location: index.php');
    exit();
}

$pageTitle = 'Connexion – ' . SALON_NOM;
$erreur    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = trim($_POST['pseudo'] ?? '');
    $mdp    = trim($_POST['mdp']    ?? '');

    if (empty($pseudo) || empty($mdp)) {
        $erreur = "Veuillez remplir tous les champs.";
    } elseif ($pseudo !== ADMIN_PSEUDO || !password_verify($mdp, ADMIN_MDP_HASH)) {
        $erreur = "Pseudo ou mot de passe incorrect.";
    } else {
        session_regenerate_id(true);
        $_SESSION['membre'] = ['pseudo' => $pseudo];
        $redirect = $_SESSION['redirect_apres_connexion'] ?? 'index.php';
        unset($_SESSION['redirect_apres_connexion']);
        header('Location: ' . $redirect);
        exit();
    }
}

require_once 'inc/haut.inc.php';
?>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-sm-10">
            <div class="resa-card mt-4">

                <div class="text-center mb-4">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center
                                bg-warning rounded-3 text-dark"
                         style="width:56px;height:56px;font-size:1.6rem;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h2 class="resa-title">Connexion Admin</h2>
                    <p class="text-muted" style="font-size:.9rem;">Accès réservé à l'équipe du salon.</p>
                </div>

                <?php if ($erreur): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <?= propre($erreur) ?>
                    </div>
                <?php endif; ?>

                <form action="connexion.php" method="POST" novalidate id="loginForm">
                    <div class="mb-3">
                        <label for="pseudo" class="form-label">Pseudo</label>
                        <input type="text" class="form-control"
                               id="pseudo" name="pseudo"
                               value="<?= propre($_POST['pseudo'] ?? '') ?>"
                               placeholder="Votre pseudo" required maxlength="100"
                               autocomplete="username">
                        <div class="invalid-feedback">Veuillez entrer votre pseudo.</div>
                    </div>

                    <div class="mb-4">
                        <label for="mdp" class="form-label">Mot de passe</label>
                        <div class="input-group">
                            <input type="password" class="form-control"
                                   id="mdp" name="mdp"
                                   placeholder="Votre mot de passe"
                                   required maxlength="255" autocomplete="current-password">
                            <button class="btn btn-outline-secondary" type="button"
                                    id="toggleMdp" tabindex="-1">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning text-dark w-100 btn-lg fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Se connecter
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="index.php">
                        <i class="bi bi-arrow-left"></i> Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const form = document.getElementById('loginForm');
form.addEventListener('submit', e => {
    if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    form.classList.add('was-validated');
});
document.getElementById('toggleMdp').addEventListener('click', () => {
    const input = document.getElementById('mdp');
    const icon  = document.getElementById('eyeIcon');
    const show  = input.type === 'password';
    input.type     = show ? 'text'          : 'password';
    icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
});
</script>

<?php require_once 'inc/bas.inc.php'; ?>