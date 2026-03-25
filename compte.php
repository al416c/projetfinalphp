<?php
require_once 'config/init.php';
$pageTitle = 'Mon compte';

if (!isLoggedIn()) redirect('connexion.php');

$userId = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$activeTab = $_GET['tab'] ?? 'profile';

// Handle profile update
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');

    if (empty($username) || empty($email)) {
        $errors[] = 'Tous les champs sont requis.';
    }

    // Check unique username (excluding self)
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
    $stmtCheck->execute([$username, $userId]);
    if ($stmtCheck->fetchColumn() > 0) $errors[] = 'Ce nom d\'utilisateur est pris.';

    // Check unique email
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
    $stmtCheck->execute([$email, $userId]);
    if ($stmtCheck->fetchColumn() > 0) $errors[] = 'Cet email est déjà utilisé.';

    // Handle photo upload
    $photo = $user['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = mime_content_type($_FILES['photo']['tmp_name']);
        if (in_array($fileType, $allowedTypes)) {
            if ($user['photo'] && file_exists(UPLOAD_DIR . "produits/{$user['photo']}")) {
                unlink(UPLOAD_DIR . "produits/{$user['photo']}");
            }
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photo = 'avatar_' . $userId . '_' . uniqid() . '.' . $ext;
            $uploadDir = UPLOAD_DIR . 'produits/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photo);
        }
    }

    if (empty($errors)) {
        $pdo->prepare("UPDATE users SET username = ?, email = ?, photo = ? WHERE id = ?")->execute([$username, $email, $photo, $userId]);
        $_SESSION['username'] = $username;
        setFlash('success', 'Profil mis à jour !');
        redirect('compte.php?tab=profile');
    }
    $activeTab = 'profile';
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPw = $_POST['current_password'] ?? '';
    $newPw = $_POST['new_password'] ?? '';
    $confirmPw = $_POST['confirm_password'] ?? '';

    if (!password_verify($currentPw, $user['password'])) {
        $errors[] = 'Mot de passe actuel incorrect.';
    }
    if (strlen($newPw) < 6) {
        $errors[] = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
    }
    if ($newPw !== $confirmPw) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }

    if (empty($errors)) {
        $hashed = password_hash($newPw, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $userId]);
        setFlash('success', 'Mot de passe modifié !');
        redirect('compte.php?tab=profile');
    }
    $activeTab = 'profile';
}

// Handle add balance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_balance'])) {
    $amount = (float)($_POST['amount'] ?? 0);
    if ($amount >= 10 && $amount <= 10000) {
        $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$amount, $userId]);
        setFlash('success', formatPrice($amount) . ' ajouté à votre solde !');
        redirect('compte.php?tab=balance');
    } else {
        $errors[] = 'Montant invalide (min 10€, max 10 000€).';
        $activeTab = 'balance';
    }
}

