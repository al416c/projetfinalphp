<?php
$cartCount = getCartCount();
$flash = getFlash();
$notifCount = isLoggedIn() ? getUnreadNotificationCount() : 0;

// Fetch recent notifications for dropdown
$recentNotifs = [];
if (isLoggedIn()) {
    $stmtNotif = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY date_creation DESC LIMIT 8");
    $stmtNotif->execute([$_SESSION['user_id']]);
    $recentNotifs = $stmtNotif->fetchAll();
}
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
                <li class="notif-wrapper">
                    <button class="nav-icon notif-toggle" id="notifBell" aria-label="Notifications">
                        <i class="bi bi-bell"></i>
                        <?php if ($notifCount > 0): ?>
                            <span class="nav-badge notif-badge"><?= $notifCount ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <strong>Notifications</strong>
                            <?php if ($notifCount > 0): ?>
                                <a href="<?= SITE_URL ?>/ajax/mark_notifications_read.php" class="notif-mark-read" id="markAllRead">Tout marquer lu</a>
                            <?php endif; ?>
                        </div>
                        <div class="notif-list">
                            <?php if (empty($recentNotifs)): ?>
                                <div class="notif-empty">Aucune notification</div>
                            <?php else: ?>
                                <?php foreach ($recentNotifs as $notif): ?>
                                    <a href="<?= $notif['lien'] ? SITE_URL . '/' . $notif['lien'] : '#' ?>" class="notif-item <?= !$notif['lu'] ? 'unread' : '' ?>">
                                        <div class="notif-icon">
                                            <i class="bi <?= $notif['type'] === 'sale' ? 'bi-cash-coin' : 'bi-bell' ?>"></i>
                                        </div>
                                        <div class="notif-content">
                                            <p><?= sanitize($notif['message']) ?></p>
                                            <span class="notif-time"><?= timeAgo($notif['date_creation']) ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
                <li>
                    <a href="<?= SITE_URL ?>/compte.php" class="nav-icon" title="<?= sanitize($_SESSION['username']) ?>">
                        <i class="bi bi-person-circle"></i>
                    </a>
                </li>
                <?php if (isAdmin()): ?>
                    <li><a href="<?= SITE_URL ?>/admin/" class="nav-icon"><i class="bi bi-gear"></i></a></li>
                <?php endif; ?>
                <li>
                    <button class="dark-mode-toggle nav-icon" id="darkModeToggle" title="Mode sombre">
                        <i class="bi bi-moon"></i>
                    </button>
                </li>
                <li><a href="<?= SITE_URL ?>/deconnexion.php" class="nav-icon"><i class="bi bi-box-arrow-right"></i></a></li>
            <?php else: ?>
                <li>
                    <button class="dark-mode-toggle nav-icon" id="darkModeToggle" title="Mode sombre">
                        <i class="bi bi-moon"></i>
                    </button>
                </li>
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
