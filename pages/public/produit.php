<?php
require_once 'config/init.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('produits.php');

// Fetch article
$stmt = $pdo->prepare("
    SELECT a.*, s.quantite as stock, u.username as auteur_nom, u.id as auteur_id, u.photo as auteur_photo,
        c.nom as categorie_nom, c.id as categorie_id
    FROM articles a
    LEFT JOIN stock s ON a.id = s.article_id
    LEFT JOIN users u ON a.auteur_id = u.id
    LEFT JOIN categories c ON a.categorie_id = c.id
    WHERE a.id = ?
");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    setFlash('error', 'Article introuvable.');
    redirect('produits.php');
}

$pageTitle = $article['nom'];
$rating = getAverageRating($id);
$isFav = isFavorited($id);

// Fetch reviews
$stmtReviews = $pdo->prepare("
    SELECT com.*, u.username, u.photo
    FROM commentaires com
    LEFT JOIN users u ON com.user_id = u.id
    WHERE com.article_id = ?
    ORDER BY com.date_commentaire DESC
");
$stmtReviews->execute([$id]);
$reviews = $stmtReviews->fetchAll();

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        setFlash('error', 'Connectez-vous pour laisser un avis.');
        redirect("produit.php?id=$id");
    }

    $note = max(1, min(5, (int)$_POST['note']));
    $commentaire = cleanInput($_POST['commentaire'] ?? '');

    // Check if user already reviewed
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM commentaires WHERE article_id = ? AND user_id = ?");
    $stmtCheck->execute([$id, $_SESSION['user_id']]);
    if ($stmtCheck->fetchColumn() > 0) {
        setFlash('error', 'Vous avez déjà laissé un avis sur cet article.');
    } else {
        $stmtInsert = $pdo->prepare("INSERT INTO commentaires (article_id, user_id, note, commentaire) VALUES (?, ?, ?, ?)");
        $stmtInsert->execute([$id, $_SESSION['user_id'], $note, $commentaire]);
        setFlash('success', 'Avis ajouté avec succès !');
    }
    redirect("produit.php?id=$id");
}

