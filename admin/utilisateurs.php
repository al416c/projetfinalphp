<?php
require_once '../config/init.php';

if (!isAdmin()) {
    redirect('../connexion.php');
}

$pageTitle = 'Gestion des utilisateurs - ' . SITE_NAME;

if (isset($_GET['delete']) && $_GET['delete'] != $_SESSION['user_id']) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    redirect('utilisateurs.php');
}

$stmt = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM commandes WHERE user_id = u.id) as nb_commandes FROM users u ORDER BY u.date_inscription DESC");
$utilisateurs = $stmt->fetchAll();
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
                    <a class="nav-link" href="commandes.php"><i class="fas fa-shopping-cart me-2"></i>Commandes</a>
                    <a class="nav-link active" href="utilisateurs.php"><i class="fas fa-users me-2"></i>Utilisateurs</a>
                </nav>
            </div>

            <div class="col-md-10 py-4">
                <h1 class="mb-4">Gestion des utilisateurs</h1>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Commandes</th>
                                        <th>Inscription</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($utilisateurs as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $user['role'] == 'admin' ? 'danger' : 'primary' ?>">
                                                <?= $user['role'] ?>
                                            </span>
                                        </td>
                                        <td><span class="badge bg-secondary"><?= $user['nb_commandes'] ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($user['date_inscription'])) ?></td>
                                        <td>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="utilisateurs.php?delete=<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cet utilisateur ?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
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
