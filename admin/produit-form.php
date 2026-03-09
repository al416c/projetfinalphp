<?php
require_once '../config/init.php';

if (!isAdmin()) {
    redirect('../connexion.php');
}

$editing = false;
$article = ['nom' => '', 'description' => '', 'prix' => '', 'categorie_id' => '', 'image' => ''];
$stock = 0;

// Edit mode
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();

    if (!$article) {
        redirect('produits.php');
    }
    $editing = true;

    $stmt = $pdo->prepare("SELECT quantite FROM stock WHERE article_id = ?");
    $stmt->execute([$id]);
    $stock = $stmt->fetchColumn() ?: 0;
}

$pageTitle = $editing ? 'Modifier l\'article' : 'Nouvel article';
$errors = [];

// Categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

// Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);
    $categorieId = (int) ($_POST['categorie_id'] ?? 0);
    $quantite = (int) ($_POST['quantite'] ?? 0);

    if (empty($nom)) $errors[] = 'Le nom est requis.';
    if (empty($description)) $errors[] = 'La description est requise.';
    if ($prix <= 0) $errors[] = 'Le prix doit être supérieur à 0.';
    if ($categorieId <= 0) $errors[] = 'Veuillez sélectionner une catégorie.';
    if ($quantite < 0) $errors[] = 'La quantité ne peut pas être négative.';

    // Image upload
    $imageName = $editing ? $article['image'] : '';
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Format d\'image non autorisé.';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Image trop volumineuse (max 5 Mo).';
        } else {
            $imageName = uniqid('art_') . '.' . $ext;
            $uploadPath = UPLOAD_DIR . 'produits/' . $imageName;
            if (!is_dir(UPLOAD_DIR . 'produits/')) {
                mkdir(UPLOAD_DIR . 'produits/', 0777, true);
            }
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors[] = 'Erreur lors de l\'upload de l\'image.';
                $imageName = $editing ? $article['image'] : '';
            } else {
                // Delete old image if editing
                if ($editing && $article['image'] && $article['image'] !== $imageName) {
                    $oldPath = UPLOAD_DIR . 'produits/' . $article['image'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
            }
        }
    }

    if (empty($errors)) {
        if ($editing) {
            $stmt = $pdo->prepare("UPDATE articles SET nom = ?, description = ?, prix = ?, categorie_id = ?, image = ? WHERE id = ?");
            $stmt->execute([$nom, $description, $prix, $categorieId, $imageName, $article['id']]);

            // Update stock
            $stmt = $pdo->prepare("SELECT id FROM stock WHERE article_id = ?");
            $stmt->execute([$article['id']]);
            if ($stmt->fetch()) {
                $pdo->prepare("UPDATE stock SET quantite = ? WHERE article_id = ?")->execute([$quantite, $article['id']]);
            } else {
                $pdo->prepare("INSERT INTO stock (article_id, quantite) VALUES (?, ?)")->execute([$article['id'], $quantite]);
            }

            setFlash('success', 'Article modifié avec succès.');
        } else {
            // Admin creates articles as their own
            $stmt = $pdo->prepare("INSERT INTO articles (nom, description, prix, image, categorie_id, auteur_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $description, $prix, $imageName, $categorieId, $_SESSION['user_id']]);
            $newId = $pdo->lastInsertId();

            $pdo->prepare("INSERT INTO stock (article_id, quantite) VALUES (?, ?)")->execute([$newId, $quantite]);
            setFlash('success', 'Article créé avec succès.');
        }

        redirect('produits.php');
    } else {
        // Preserve form data
        $article = ['nom' => $nom, 'description' => $description, 'prix' => $prix, 'categorie_id' => $categorieId, 'image' => $imageName];
        $stock = $quantite;
    }
}

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
            <h1><?= $editing ? 'Modifier l\'article' : 'Nouvel article' ?></h1>
            <a href="<?= SITE_URL ?>/admin/produits.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error fade-in">
                <ul style="margin:0;padding-left:1.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="admin-card fade-in">
            <form method="POST" enctype="multipart/form-data" class="form-modern">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nom">Nom de l'article</label>
                        <input type="text" id="nom" name="nom" value="<?= sanitize($article['nom']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="prix">Prix (€)</label>
                        <input type="number" id="prix" name="prix" step="0.01" min="0.01" value="<?= $article['prix'] ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5" required><?= sanitize($article['description']) ?></textarea>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="categorie_id">Catégorie</label>
                        <select id="categorie_id" name="categorie_id" required>
                            <option value="">Sélectionner...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $article['categorie_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= sanitize($cat['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantite">Stock</label>
                        <input type="number" id="quantite" name="quantite" min="0" value="<?= $stock ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Image</label>
                    <div class="upload-zone" id="uploadZone">
                        <input type="file" id="image" name="image" accept="image/*" <?= !$editing ? '' : '' ?>>
                        <div class="upload-placeholder">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <p>Cliquez ou glissez une image</p>
                        </div>
                        <div class="upload-preview" id="uploadPreview" style="<?= ($editing && $article['image']) ? '' : 'display:none;' ?>">
                            <?php if ($editing && $article['image']): ?>
                                <img src="<?= SITE_URL ?>/uploads/produits/<?= $article['image'] ?>" alt="Preview">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i>
                        <?= $editing ? 'Enregistrer les modifications' : 'Créer l\'article' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
