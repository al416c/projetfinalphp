<?php
require_once 'config/init.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id || !isLoggedIn()) redirect('produits.php');

// Fetch article
$stmt = $pdo->prepare("
    SELECT a.*, s.quantite as stock
    FROM articles a
    LEFT JOIN stock s ON a.id = s.article_id
    WHERE a.id = ?
");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    setFlash('error', 'Article introuvable.');
    redirect('produits.php');
}

// Only author or admin can edit
if ($_SESSION['user_id'] != $article['auteur_id'] && !isAdmin()) {
    setFlash('error', 'Vous n\'avez pas la permission de modifier cet article.');
    redirect("produit.php?id=$id");
}

$pageTitle = 'Modifier : ' . $article['nom'];
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
$errors = [];

// Handle delete
if (isset($_POST['delete'])) {
    // Delete image if exists
    if ($article['image'] && file_exists(UPLOAD_DIR . "produits/{$article['image']}")) {
        unlink(UPLOAD_DIR . "produits/{$article['image']}");
    }
    $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);
    setFlash('success', 'Article supprimé avec succès.');
    redirect('compte.php');
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete'])) {
    $nom = sanitize($_POST['nom'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $prix = (float)($_POST['prix'] ?? 0);
    $categorie_id = (int)($_POST['categorie_id'] ?? 0);
    $quantite = max(0, (int)($_POST['quantite'] ?? 0));

    if (empty($nom)) $errors[] = 'Le nom est requis.';
    if (empty($description)) $errors[] = 'La description est requise.';
    if ($prix <= 0) $errors[] = 'Le prix doit être supérieur à 0.';

    // Handle image upload
    $imageName = $article['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = 'Format d\'image non supporté.';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'L\'image est trop volumineuse (max 5 Mo).';
        } else {
            // Delete old image
            if ($article['image'] && file_exists(UPLOAD_DIR . "produits/{$article['image']}")) {
                unlink(UPLOAD_DIR . "produits/{$article['image']}");
            }
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('article_') . '.' . $ext;
            $uploadDir = UPLOAD_DIR . 'produits/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }
    }

    if (empty($errors)) {
        $catId = $categorie_id > 0 ? $categorie_id : null;
        $stmtUpdate = $pdo->prepare("UPDATE articles SET nom = ?, description = ?, prix = ?, image = ?, categorie_id = ? WHERE id = ?");
        $stmtUpdate->execute([$nom, $description, $prix, $imageName, $catId, $id]);

        // Update stock
        $stmtStockCheck = $pdo->prepare("SELECT COUNT(*) FROM stock WHERE article_id = ?");
        $stmtStockCheck->execute([$id]);
        if ($stmtStockCheck->fetchColumn() > 0) {
            $pdo->prepare("UPDATE stock SET quantite = ? WHERE article_id = ?")->execute([$quantite, $id]);
        } else {
            $pdo->prepare("INSERT INTO stock (article_id, quantite) VALUES (?, ?)")->execute([$id, $quantite]);
        }

        setFlash('success', 'Article modifié avec succès !');
        redirect("produit.php?id=$id");
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="sell-form fade-in">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <h1 style="margin-bottom: 0;">Modifier l'article</h1>
            <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');" style="margin: 0;">
                <button type="submit" name="delete" value="1" class="btn btn-danger btn-sm">
                    <i class="bi bi-trash3"></i> Supprimer
                </button>
            </form>
        </div>
        <p style="margin-bottom: 40px;">Modifiez les informations de votre article.</p>

        <?php if ($errors): ?>
            <div class="flash flash-error" style="position: static; animation: none; margin-bottom: 24px;">
                <?= implode('<br>', array_map('sanitize', $errors)) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <!-- Current image / Upload -->
            <div class="form-group">
                <label class="form-label">Photo de l'article</label>
                <?php if ($article['image'] && file_exists("uploads/produits/{$article['image']}")): ?>
                    <div style="margin-bottom: 12px;">
                        <img src="uploads/produits/<?= sanitize($article['image']) ?>" alt="" style="max-height: 200px; border-radius: 16px;">
                    </div>
                <?php endif; ?>
                <div class="image-upload">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <p>Cliquez ou glissez une nouvelle image</p>
                    <p class="caption mt-1">Laissez vide pour garder l'image actuelle</p>
                </div>
                <input type="file" id="image-input" name="image" accept="image/*" style="display: none;">
            </div>

            <div class="form-group">
                <label class="form-label">Nom de l'article</label>
                <input type="text" name="nom" class="form-control" value="<?= sanitize($article['nom']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" required><?= sanitize($article['description']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Prix (€)</label>
                    <input type="number" name="prix" class="form-control" step="0.01" min="0.01" value="<?= $article['prix'] ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Quantité en stock</label>
                    <input type="number" name="quantite" class="form-control" min="0" value="<?= $article['stock'] ?? 0 ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Catégorie</label>
                <select name="categorie_id" class="form-control">
                    <option value="0">— Aucune catégorie —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $article['categorie_id'] == $cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 mt-3">
                <i class="bi bi-check-lg"></i> Enregistrer les modifications
            </button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
