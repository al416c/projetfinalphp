<?php
require_once '../config/init.php';

if (!isAdmin()) {
    redirect('../connexion.php');
}

if (!isset($_GET['id'])) {
    redirect('commandes.php');
}

$stmt = $pdo->prepare("SELECT c.*, u.nom, u.prenom, u.email, u.telephone FROM commandes c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
$stmt->execute([$_GET['id']]);
$commande = $stmt->fetch();

if (!$commande) {
    redirect('commandes.php');
}

$stmt = $pdo->prepare("SELECT cd.*, p.nom, p.image FROM commande_details cd JOIN produits p ON cd.produit_id = p.id WHERE cd.commande_id = ?");
$stmt->execute([$commande['id']]);
$details = $stmt->fetchAll();

$pageTitle = 'Commande #' . $commande['id'] . ' - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= SITE_URL ?>/admin/">
                <i class="fas fa-cog"></i> Admin - <?= SITE_NAME ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= SITE_URL ?>"><i class="fas fa-external-link-alt"></i> Voir le site</a>
                <a class="nav-link" href="<?= SITE_URL ?>/deconnexion.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 admin-sidebar py-3">
                <nav class="nav flex-column">
                    <a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt me-2"></i>Tableau de bord</a>
                    <a class="nav-link" href="produits.php"><i class="fas fa-box me-2"></i>Produits</a>
                    <a class="nav-link" href="categories.php"><i class="fas fa-tags me-2"></i>Catégories</a>
                    <a class="nav-link active" href="commandes.php"><i class="fas fa-shopping-cart me-2"></i>Commandes</a>
                    <a class="nav-link" href="utilisateurs.php"><i class="fas fa-users me-2"></i>Utilisateurs</a>
                </nav>
            </div>

            <div class="col-md-10 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Commande #<?= $commande['id'] ?></h1>
                    <a href="commandes.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Produits commandés</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Produit</th>
                                                <th>Prix unitaire</th>
                                                <th>Quantité</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($details as $detail): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($detail['image']): ?>
                                                            <img src="<?= SITE_URL ?>/uploads/produits/<?= $detail['image'] ?>" width="40" height="40" class="rounded me-2" style="object-fit:cover;">
                                                        <?php endif; ?>
                                                        <?= htmlspecialchars($detail['nom']) ?>
                                                    </div>
                                                </td>
                                                <td><?= number_format($detail['prix_unitaire'], 2, ',', ' ') ?> €</td>
                                                <td><?= $detail['quantite'] ?></td>
                                                <td><?= number_format($detail['prix_unitaire'] * $detail['quantite'], 2, ',', ' ') ?> €</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Total</th>
                                                <th><?= number_format($commande['total'], 2, ',', ' ') ?> €</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Informations client</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Nom :</strong> <?= htmlspecialchars($commande['prenom'] . ' ' . $commande['nom']) ?></p>
                                <p class="mb-1"><strong>Email :</strong> <?= htmlspecialchars($commande['email']) ?></p>
                                <p class="mb-0"><strong>Téléphone :</strong> <?= htmlspecialchars($commande['telephone'] ?? 'Non renseigné') ?></p>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Livraison</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-0"><?= nl2br(htmlspecialchars($commande['adresse_livraison'])) ?></p>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Statut</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></p>
                                <form method="POST" action="commandes.php">
                                    <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">
                                    <input type="hidden" name="update_status" value="1">
                                    <select name="statut" class="form-select mb-2">
                                        <option value="en_attente" <?= $commande['statut'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                        <option value="validee" <?= $commande['statut'] == 'validee' ? 'selected' : '' ?>>Validée</option>
                                        <option value="expediee" <?= $commande['statut'] == 'expediee' ? 'selected' : '' ?>>Expédiée</option>
                                        <option value="livree" <?= $commande['statut'] == 'livree' ? 'selected' : '' ?>>Livrée</option>
                                        <option value="annulee" <?= $commande['statut'] == 'annulee' ? 'selected' : '' ?>>Annulée</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary w-100">Mettre à jour</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
