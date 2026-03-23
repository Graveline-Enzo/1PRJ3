<?php
function propre(string $val): string
{
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

function post(string $champ): string
{
    return isset($_POST[$champ]) ? trim($_POST[$champ]) : '';
}

function get(string $champ): string
{
    return isset($_GET[$champ]) ? trim($_GET[$champ]) : '';
}

function validerNom(string $nom): bool
{
    $nom = trim($nom);
    return mb_strlen($nom) >= 2 && mb_strlen($nom) <= 100;
}

function validerEmail(string $email): bool
{
    return (bool) filter_var(trim($email), FILTER_VALIDATE_EMAIL);
}

function validerMessage(string $msg): bool
{
    $len = mb_strlen(trim($msg));
    return $len >= 10 && $len <= 1000;
}

function getDB(): ?PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR;
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        error_log('[DB] ' . $e->getMessage());
        return null;
    }
    return $pdo;
}

function getServices(): array
{
    $pdo = getDB();
    if (!$pdo) return [];
    try {
        return $pdo->query("SELECT * FROM services ORDER BY nom ASC")->fetchAll();
    } catch (PDOException $e) {
        error_log('[Services] ' . $e->getMessage());
        return [];
    }
}

function getService(int $id): ?array
{
    $pdo = getDB();
    if (!$pdo) return null;
    try {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        error_log('[Service] ' . $e->getMessage());
        return null;
    }
}

function creerService(string $nom, string $desc, int $duree, int $prix): int|false
{
    $pdo = getDB();
    if (!$pdo) return false;
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO services (nom, description, duree_minutes, prix_euros)
             VALUES (:nom, :desc, :duree, :prix)"
        );
        $stmt->execute([':nom' => $nom, ':desc' => $desc, ':duree' => $duree, ':prix' => $prix]);
        return (int) $pdo->lastInsertId(); // ← retourne l'id généré
    } catch (PDOException $e) {
        error_log('[creerService] ' . $e->getMessage());
        return false;
    }
}

function modifierService(int $id, string $nom, string $desc, int $duree, int $prix): bool
{
    $pdo = getDB();
    if (!$pdo) return false;
    try {
        $stmt = $pdo->prepare(
            "UPDATE services SET nom=:nom, description=:desc, duree_minutes=:duree, prix_euros=:prix
             WHERE id=:id"
        );
        return $stmt->execute([':nom' => $nom, ':desc' => $desc, ':duree' => $duree, ':prix' => $prix, ':id' => $id]);
    } catch (PDOException $e) {
        error_log('[modifierService] ' . $e->getMessage());
        return false;
    }
}

function supprimerService(int $id): bool
{
    $pdo = getDB();
    if (!$pdo) return false;
    try {
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        error_log('[supprimerService] ' . $e->getMessage());
        return false;
    }
}

function getDisponibilites(): array
{
    $pdo = getDB();
    if (!$pdo) return [];
    $ordre = ['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'];
    try {
        $rows = $pdo->query("SELECT * FROM disponibilites ORDER BY id ASC")->fetchAll();
        usort($rows, fn($a, $b) =>
            array_search($a['jour_semaine'], $ordre) <=> array_search($b['jour_semaine'], $ordre)
        );
        return $rows;
    } catch (PDOException $e) {
        error_log('[Dispo] ' . $e->getMessage());
        return [];
    }
}

function creerDisponibilite(string $jour, string $debut, string $fin, string $actif): int|false
{
    $pdo = getDB();
    if (!$pdo) return false;
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO disponibilites (jour_semaine, heure_debut, heure_fin, actif)
             VALUES (:jour, :debut, :fin, :actif)"
        );
        $stmt->execute([':jour' => $jour, ':debut' => $debut, ':fin' => $fin, ':actif' => $actif]);
        return (int) $pdo->lastInsertId(); // ← retourne l'id généré
    } catch (PDOException $e) {
        error_log('[creerDispo] ' . $e->getMessage());
        return false;
    }
}

function modifierDisponibilite(int $id, string $jour, string $debut, string $fin, string $actif): bool
{
    $pdo = getDB();
    if (!$pdo) return false;
    try {
        $stmt = $pdo->prepare(
            "UPDATE disponibilites SET jour_semaine=:jour, heure_debut=:debut, heure_fin=:fin, actif=:actif
             WHERE id=:id"
        );
        return $stmt->execute([':jour' => $jour, ':debut' => $debut, ':fin' => $fin, ':actif' => $actif, ':id' => $id]);
    } catch (PDOException $e) {
        error_log('[modifierDispo] ' . $e->getMessage());
        return false;
    }
}

function supprimerDisponibilite(int $id): bool
{
    $pdo = getDB();
    if (!$pdo) return false;
    try {
        $stmt = $pdo->prepare("DELETE FROM disponibilites WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        error_log('[supprimerDispo] ' . $e->getMessage());
        return false;
    }
}

function getReservations(): array
{
    $pdo = getDB();
    if (!$pdo) return [];
    try {
        return $pdo->query(
            "SELECT r.*, s.nom AS service_nom
             FROM reservations r
             LEFT JOIN services s ON r.service_id = s.id
             ORDER BY r.date_rdv DESC, r.heure_rdv DESC"
        )->fetchAll();
    } catch (PDOException $e) {
        error_log('[Reservations] ' . $e->getMessage());
        return [];
    }
}

function modifierStatutReservation(int $id, string $statut): bool
{
    $valides = ['en_attente', 'confirme', 'annule'];
    if (!in_array($statut, $valides)) return false;

    $pdo = getDB();
    if (!$pdo) return false;
    try {
        $stmt = $pdo->prepare("UPDATE reservations SET statut=:statut WHERE id=:id");
        return $stmt->execute([':statut' => $statut, ':id' => $id]);
    } catch (PDOException $e) {
        error_log('[modifierStatut] ' . $e->getMessage());
        return false;
    }
}

function supprimerReservation(int $id): bool
{
    $pdo = getDB();
    if (!$pdo) return false;
    try {
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        error_log('[supprimerResa] ' . $e->getMessage());
        return false;
    }
}

function sauvegarderContact(string $nom, string $email, string $message): bool
{
    error_log("[Contact] De: $nom <$email> — $message");
    return true;
}