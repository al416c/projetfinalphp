<?php
require_once 'config/init.php';
$pageTitle = 'Mentions Légales';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="static-page fade-in">
        <h1>Mentions Légales</h1>

        <h2>Éditeur du site</h2>
        <p><strong>NOVA — Premium Tech Marketplace</strong></p>
        <p>Projet réalisé dans le cadre d'un exercice académique.</p>

        <h2>Hébergement</h2>
        <p>Ce site est hébergé localement dans le cadre d'un projet de développement.</p>

        <h2>Propriété intellectuelle</h2>
        <p>L'ensemble du contenu de ce site (textes, images, graphismes, logo, icônes) est la propriété exclusive de NOVA, sauf mention contraire. Toute reproduction, représentation ou diffusion, en tout ou partie, du contenu de ce site est interdite.</p>

        <h2>Données personnelles</h2>
        <p>Conformément à la réglementation en vigueur, vous disposez d'un droit d'accès, de rectification et de suppression des données vous concernant. Pour exercer ce droit, contactez-nous via la page <a href="<?= SITE_URL ?>/contact.php">contact</a>.</p>

        <h2>Cookies</h2>
        <p>Ce site utilise des cookies de session nécessaires à son bon fonctionnement. Ces cookies ne collectent aucune donnée personnelle à des fins publicitaires.</p>

        <h2>Limitation de responsabilité</h2>
        <p>NOVA ne pourra être tenue responsable des dommages directs ou indirects causés au matériel de l'utilisateur lors de l'accès au site. NOVA décline toute responsabilité quant à l'utilisation qui pourrait être faite des informations et contenus présents sur le site.</p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
