<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./inc/css/style.css">
    <title><?= isset($pageTitle) ? propre($pageTitle) : SALON_NOM ?></title>
</head>
<body>

<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <div class="container-fluid">

            <div class="logo-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="6" cy="6" r="3"/><path d="M8.12 8.12 12 12"/>
                    <path d="M20 4 8.12 15.88"/><circle cx="6" cy="18" r="3"/>
                    <path d="M14.8 14.8 20 20"/>
                </svg>
                <a class="navbar-brand" href="index.php"><?= SALON_NOM ?></a>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#horaires">Horaires</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <a href="reservation.php" class="btn btn-outline-success">Réserver</a>

                    <?php if (!empty($_SESSION['membre'])): ?>
                        <a href="admin.php" class="btn btn-warning text-dark fw-semibold">
                            <i class="bi bi-shield-lock-fill me-1"></i>Admin
                        </a>
                    <?php else: ?>
                        <a href="connexion.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-person-lock me-1"></i>Admin
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </nav>
</header>