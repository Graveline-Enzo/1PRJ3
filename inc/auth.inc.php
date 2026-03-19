<?php
/* =============================================================
   inc/auth.inc.php — Protection des pages réservées aux admins
============================================================= */

if (empty($_SESSION['membre'])) {
    $_SESSION['redirect_apres_connexion'] = $_SERVER['REQUEST_URI'];
    header('Location: connexion.php');
    exit();
}