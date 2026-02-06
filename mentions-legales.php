<?php
require_once 'config/init.php';

$pageTitle = 'Mentions légales - ' . SITE_NAME;

require_once 'includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Mentions légales</h1>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Éditeur du site</h5>
            <p>
                <?= SITE_NAME ?><br>
                123 Rue du Commerce<br>
                75001 Paris, France<br>
                Téléphone : 01 23 45 67 89<br>
                Email : contact@monshop.com
            </p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Directeur de la publication</h5>
            <p>M. Jean Dupont</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Hébergement</h5>
            <p>
                Ce site est hébergé par :<br>
                Hébergeur SA<br>
                456 Avenue de l'Internet<br>
                75002 Paris, France
            </p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Propriété intellectuelle</h5>
            <p>
                L'ensemble de ce site relève de la législation française et internationale sur le droit d'auteur et la propriété intellectuelle. Tous les droits de reproduction sont réservés, y compris pour les documents téléchargeables et les représentations iconographiques et photographiques.
            </p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Données personnelles</h5>
            <p>
                Conformément à la loi « Informatique et Libertés » du 6 janvier 1978 modifiée et au Règlement Général sur la Protection des Données (RGPD), vous disposez d'un droit d'accès, de rectification et de suppression des données vous concernant. Pour exercer ce droit, veuillez nous contacter par email.
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