// Related articles
$stmtRelated = $pdo->prepare("
    SELECT a.*, s.quantite as stock, c.nom as categorie_nom,
        (SELECT AVG(note) FROM commentaires WHERE article_id = a.id) as avg_note
    FROM articles a
    LEFT JOIN stock s ON a.id = s.article_id
    LEFT JOIN categories c ON a.categorie_id = c.id
    WHERE a.categorie_id = ? AND a.id != ?
    ORDER BY RAND()
    LIMIT 4
");
$stmtRelated->execute([$article['categorie_id'], $id]);
$related = $stmtRelated->fetchAll();

// Can edit?
$canEdit = isLoggedIn() && ($_SESSION['user_id'] == $article['auteur_id'] || isAdmin());

require_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<div class="breadcrumb-bar">
    <div class="container-wide">
        <ul class="breadcrumb">
            <li><a href="index.php">Accueil</a></li>
            <li class="separator"><i class="bi bi-chevron-right"></i></li>
            <li><a href="produits.php">Articles</a></li>
            <?php if ($article['categorie_nom']): ?>
                <li class="separator"><i class="bi bi-chevron-right"></i></li>
                <li><a href="categorie.php?id=<?= $article['categorie_id'] ?>"><?= sanitize($article['categorie_nom']) ?></a></li>
            <?php endif; ?>
            <li class="separator"><i class="bi bi-chevron-right"></i></li>
            <li><?= sanitize($article['nom']) ?></li>
        </ul>
    </div>
</div>

<div class="container-wide">
    <div class="product-detail">
        <!-- Gallery -->
        <div class="product-gallery fade-in">
            <?php if ($article['image'] && file_exists("uploads/produits/{$article['image']}")): ?>
                <img src="uploads/produits/<?= sanitize($article['image']) ?>" alt="<?= sanitize($article['nom']) ?>">
            <?php else: ?>
                <div style="width: 100%; aspect-ratio: 1; background: var(--bg-secondary); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-box-seam" style="font-size: 80px; color: var(--text-tertiary);"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="product-info fade-in">
            <p class="eyebrow"><?= sanitize($article['categorie_nom'] ?? 'Article') ?></p>
            <h1><?= sanitize($article['nom']) ?></h1>

            <?php if ($rating['count'] > 0): ?>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                    <?= renderStars($rating['average']) ?>
                    <span class="caption"><?= $rating['average'] ?>/5 (<?= $rating['count'] ?> avis)</span>
                </div>
            <?php endif; ?>

            <p class="price"><?= formatPrice($article['prix']) ?></p>
            <p class="description"><?= nl2br(sanitize($article['description'])) ?></p>

            <!-- Stock -->
            <?php if ($article['stock'] > 10): ?>
                <span class="stock-badge stock-in"><i class="bi bi-check-circle-fill"></i> En stock</span>
            <?php elseif ($article['stock'] > 0): ?>
                <span class="stock-badge stock-low"><i class="bi bi-exclamation-circle-fill"></i> Plus que <?= $article['stock'] ?> en stock</span>
            <?php else: ?>
                <span class="stock-badge stock-out"><i class="bi bi-x-circle-fill"></i> Rupture de stock</span>
            <?php endif; ?>

            <!-- Seller info -->
            <a href="<?= SITE_URL ?>/profil.php?id=<?= $article['auteur_id'] ?>" class="seller-info seller-info-link" style="margin-top: 20px; text-decoration: none; color: inherit;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--gradient-1); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 16px; flex-shrink: 0;">
                    <?= strtoupper(substr($article['auteur_nom'], 0, 1)) ?>
                </div>
                <div>
                    <strong style="font-size: 15px;"><?= sanitize($article['auteur_nom']) ?></strong>
                    <p class="caption">Vendeur · Publié <?= timeAgo($article['date_publication']) ?></p>
                </div>
                <i class="bi bi-chevron-right" style="margin-left: auto; opacity: .4;"></i>
            </a>

            <!-- Actions -->
            <?php if ($article['stock'] > 0): ?>
            <div style="display: flex; align-items: center; gap: 12px; margin-top: 24px;">
                <div class="quantity-selector">
                    <button class="qty-minus" type="button">−</button>
                    <input type="number" id="qty-<?= $article['id'] ?>" value="1" min="1" max="<?= $article['stock'] ?>" data-max="<?= $article['stock'] ?>" readonly>
                    <button class="qty-plus" type="button">+</button>
                </div>
            </div>
            <div class="product-actions" style="margin-top: 16px;">
                <button class="btn btn-primary btn-add-cart" data-id="<?= $article['id'] ?>">
                    <i class="bi bi-bag-plus"></i> Ajouter au panier
                </button>
                <button class="fav-btn <?= $isFav ? 'active' : '' ?>" data-id="<?= $article['id'] ?>">
                    <i class="bi <?= $isFav ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                </button>
            </div>
            <?php else: ?>
            <div class="product-actions" style="margin-top: 24px;">
                <button class="btn btn-primary" disabled style="opacity: 0.5;">Indisponible</button>
                <button class="fav-btn <?= $isFav ? 'active' : '' ?>" data-id="<?= $article['id'] ?>">
                    <i class="bi <?= $isFav ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                </button>
            </div>
            <?php endif; ?>

            <?php if ($canEdit): ?>
            <div style="margin-top: 16px;">
                <a href="modifier.php?id=<?= $article['id'] ?>" class="btn btn-ghost btn-sm"><i class="bi bi-pencil"></i> Modifier cet article</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reviews -->
    <div class="reviews-section fade-in">
        <div class="container">
            <h2 class="headline-4 mb-4">Avis clients <?php if ($rating['count']): ?>(<?= $rating['count'] ?>)<?php endif; ?></h2>

            <?php if (isLoggedIn() && $_SESSION['user_id'] != $article['auteur_id']): ?>
                <?php
                $alreadyReviewed = false;
                foreach ($reviews as $r) { if ($r['user_id'] == $_SESSION['user_id']) { $alreadyReviewed = true; break; } }
                ?>
                <?php if (!$alreadyReviewed): ?>
                <form method="POST" style="margin-bottom: 40px; padding: 24px; background: var(--bg-secondary); border-radius: var(--radius-md);">
                    <h3 style="font-size: 19px; font-weight: 600; margin-bottom: 16px;">Laisser un avis</h3>
                    <div class="form-group">
                        <label class="form-label">Note</label>
                        <select name="note" class="form-control" style="max-width: 200px;" required>
                            <option value="5">★★★★★ Excellent</option>
                            <option value="4">★★★★ Très bien</option>
                            <option value="3">★★★ Bien</option>
                            <option value="2">★★ Moyen</option>
                            <option value="1">★ Mauvais</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Commentaire</label>
                        <textarea name="commentaire" class="form-control" placeholder="Partagez votre expérience..." rows="3"></textarea>
                    </div>
                    <button type="submit" name="submit_review" class="btn btn-primary btn-sm">Publier l'avis</button>
                </form>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (empty($reviews)): ?>
                <p class="text-secondary">Aucun avis pour le moment. Soyez le premier à donner votre avis !</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div class="avatar"><?= strtoupper(substr($review['username'], 0, 1)) ?></div>
                        <div class="review-meta">
                            <strong><?= sanitize($review['username']) ?></strong>
                            <span class="caption"><?= timeAgo($review['date_commentaire']) ?></span>
                        </div>
                        <?= renderStars($review['note']) ?>
                    </div>
                    <?php if ($review['commentaire']): ?>
                        <p class="review-body"><?= nl2br(sanitize($review['commentaire'])) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related products -->
    <?php if (!empty($related)): ?>
    <section class="section">
        <div class="section-header fade-in">
            <p class="eyebrow">Vous aimerez aussi</p>
            <h2 class="headline-3">Articles similaires</h2>
        </div>
        <div class="product-grid stagger-children">
            <?php foreach ($related as $rel): ?>
            <a href="produit.php?id=<?= $rel['id'] ?>" class="card" style="text-decoration: none; color: inherit;">
                <?php if ($rel['image'] && file_exists("uploads/produits/{$rel['image']}")): ?>
                    <img src="uploads/produits/<?= sanitize($rel['image']) ?>" alt="<?= sanitize($rel['nom']) ?>" class="card-img">
                <?php else: ?>
                    <div class="card-img" style="display: flex; align-items: center; justify-content: center; background: var(--bg-secondary);">
                        <i class="bi bi-box-seam" style="font-size: 40px; color: var(--text-tertiary);"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <p class="eyebrow"><?= sanitize($rel['categorie_nom'] ?? 'Article') ?></p>
                    <h3><?= sanitize($rel['nom']) ?></h3>
                    <span class="price"><?= formatPrice($rel['prix']) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
