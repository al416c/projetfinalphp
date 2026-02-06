<?php
require_once '../config/init.php';

if (!isAdmin()) {
    redirect('../connexion.php');
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

$produit = null;
$isEdit = false;

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $produit = $stmt->fetch();
    $isEdit = true;
}

$pageTitle = ($isEdit ? 'Modifier' : 'Ajouter') . ' un produit - ' . SITE_NAME;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = sanitize($_POST['nom'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $categorie_id = !empty($_POST['categorie_id']) ? intval($_POST['categorie_id']) : null;

    if (empty($nom)) {
        $errors[] = "Le nom est requis.";
    }
    if ($prix <= 0) {
        $errors[] = "Le prix doit être supérieur à 0.";
    }
    if ($stock < 0) {
        $errors[] = "Le stock ne peut pas être négatif.";
    }

    $imageName = $produit['image'] ?? null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $imageName = uniqid() . '.' . $ext;
            $uploadDir = '../uploads/produits/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        } else {
            $errors[] = "Format d'image non autorisé.";
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE produits SET nom = ?, description = ?, prix = ?, stock = ?, categorie_id = ?, image = ? WHERE id = ?");
            $stmt->execute([$nom, $description, $prix, $stock, $categorie_id, $imageName, $produit['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO produits (nom, description, prix, stock, categorie_id, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $description, $prix, $stock, $categorie_id, $imageName]);
        }
        redirect('produits.php');
    }
}
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
                    <a class="nav-link active" href="produits.php"><i class="fas fa-box me-2"></i>Produits</a>
                    <a class="nav-link" href="categories.php"><i class="fas fa-tags me-2"></i>Catégories</a>
                    <a class="nav-link" href="commandes.php"><i class="fas fa-shopping-cart me-2"></i>Commandes</a>
                    <a class="nav-link" href="utilisateurs.php"><i class="fas fa-users me-2"></i>Utilisateurs</a>
                </nav>
            </div>

            <div class="col-md-10 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><?= $isEdit ? 'Modifier' : 'Ajouter' ?> un produit</h1>
                    <a href="produits.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Nom du produit</label>
                                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($produit['nom'] ?? $_POST['nom'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($produit['description'] ?? $_POST['description'] ?? '') ?></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Prix (€)</label>
                                            <input type="number" name="prix" class="form-control" step="0.01" min="0" value="<?= $produit['prix'] ?? $_POST['prix'] ?? '' ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Stock</label>
                                            <input type="number" name="stock" class="form-control" min="0" value="<?= $produit['stock'] ?? $_POST['stock'] ?? 0 ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Catégorie</label>
                                            <select name="categorie_id" class="form-select">
                                                <option value="">-- Aucune --</option>
                                                <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>" <?= (($produit['categorie_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cat['nom']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Image</label>
                                        <?php if ($produit && $produit['image']): ?>
                                            <div class="mb-2">
                                                <img src="<?= SITE_URL ?>/uploads/produits/<?= $produit['image'] ?>" class="img-fluid rounded" alt="">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $isEdit ? 'Modifier' : 'Ajouter' ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
