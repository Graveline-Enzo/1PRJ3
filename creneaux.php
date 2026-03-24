<?php
require_once 'inc/init.inc.php';
require_once 'inc/fonction.inc.php';

header('Content-Type: application/json');

$date  = $_GET['date']  ?? '';
$duree = (int)($_GET['duree'] ?? 0);

if (!$date || !$duree || !strtotime($date) || strtotime($date) < strtotime('today')) {
    echo json_encode([]);
    exit();
}

echo json_encode(getCreneauxDisponibles($date, $duree));