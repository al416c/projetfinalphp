<?php
$cartCount = getCartCount();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — ' : '' ?><?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<!-- Navigation -->
<nav class="nav">
    <div class="nav-inner">
        <a href="<?= SITE_URL ?>/" class="nav-logo"><span>NOVA</span></a>

        <button class="nav-toggle" aria-label="Menu">
            <i class="bi bi-list"></i>
        </button>

        <ul class="nav-links">
            <li>
                <form class="nav-search-form" action="<?= SITE_URL ?>/recherche.php" method="GET">
                    <button type="submit"><i class="bi bi-search"></i></button>
                    <input type="text" name="q" placeholder="Rechercher..." autocomplete="off">
                </form>
            </li>
            <li><a href="<?= SITE_URL ?>/produits.php">Explorer</a></li>
            <li><a href="<?= SITE_URL ?>/categories.php">Catégories</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="<?= SITE_URL ?>/vendre.php">Vendre</a></li>
            <?php endif; ?>
            <li>
                <a href="<?= SITE_URL ?>/panier.php" class="nav-icon">
                    <i class="bi bi-bag"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="nav-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php if (isLoggedIn()): ?>
                <li>
                    <a href="<?= SITE_URL ?>/compte.php" class="nav-icon" title="<?= sanitize($_SESSION['username']) ?>">
                        <i class="bi bi-person-circle"></i>
                    </a>
                </li>
                <?php if (isAdmin()): ?>
                    <li><a href="<?= SITE_URL ?>/admin/" class="nav-icon"><i class="bi bi-gear"></i></a></li>
                <?php endif; ?>
                <li><a href="<?= SITE_URL ?>/deconnexion.php" class="nav-icon"><i class="bi bi-box-arrow-right"></i></a></li>
            <?php else: ?>
                <li><a href="<?= SITE_URL ?>/connexion.php">Connexion</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<?php if ($flash): ?>
    <div class="flash flash-<?= $flash['type'] ?>">
        <?= sanitize($flash['message']) ?>
    </div>
<?php endif; ?>
