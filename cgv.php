<?php
require_once 'config/init.php';

$pageTitle = 'Conditions Générales de Vente - ' . SITE_NAME;

require_once 'includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Conditions Générales de Vente</h1>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Article 1 - Objet</h5>
            <p>
                Les présentes conditions générales de vente régissent les relations contractuelles entre <?= SITE_NAME ?> et le client, les deux parties les acceptant sans réserve.
            </p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Article 2 - Prix</h5>
            <p>
                Les prix de nos produits sont indiqués en euros toutes taxes comprises (TTC). <?= SITE_NAME ?> se réserve le droit de modifier ses prix à tout moment, étant entendu que le prix figurant sur le site le jour de la commande sera le seul applicable à l'acheteur.
            </p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Article 3 - Commande</h5>
            <p>
                L'acheteur passe sa commande en ligne sur le site. Pour que la commande soit validée, l'acheteur devra accepter les présentes conditions générales de vente. Le paiement de la commande vaut acceptation des présentes CGV.
            </p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Article 4 - Livraison</h5>
            <p>
                Les livraisons sont faites à l'adresse indiquée lors de la commande. Les délais de livraison sont donnés à titre indicatif. Un retard ne peut donner lieu à aucune retenue, ni annulation de la commande. Livraison gratuite à partir de 50€ d'achat.
            </p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Article 5 - Retour et remboursement</h5>
            <p>
                Conformément à la législation en vigueur, vous disposez d'un délai de 14 jours à compter de la réception de votre commande pour exercer votre droit de rétractation sans avoir à justifier de motifs ni à payer de pénalités.
            </p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Article 6 - Garantie</h5>
            <p>
                Tous nos produits bénéficient de la garantie légale de conformité et de la garantie contre les vices cachés prévues par le Code Civil.
            </p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Article 7 - Responsabilité</h5>
            <p>
                <?= SITE_NAME ?> ne saurait être tenu pour responsable des dommages résultant d'une mauvaise utilisation du produit acheté.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Article 8 - Litiges</h5>
            <p>
                Les présentes conditions de vente sont soumises à la loi française. En cas de litige, les tribunaux français seront seuls compétents.
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
