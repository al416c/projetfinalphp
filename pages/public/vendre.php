<?php
require_once 'config/init.php';
$pageTitle = 'Vendre un article';

if (!isLoggedIn()) {
    setFlash('error', 'Connectez-vous pour vendre un article.');
    redirect('connexion.php');
}

// Get categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = cleanInput($_POST['nom'] ?? '');
    $description = cleanInput($_POST['description'] ?? '');
    $prix = (float)($_POST['prix'] ?? 0);
    $categorie_id = (int)($_POST['categorie_id'] ?? 0);
    $quantite = max(1, (int)($_POST['quantite'] ?? 1));

    if (empty($nom)) $errors[] = 'Le nom est requis.';
    if (empty($description)) $errors[] = 'La description est requise.';
    if ($prix <= 0) $errors[] = 'Le prix doit être supérieur à 0.';
    if ($quantite < 1) $errors[] = 'La quantité doit être au minimum 1.';

    // Handle image upload
    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = 'Format d\'image non supporté. Utilisez JPG, PNG, WebP ou GIF.';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'L\'image est trop volumineuse (max 5 Mo).';
        } else {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('article_') . '.' . $ext;
            $uploadDir = UPLOAD_DIR . 'produits/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }
    }

    if (empty($errors)) {
        $catId = $categorie_id > 0 ? $categorie_id : null;
        $stmt = $pdo->prepare("INSERT INTO articles (nom, description, prix, image, categorie_id, auteur_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $description, $prix, $imageName, $catId, $_SESSION['user_id']]);
        $articleId = $pdo->lastInsertId();

        // Create stock entry
        $stmtStock = $pdo->prepare("INSERT INTO stock (article_id, quantite) VALUES (?, ?)");
        $stmtStock->execute([$articleId, $quantite]);

        setFlash('success', 'Article mis en vente avec succès !');
        redirect("produit.php?id=$articleId");
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="sell-form fade-in">
        <h1>Vendre un article</h1>
        <p>Remplissez les informations ci-dessous pour mettre votre article en vente sur NOVA.</p>

        <?php if ($errors): ?>
            <div class="flash flash-error" style="position: static; animation: none; margin-bottom: 24px;">
                <?= implode('<br>', array_map('sanitize', $errors)) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <!-- Image upload -->
            <div class="form-group">
                <label class="form-label">Photo de l'article</label>
                <div class="image-upload">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <p>Cliquez ou glissez une image ici</p>
                    <p class="caption mt-1">JPG, PNG, WebP · Max 5 Mo</p>
                </div>
                <input type="file" id="image-input" name="image" accept="image/*" style="display: none;">
            </div>

            <div class="form-group">
                <label class="form-label">Nom de l'article</label>
                <input type="text" name="nom" class="form-control" placeholder="Ex: Casque Bluetooth Pro" value="<?= sanitize($nom ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" placeholder="Décrivez votre article en détail..." required><?= sanitize($description ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Prix (€)</label>
                    <input type="number" name="prix" class="form-control" placeholder="0.00" step="0.01" min="0.01" value="<?= $prix ?? '' ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Quantité en stock</label>
                    <input type="number" name="quantite" class="form-control" placeholder="1" min="1" value="<?= $quantite ?? 1 ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Catégorie</label>
                <select name="categorie_id" class="form-control">
                    <option value="0">— Aucune catégorie —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($categorie_id ?? 0) == $cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 mt-3">
                <i class="bi bi-tag"></i> Mettre en vente
            </button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
