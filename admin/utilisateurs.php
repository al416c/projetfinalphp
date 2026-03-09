<?php
require_once '../config/init.php';

if (!isAdmin()) {
    redirect('../connexion.php');
}

$pageTitle = 'Gestion des utilisateurs';

// Change role
if (isset($_GET['toggle_role'])) {
    $id = (int) $_GET['toggle_role'];
    if ($id !== $_SESSION['user_id']) { // Can't change own role
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if ($user) {
            $newRole = $user['role'] === 'admin' ? 'user' : 'admin';
            $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$newRole, $id]);
            setFlash('success', 'Rôle mis à jour.');
        }
    }
    redirect('utilisateurs.php');
}

// Delete user
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id !== $_SESSION['user_id']) { // Can't delete self
        // Delete user's data
        $pdo->prepare("DELETE FROM commentaires WHERE user_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM favoris WHERE user_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM panier WHERE user_id = ?")->execute([$id]);
        
        // Delete user's articles (and their related data)
        $articles = $pdo->prepare("SELECT id FROM articles WHERE auteur_id = ?");
        $articles->execute([$id]);
        foreach ($articles->fetchAll() as $art) {
            $pdo->prepare("DELETE FROM stock WHERE article_id = ?")->execute([$art['id']]);
            $pdo->prepare("DELETE FROM commentaires WHERE article_id = ?")->execute([$art['id']]);
            $pdo->prepare("DELETE FROM favoris WHERE article_id = ?")->execute([$art['id']]);
            $pdo->prepare("DELETE FROM panier WHERE article_id = ?")->execute([$art['id']]);
        }
        $pdo->prepare("DELETE FROM articles WHERE auteur_id = ?")->execute([$id]);
        
        // Delete user photo
        $stmt = $pdo->prepare("SELECT photo FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $u = $stmt->fetch();
        if ($u && $u['photo']) {
            $path = UPLOAD_DIR . 'produits/' . $u['photo'];
            if (file_exists($path)) unlink($path);
        }
        
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        setFlash('success', 'Utilisateur supprimé.');
    } else {
        setFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
    }
    redirect('utilisateurs.php');
}

$users = $pdo->query("
    SELECT u.*, 
        (SELECT COUNT(*) FROM articles WHERE auteur_id = u.id) AS article_count,
        (SELECT COUNT(*) FROM factures WHERE user_id = u.id) AS facture_count
    FROM users u
    ORDER BY u.date_inscription DESC
")->fetchAll();

require_once '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h3><i class="bi bi-gear-fill"></i> Admin</h3>
        </div>
        <nav class="admin-nav">
            <a href="<?= SITE_URL ?>/admin/index.php" class="admin-nav-link">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
            <a href="<?= SITE_URL ?>/admin/produits.php" class="admin-nav-link">
                <i class="bi bi-box-seam"></i> Articles
            </a>
            <a href="<?= SITE_URL ?>/admin/categories.php" class="admin-nav-link">
                <i class="bi bi-grid"></i> Catégories
            </a>
            <a href="<?= SITE_URL ?>/admin/commandes.php" class="admin-nav-link">
                <i class="bi bi-receipt"></i> Factures
            </a>
            <a href="<?= SITE_URL ?>/admin/utilisateurs.php" class="admin-nav-link active">
                <i class="bi bi-people"></i> Utilisateurs
            </a>
            <hr>
            <a href="<?= SITE_URL ?>/index.php" class="admin-nav-link">
                <i class="bi bi-arrow-left"></i> Retour au site
            </a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Utilisateurs</h1>
        </div>

        <div class="admin-card fade-in">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Nom d'utilisateur</th>
                        <th>Email</th>
                        <th>Solde</th>
                        <th>Articles</th>
                        <th>Achats</th>
                        <th>Rôle</th>
                        <th>Inscrit le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <?php if ($user['photo']): ?>
                                    <img src="<?= SITE_URL ?>/uploads/produits/<?= $user['photo'] ?>" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:50%;">
                                <?php else: ?>
                                    <div style="width:40px;height:40px;background:var(--accent-gradient);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:600;font-size:0.875rem;">
                                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= sanitize($user['username']) ?></strong></td>
                            <td><?= sanitize($user['email']) ?></td>
                            <td><?= formatPrice($user['balance']) ?></td>
                            <td><span class="badge badge-default"><?= $user['article_count'] ?></span></td>
                            <td><span class="badge badge-default"><?= $user['facture_count'] ?></span></td>
                            <td>
                                <span class="badge <?= $user['role'] === 'admin' ? 'badge-primary' : 'badge-default' ?>">
                                    <?= $user['role'] ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($user['date_inscription'])) ?></td>
                            <td>
                                <div style="display:flex;gap:0.5rem;">
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                        <a href="<?= SITE_URL ?>/admin/utilisateurs.php?toggle_role=<?= $user['id'] ?>" class="btn-small" title="Changer le rôle" onclick="return confirm('Changer le rôle de cet utilisateur ?')">
                                            <i class="bi bi-shield"></i>
                                        </a>
                                        <a href="<?= SITE_URL ?>/admin/utilisateurs.php?delete=<?= $user['id'] ?>" class="btn-small btn-danger" onclick="return confirm('Supprimer cet utilisateur et toutes ses données ?')" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#86868b;font-size:0.75rem;">Vous</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
