<?php
require_once '../config/init.php';

if (!isAdmin()) {
    redirect('../connexion.php');
}

$editing = false;
$categorie = ['nom' => '', 'description' => '', 'image' => ''];

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $categorie = $stmt->fetch();
    if (!$categorie) redirect('categories.php');
    $editing = true;
}

$pageTitle = $editing ? 'Modifier la catégorie' : 'Nouvelle catégorie';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($nom)) $errors[] = 'Le nom est requis.';

    // Check unique name
    $check = $pdo->prepare("SELECT id FROM categories WHERE nom = ? AND id != ?");
    $check->execute([$nom, $editing ? $categorie['id'] : 0]);
    if ($check->fetch()) $errors[] = 'Ce nom de catégorie existe déjà.';

    // Image upload
    $imageName = $editing ? $categorie['image'] : '';
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Format d\'image non autorisé.';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Image trop volumineuse (max 5 Mo).';
        } else {
            $imageName = uniqid('cat_') . '.' . $ext;
            $uploadPath = UPLOAD_DIR . 'categories/' . $imageName;
            if (!is_dir(UPLOAD_DIR . 'categories/')) {
                mkdir(UPLOAD_DIR . 'categories/', 0777, true);
            }
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors[] = 'Erreur lors de l\'upload.';
                $imageName = $editing ? $categorie['image'] : '';
            } else {
                if ($editing && $categorie['image'] && $categorie['image'] !== $imageName) {
                    $oldPath = UPLOAD_DIR . 'categories/' . $categorie['image'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
            }
        }
    }

    if (empty($errors)) {
        if ($editing) {
            $stmt = $pdo->prepare("UPDATE categories SET nom = ?, description = ?, image = ? WHERE id = ?");
            $stmt->execute([$nom, $description, $imageName, $categorie['id']]);
            setFlash('success', 'Catégorie modifiée.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (nom, description, image) VALUES (?, ?, ?)");
            $stmt->execute([$nom, $description, $imageName]);
            setFlash('success', 'Catégorie créée.');
        }
        redirect('categories.php');
    } else {
        $categorie = ['nom' => $nom, 'description' => $description, 'image' => $imageName];
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
            <h1><?= $editing ? 'Modifier la catégorie' : 'Nouvelle catégorie' ?></h1>
            <a href="<?= SITE_URL ?>/admin/categories.php" class="btn btn-secondary">
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
                <div class="form-group">
                    <label for="nom">Nom de la catégorie</label>
                    <input type="text" id="nom" name="nom" value="<?= sanitize($categorie['nom']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"><?= sanitize($categorie['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image">Image</label>
                    <div class="upload-zone" id="uploadZone">
                        <input type="file" id="image" name="image" accept="image/*">
                        <div class="upload-placeholder">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <p>Cliquez ou glissez une image</p>
                        </div>
                        <div class="upload-preview" id="uploadPreview" style="<?= ($editing && $categorie['image']) ? '' : 'display:none;' ?>">
                            <?php if ($editing && $categorie['image']): ?>
                                <img src="<?= SITE_URL ?>/uploads/categories/<?= $categorie['image'] ?>" alt="Preview">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i>
                        <?= $editing ? 'Enregistrer' : 'Créer la catégorie' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
