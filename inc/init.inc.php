<?php
/* =============================================================
   inc/init.inc.php — Initialisation globale du projet
============================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SALON_NOM',     'Hair IT');
define('SALON_ADRESSE', '12 rue des Lilas, 75011 Paris');
define('SALON_TEL',     '01 23 45 67 89');
define('SALON_EMAIL',   'contact@hair-it.fr');
define('SALON_ANNEE',   date('Y'));

define('DB_HOST', 'localhost');
define('DB_NAME', 'hair_it');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHAR', 'utf8mb4');

define('ADMIN_PSEUDO',   'admin');
define('ADMIN_MDP_HASH', '$2y$10$/nAeV/1sRM36Y/X5ro2iWe0mz6qNVzj4xn81h06totgNJdT1pWAsW');

date_default_timezone_set('Europe/Paris');
ini_set('display_errors', 1);
error_reporting(E_ALL);