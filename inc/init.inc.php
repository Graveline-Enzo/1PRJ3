<?php

// -------------------- BDD
// Connexion à la BDD
$host = 'mysql:host=localhost;dbname=hair_it;';
$login = 'root';
$password = '';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
];

try
{
    $pdo = new PDO($host, $login, $password, $options);
}
catch(PDOExeption $e)
{
    die("🔴Un problème est survenu lors de la tentative de connexion à la base données : " . $e->getMessage());
}


// -------------------- SESSION
// Démmarage de la session
session_start();

// -------------------- CHEMIN
// Création de la constante
define("RACINE_SITE", "1PRJ3/");

// -------------------- VARIABLES
// Initialisation de la variable contenue vide pour éviter les erreurs
$contenu = '';

// -------------------- AUTRES
// Ici on inclus le fichier des fonctions
require_once('fonction.inc.php');


?>