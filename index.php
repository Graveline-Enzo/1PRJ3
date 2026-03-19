<?php 

require_once 'inc/init.inc.php';
// ------- TRAITEMENT PHP

// ------- AFFICHAGE HTML
require_once 'inc/haut.inc.php';

?>
<main>

<div class="salon-presentation position-relative d-flex align-items-center" style="min-height: 100vh; background-image: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)), url('./ressource/salon-background.jpg'); background-size: cover; background-position: center;">
    <div class="salon-content px-5 text-white" style="max-width: 700px;">
        <h2 class="display-1 fw-black text-white mb-4">Hair it</h2>
        <p class="salon-desc fs-5 lh-lg mb-4">Depuis plus de 15 ans, nous sublimions votre beauté naturelle avec passion et expertise. Notre équipe de coiffeurs professionnels vous accueille dans un cadre élégant et chaleureux pour une expérience unique.</p>
        <div class="d-flex gap-3 mb-5 flex-wrap">
            <a href="" class="btn-rdv btn rounded-pill px-4 py-3 fw-bold text-white" style="background-color: var(--orange);">Prendre rendez-vous</a>
            <a href="d" class="btn btn-outline-light rounded-pill px-4 py-3 fw-bold">Découvrir nos services</a>
        </div>
        <div class="d-flex gap-5 pt-3 border-top border-secondary">
            <div class="d-flex align-items-center gap-3">
                <div>
                    <strong class="fs-4 d-block">15+</strong>
                    <p class="mb-0 small text-white-50">Années d'expérience</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div>
                    <strong class="fs-4 d-block">5000+</strong>
                    <p class="mb-0 small text-white-50">Clients satisfaits</p>
                </div>
            </div>
        </div>
    </div>
</div>

</main>
<?php
require_once 'inc/bas.inc.php';
?>