// Refresh user data after updates
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Fetch user's articles
$stmtArticles = $pdo->prepare("
    SELECT a.*, s.quantite as stock, c.nom as categorie_nom
    FROM articles a
    LEFT JOIN stock s ON a.id = s.article_id
    LEFT JOIN categories c ON a.categorie_id = c.id
    WHERE a.auteur_id = ?
    ORDER BY a.date_publication DESC
");
$stmtArticles->execute([$userId]);
$userArticles = $stmtArticles->fetchAll();

// Fetch purchased items (from invoices)
$stmtPurchased = $pdo->prepare("
    SELECT fd.*, a.nom, a.image, f.date_transaction, f.id as facture_id
    FROM facture_details fd
    JOIN articles a ON fd.article_id = a.id
    JOIN factures f ON fd.facture_id = f.id
    WHERE f.user_id = ?
    ORDER BY f.date_transaction DESC
    LIMIT 20
");
$stmtPurchased->execute([$userId]);
$purchased = $stmtPurchased->fetchAll();

// Fetch recent invoices
$stmtFactures = $pdo->prepare("SELECT * FROM factures WHERE user_id = ? ORDER BY date_transaction DESC LIMIT 10");
$stmtFactures->execute([$userId]);
$factures = $stmtFactures->fetchAll();

// Fetch favorites
$stmtFavs = $pdo->prepare("
    SELECT a.*, s.quantite as stock, c.nom as categorie_nom, fav.date_ajout as fav_date
    FROM favoris fav
    JOIN articles a ON fav.article_id = a.id
    LEFT JOIN stock s ON a.id = s.article_id
    LEFT JOIN categories c ON a.categorie_id = c.id
    WHERE fav.user_id = ?
    ORDER BY fav.date_ajout DESC
");
$stmtFavs->execute([$userId]);
$favorites = $stmtFavs->fetchAll();

require_once 'includes/header.php';
?>

<div class="container-wide">
    <div class="account-layout">
        <!-- Sidebar -->
        <div class="account-sidebar fade-in">
            <div class="user-card">
                <div class="avatar-lg">
                    <?php if ($user['photo'] && file_exists("uploads/produits/{$user['photo']}")): ?>
                        <img src="uploads/produits/<?= sanitize($user['photo']) ?>" alt="">
                    <?php else: ?>
                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <h3><?= sanitize($user['username']) ?></h3>
                <p class="caption"><?= sanitize($user['email']) ?></p>
                <p class="caption">Membre depuis <?= date('M Y', strtotime($user['date_inscription'])) ?></p>
                <p class="balance"><?= formatPrice($user['balance']) ?></p>
                <p class="caption">Solde disponible</p>
            </div>
            <ul class="account-nav">
                <li><a href="?tab=profile" class="<?= $activeTab === 'profile' ? 'active' : '' ?>"><i class="bi bi-person"></i> Profil</a></li>
                <li><a href="?tab=balance" class="<?= $activeTab === 'balance' ? 'active' : '' ?>"><i class="bi bi-wallet2"></i> Solde</a></li>
                <li><a href="?tab=articles" class="<?= $activeTab === 'articles' ? 'active' : '' ?>"><i class="bi bi-tag"></i> Mes articles</a></li>
                <li><a href="?tab=achats" class="<?= $activeTab === 'achats' ? 'active' : '' ?>"><i class="bi bi-bag-check"></i> Mes achats</a></li>
                <li><a href="?tab=factures" class="<?= $activeTab === 'factures' ? 'active' : '' ?>"><i class="bi bi-receipt"></i> Factures</a></li>
                <li><a href="?tab=favoris" class="<?= $activeTab === 'favoris' ? 'active' : '' ?>"><i class="bi bi-heart"></i> Favoris</a></li>
            </ul>
        </div>

        <!-- Content -->
        <div class="account-content fade-in">
            <?php if ($errors): ?>
                <div class="flash flash-error" style="position: static; animation: none; margin-bottom: 24px;">
                    <?= implode('<br>', array_map('sanitize', $errors)) ?>
                </div>
            <?php endif; ?>

            <!-- Profile Tab -->
            <?php if ($activeTab === 'profile'): ?>
            <h2>Mon profil</h2>
            <form method="POST" enctype="multipart/form-data" style="max-width: 500px;">
                <div class="form-group">
                    <label class="form-label">Photo de profil</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>
                <div class="form-group">
                    <label class="form-label">Nom d'utilisateur</label>
                    <input type="text" name="username" class="form-control" value="<?= sanitize($user['username']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= sanitize($user['email']) ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">Enregistrer</button>
            </form>

            <div class="divider" style="margin: 40px 0;"></div>

            <h3 class="headline-4 mb-3">Changer le mot de passe</h3>
            <form method="POST" style="max-width: 500px;">
                <div class="form-group">
                    <label class="form-label">Mot de passe actuel</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmer le nouveau mot de passe</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-dark">Modifier le mot de passe</button>
            </form>

            <!-- Balance Tab -->
            <?php elseif ($activeTab === 'balance'): ?>
            <h2>Mon solde</h2>
            <div class="balance-card">
                <p class="balance-label">Solde actuel</p>
                <p class="balance-amount"><?= formatPrice($user['balance']) ?></p>
            </div>

            <h3 class="headline-4 mb-3">Recharger mon solde</h3>
            <form method="POST" style="max-width: 400px;">
                <div class="form-group">
                    <label class="form-label">Montant à ajouter (€)</label>
                    <input type="number" name="amount" class="form-control" min="10" max="10000" step="0.01" placeholder="50.00" required>
                </div>
                <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
                    <?php foreach ([25, 50, 100, 250, 500] as $amt): ?>
                        <button type="button" class="filter-chip" onclick="this.closest('form').querySelector('input[name=amount]').value=<?= $amt ?>"><?= $amt ?> €</button>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="add_balance" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Recharger</button>
            </form>

            <!-- Articles Tab -->
            <?php elseif ($activeTab === 'articles'): ?>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px;">
                <h2 style="margin-bottom: 0;">Mes articles en vente</h2>
                <a href="vendre.php" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nouvel article</a>
            </div>

            <?php if (empty($userArticles)): ?>
                <div class="empty-state">
                    <i class="bi bi-tag"></i>
                    <h3>Aucun article en vente</h3>
                    <p>Commencez à vendre dès maintenant !</p>
                    <a href="vendre.php" class="btn btn-primary">Mettre en vente</a>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($userArticles as $art): ?>
                    <div class="card">
                        <a href="produit.php?id=<?= $art['id'] ?>">
                            <?php if ($art['image'] && file_exists("uploads/produits/{$art['image']}")): ?>
                                <img src="uploads/produits/<?= sanitize($art['image']) ?>" alt="" class="card-img">
                            <?php else: ?>
                                <div class="card-img" style="display: flex; align-items: center; justify-content: center; background: var(--bg-secondary);">
                                    <i class="bi bi-box-seam" style="font-size: 32px; color: var(--text-tertiary);"></i>
                                </div>
                            <?php endif; ?>
                        </a>
                        <div class="card-body">
                            <p class="eyebrow"><?= sanitize($art['categorie_nom'] ?? 'Article') ?></p>
                            <h3><?= sanitize($art['nom']) ?></h3>
                            <div class="card-meta">
                                <span class="price"><?= formatPrice($art['prix']) ?></span>
                                <span class="badge <?= ($art['stock'] ?? 0) > 0 ? 'badge-success' : 'badge-danger' ?>">
                                    Stock: <?= $art['stock'] ?? 0 ?>
                                </span>
                            </div>
                            <a href="modifier.php?id=<?= $art['id'] ?>" class="btn btn-ghost btn-sm mt-2 w-100"><i class="bi bi-pencil"></i> Modifier</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Purchased Tab -->
            <?php elseif ($activeTab === 'achats'): ?>
            <h2>Mes achats</h2>
            <?php if (empty($purchased)): ?>
                <div class="empty-state">
                    <i class="bi bi-bag-check"></i>
                    <h3>Aucun achat</h3>
                    <p>Vos achats apparaîtront ici.</p>
                    <a href="produits.php" class="btn btn-primary">Explorer</a>
                </div>
            <?php else: ?>
                <?php
                // Group purchases by date for timeline
                $groupedPurchases = [];
                foreach ($purchased as $item) {
                    $dateKey = date('Y-m-d', strtotime($item['date_transaction']));
                    $groupedPurchases[$dateKey][] = $item;
                }
                ?>
                <div class="order-timeline">
                    <?php foreach ($groupedPurchases as $date => $items): ?>
                    <div class="timeline-group">
                        <div class="timeline-date">
                            <div class="timeline-dot"></div>
                            <span><?= date('d', strtotime($date)) ?></span>
                            <span class="timeline-month"><?= strftime('%b %Y', strtotime($date)) ?></span>
                        </div>
                        <div class="timeline-items">
                            <?php foreach ($items as $item): ?>
                            <div class="timeline-item">
                                <div class="timeline-item-img">
                                    <?php if ($item['image'] && file_exists("uploads/produits/{$item['image']}")): ?>
                                        <img src="uploads/produits/<?= sanitize($item['image']) ?>" alt="">
                                    <?php else: ?>
                                        <i class="bi bi-box-seam"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-item-info">
                                    <strong><?= sanitize($item['nom']) ?></strong>
                                    <p class="caption">Quantité : <?= $item['quantite'] ?></p>
                                </div>
                                <div class="timeline-item-end">
                                    <span class="timeline-price"><?= formatPrice($item['prix_unitaire'] * $item['quantite']) ?></span>
                                    <a href="commande-detail.php?id=<?= $item['facture_id'] ?>" class="btn btn-ghost btn-sm" title="Voir la commande"><i class="bi bi-arrow-right"></i></a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Factures Tab -->
            <?php elseif ($activeTab === 'factures'): ?>
            <h2>Mes factures</h2>
            <?php if (empty($factures)): ?>
                <div class="empty-state">
                    <i class="bi bi-receipt"></i>
                    <h3>Aucune facture</h3>
                    <p>Vos factures apparaîtront ici après vos achats.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Facture</th>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Ville</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($factures as $f): ?>
                            <tr>
                                <td><strong>#<?= str_pad($f['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($f['date_transaction'])) ?></td>
                                <td><strong><?= formatPrice($f['montant']) ?></strong></td>
                                <td><?= sanitize($f['ville']) ?></td>
                                <td><a href="commande-detail.php?id=<?= $f['id'] ?>" class="btn btn-ghost btn-sm">Voir</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Favorites Tab -->
            <?php elseif ($activeTab === 'favoris'): ?>
            <h2>Mes favoris</h2>
            <?php if (empty($favorites)): ?>
                <div class="empty-state">
                    <i class="bi bi-heart"></i>
                    <h3>Aucun favori</h3>
                    <p>Ajoutez des articles à vos favoris en cliquant sur le cœur.</p>
                    <a href="produits.php" class="btn btn-primary">Explorer</a>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($favorites as $fav): ?>
                    <a href="produit.php?id=<?= $fav['id'] ?>" class="card" style="text-decoration: none; color: inherit;">
                        <?php if ($fav['image'] && file_exists("uploads/produits/{$fav['image']}")): ?>
                            <img src="uploads/produits/<?= sanitize($fav['image']) ?>" alt="" class="card-img">
                        <?php else: ?>
                            <div class="card-img" style="display: flex; align-items: center; justify-content: center; background: var(--bg-secondary);">
                                <i class="bi bi-box-seam" style="font-size: 32px; color: var(--text-tertiary);"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <p class="eyebrow"><?= sanitize($fav['categorie_nom'] ?? 'Article') ?></p>
                            <h3><?= sanitize($fav['nom']) ?></h3>
                            <span class="price"><?= formatPrice($fav['prix']) ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>

<div style="height: 80px;"></div>

<?php require_once 'includes/footer.php'; ?>
