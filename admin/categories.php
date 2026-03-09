<?php
require_once '../config/init.php';

if (!isAdmin()) {
    redirect('../connexion.php');
}

$pageTitle = 'Gestion des catégories';

// Delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    // Check if category has articles
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE categorie_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        setFlash('error', 'Impossible de supprimer : cette catégorie contient des articles.');
    } else {
        // Delete image
        $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $cat = $stmt->fetch();
        if ($cat && $cat['image']) {
            $imgPath = UPLOAD_DIR . 'categories/' . $cat['image'];
            if (file_exists($imgPath)) unlink($imgPath);
        }
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        setFlash('success', 'Catégorie supprimée.');
    }
    redirect('categories.php');
}

$categories = $pdo->query("
    SELECT c.*, (SELECT COUNT(*) FROM articles WHERE categorie_id = c.id) AS article_count
    FROM categories c
    ORDER BY c.nom
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
            <a href="<?= SITE_URL ?>/admin/categories.php" class="admin-nav-link active">
                <i class="bi bi-grid"></i> Catégories
            </a>
            <a href="<?= SITE_URL ?>/admin/commandes.php" class="admin-nav-link">
                <i class="bi bi-receipt"></i> Factures
            </a>
            <a href="<?= SITE_URL ?>/admin/utilisateurs.php" class="admin-nav-link">
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
            <h1>Catégories</h1>
            <a href="<?= SITE_URL ?>/admin/categorie-form.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nouvelle catégorie
            </a>
        </div>

        <div class="admin-card fade-in">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Articles</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <?php if ($cat['image']): ?>
                                    <img src="<?= SITE_URL ?>/uploads/categories/<?= $cat['image'] ?>" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                                <?php else: ?>
                                    <div style="width:50px;height:50px;background:#f5f5f7;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                        <i class="bi bi-grid" style="color:#86868b;"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= sanitize($cat['nom']) ?></strong></td>
                            <td><?= sanitize(substr($cat['description'] ?? '', 0, 80)) ?>...</td>
                            <td>
                                <span class="badge badge-default"><?= $cat['article_count'] ?></span>
                            </td>
                            <td>
                                <div style="display:flex;gap:0.5rem;">
                                    <a href="<?= SITE_URL ?>/admin/categorie-form.php?id=<?= $cat['id'] ?>" class="btn-small" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= SITE_URL ?>/admin/categories.php?delete=<?= $cat['id'] ?>" class="btn-small btn-danger" onclick="return confirm('Supprimer cette catégorie ?')" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (empty($categories)): ?>
                <p style="text-align:center;padding:2rem;color:#86868b;">Aucune catégorie.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
