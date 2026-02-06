<?php
require_once 'config/init.php';

if (!isLoggedIn() || !isset($_GET['id'])) {
    redirect('index.php');
}

$stmt = $pdo->prepare("SELECT * FROM commandes WHERE id = ? AND user_id = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$commande = $stmt->fetch();

if (!$commande) {
    redirect('index.php');
}

$pageTitle = 'Confirmation de commande - ' . SITE_NAME;

require_once 'includes/header.php';
?>

<div class="container">
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
        </div>
        <h1>Merci pour votre commande !</h1>
        <p class="lead">Votre commande n°<?= $commande['id'] ?> a bien été enregistrée.</p>
        <p class="text-muted">Un email de confirmation vous sera envoyé prochainement.</p>
        
        <div class="card mx-auto mt-4" style="max-width: 400px;">
            <div class="card-body">
                <h5>Détails de la commande</h5>
                <p class="mb-1"><strong>Numéro :</strong> #<?= $commande['id'] ?></p>
                <p class="mb-1"><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></p>
                <p class="mb-1"><strong>Total :</strong> <?= number_format($commande['total'], 2, ',', ' ') ?> €</p>
                <p class="mb-0"><strong>Statut :</strong> <span class="badge bg-warning">En attente</span></p>
            </div>
        </div>

        <div class="mt-4">
            <a href="commandes.php" class="btn btn-primary">Voir mes commandes</a>
            <a href="produits.php" class="btn btn-outline-primary">Continuer mes achats</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
