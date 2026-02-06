<?php
require_once '../config/init.php';

if (!isAdmin()) {
    redirect('../connexion.php');
}

$pageTitle = 'Gestion des commandes - ' . SITE_NAME;

if (isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE commandes SET statut = ? WHERE id = ?");
    $stmt->execute([$_POST['statut'], $_POST['commande_id']]);
    redirect('commandes.php');
}

$stmt = $pdo->query("SELECT c.*, u.nom, u.prenom, u.email FROM commandes c JOIN users u ON c.user_id = u.id ORDER BY c.date_commande DESC");
$commandes = $stmt->fetchAll();
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
                <h1 class="mb-4">Gestion des commandes</h1>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>N°</th>
                                        <th>Client</th>
                                        <th>Email</th>
                                        <th>Total</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($commandes as $commande): ?>
                                    <tr>
                                        <td>#<?= $commande['id'] ?></td>
                                        <td><?= htmlspecialchars($commande['prenom'] . ' ' . $commande['nom']) ?></td>
                                        <td><?= htmlspecialchars($commande['email']) ?></td>
                                        <td><?= number_format($commande['total'], 2, ',', ' ') ?> €</td>
                                        <td><?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">
                                                <input type="hidden" name="update_status" value="1">
                                                <select name="statut" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="en_attente" <?= $commande['statut'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                                    <option value="validee" <?= $commande['statut'] == 'validee' ? 'selected' : '' ?>>Validée</option>
                                                    <option value="expediee" <?= $commande['statut'] == 'expediee' ? 'selected' : '' ?>>Expédiée</option>
                                                    <option value="livree" <?= $commande['statut'] == 'livree' ? 'selected' : '' ?>>Livrée</option>
                                                    <option value="annulee" <?= $commande['statut'] == 'annulee' ? 'selected' : '' ?>>Annulée</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <a href="commande-detail.php?id=<?= $commande['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Détails
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
