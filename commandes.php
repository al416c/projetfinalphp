<?php
require_once 'config/init.php';

if (!isLoggedIn()) {
    redirect('connexion.php');
}

$pageTitle = 'Mes commandes - ' . SITE_NAME;

$stmt = $pdo->prepare("SELECT * FROM commandes WHERE user_id = ? ORDER BY date_commande DESC");
$stmt->execute([$_SESSION['user_id']]);
$commandes = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Mes commandes</h1>

    <?php if (empty($commandes)): ?>
        <div class="alert alert-info">
            Vous n'avez pas encore de commandes. <a href="produits.php">Découvrir nos produits</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>N° Commande</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $commande): ?>
                    <tr>
                        <td>#<?= $commande['id'] ?></td>
                        <td><?= date('d/m/Y', strtotime($commande['date_commande'])) ?></td>
                        <td><?= number_format($commande['total'], 2, ',', ' ') ?> €</td>
                        <td>
                            <?php
                            $statusClass = [
                                'en_attente' => 'warning',
                                'validee' => 'info',
                                'expediee' => 'primary',
                                'livree' => 'success',
                                'annulee' => 'danger'
                            ];
                            $statusText = [
                                'en_attente' => 'En attente',
                                'validee' => 'Validée',
                                'expediee' => 'Expédiée',
                                'livree' => 'Livrée',
                                'annulee' => 'Annulée'
                            ];
                            ?>
                            <span class="badge bg-<?= $statusClass[$commande['statut']] ?>">
                                <?= $statusText[$commande['statut']] ?>
                            </span>
                        </td>
                        <td>
                            <a href="commande-detail.php?id=<?= $commande['id'] ?>" class="btn btn-sm btn-outline-primary">
                                Détails
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
