<?php
require_once '../config/init.php';

if (!isAdmin()) {
    redirect('../connexion.php');
}

$categorie = null;
$isEdit = false;

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $categorie = $stmt->fetch();
    $isEdit = true;
}

$pageTitle = ($isEdit ? 'Modifier' : 'Ajouter') . ' une catégorie - ' . SITE_NAME;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = sanitize($_POST['nom'] ?? '');
    $description = sanitize($_POST['description'] ?? '');

    if (empty($nom)) {
        $errors[] = "Le nom est requis.";
    }

    $imageName = $categorie['image'] ?? null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $imageName = uniqid() . '.' . $ext;
            $uploadDir = '../uploads/categories/';
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
            $stmt = $pdo->prepare("UPDATE categories SET nom = ?, description = ?, image = ? WHERE id = ?");
            $stmt->execute([$nom, $description, $imageName, $categorie['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (nom, description, image) VALUES (?, ?, ?)");
            $stmt->execute([$nom, $description, $imageName]);
        }
        redirect('categories.php');
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
                    <a class="nav-link" href="produits.php"><i class="fas fa-box me-2"></i>Produits</a>
                    <a class="nav-link active" href="categories.php"><i class="fas fa-tags me-2"></i>Catégories</a>
                    <a class="nav-link" href="commandes.php"><i class="fas fa-shopping-cart me-2"></i>Commandes</a>
                    <a class="nav-link" href="utilisateurs.php"><i class="fas fa-users me-2"></i>Utilisateurs</a>
                </nav>
            </div>

            <div class="col-md-10 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><?= $isEdit ? 'Modifier' : 'Ajouter' ?> une catégorie</h1>
                    <a href="categories.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
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
                                        <label class="form-label">Nom de la catégorie</label>
                                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($categorie['nom'] ?? $_POST['nom'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($categorie['description'] ?? $_POST['description'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Image</label>
                                        <?php if ($categorie && $categorie['image']): ?>
                                            <div class="mb-2">
                                                <img src="<?= SITE_URL ?>/uploads/categories/<?= $categorie['image'] ?>" class="img-fluid rounded" alt="">
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
