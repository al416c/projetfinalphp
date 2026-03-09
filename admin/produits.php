<?php
require_once '../config/init.php';

if (!isAdmin()) {
    redirect('../connexion.php');
}

$pageTitle = 'Gestion des articles';

// Delete article
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    // Delete related data first
    $pdo->prepare("DELETE FROM facture_details WHERE article_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM commentaires WHERE article_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM favoris WHERE article_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM panier WHERE article_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM stock WHERE article_id = ?")->execute([$id]);
    
    // Get image to delete
    $stmt = $pdo->prepare("SELECT image FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();
    if ($article && $article['image']) {
        $imgPath = UPLOAD_DIR . 'produits/' . $article['image'];
        if (file_exists($imgPath)) unlink($imgPath);
    }
    
    $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);
    setFlash('success', 'Article supprimé avec succès.');
    redirect('produits.php');
}

// Get all articles
$articles = $pdo->query("
    SELECT a.*, c.nom AS categorie_nom, u.username AS auteur_nom, COALESCE(s.quantite, 0) AS stock
    FROM articles a
    LEFT JOIN categories c ON a.categorie_id = c.id
    LEFT JOIN users u ON a.auteur_id = u.id
    LEFT JOIN stock s ON a.id = s.article_id
    ORDER BY a.date_creation DESC
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
            <a href="<?= SITE_URL ?>/admin/produits.php" class="admin-nav-link active">
                <i class="bi bi-box-seam"></i> Articles
            </a>
            <a href="<?= SITE_URL ?>/admin/categories.php" class="admin-nav-link">
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
            <h1>Articles</h1>
            <a href="<?= SITE_URL ?>/admin/produit-form.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nouvel article
            </a>
        </div>

        <div class="admin-card fade-in">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Vendeur</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td>
                                <?php if ($article['image']): ?>
                                    <img src="<?= SITE_URL ?>/uploads/produits/<?= $article['image'] ?>" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                                <?php else: ?>
                                    <div style="width:50px;height:50px;background:#f5f5f7;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                        <i class="bi bi-image" style="color:#86868b;"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= sanitize($article['nom']) ?></strong></td>
                            <td><?= sanitize($article['categorie_nom'] ?? 'N/A') ?></td>
                            <td><?= sanitize($article['auteur_nom'] ?? 'N/A') ?></td>
                            <td><?= formatPrice($article['prix']) ?></td>
                            <td>
                                <span class="badge <?= $article['stock'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $article['stock'] ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($article['date_creation'])) ?></td>
                            <td>
                                <div style="display:flex;gap:0.5rem;">
                                    <a href="<?= SITE_URL ?>/admin/produit-form.php?id=<?= $article['id'] ?>" class="btn-small" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= SITE_URL ?>/admin/produits.php?delete=<?= $article['id'] ?>" class="btn-small btn-danger" onclick="return confirm('Supprimer cet article ?')" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (empty($articles)): ?>
                <p style="text-align:center;padding:2rem;color:#86868b;">Aucun article pour le moment.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
