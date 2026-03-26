<?php
require_once 'config/init.php';
$pageTitle = 'Validation de commande';

if (!isLoggedIn()) redirect('connexion.php');

// Fetch cart items
$stmt = $pdo->prepare("
    SELECT p.*, a.nom, a.prix, a.image, s.quantite as stock
    FROM panier p
    JOIN articles a ON p.article_id = a.id
    LEFT JOIN stock s ON a.id = s.article_id
    WHERE p.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

if (empty($items)) {
    setFlash('error', 'Votre panier est vide.');
    redirect('panier.php');
}

$total = 0;
foreach ($items as $item) {
    $total += $item['prix'] * $item['quantite'];
}

$balance = getUserBalance();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adresse = cleanInput($_POST['adresse'] ?? '');
    $ville = cleanInput($_POST['ville'] ?? '');
    $code_postal = cleanInput($_POST['code_postal'] ?? '');

    if (empty($adresse) || empty($ville) || empty($code_postal)) {
        $errors[] = 'Tous les champs sont requis.';
    }

    // Re-check balance
    if ($balance < $total) {
        $errors[] = 'Solde insuffisant.';
    }

    // Check stock availability
    foreach ($items as $item) {
        if ($item['quantite'] > $item['stock']) {
            $errors[] = "Stock insuffisant pour " . $item['nom'] . ".";
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Deduct balance
            $stmtBalance = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ? AND balance >= ?");
            $stmtBalance->execute([$total, $_SESSION['user_id'], $total]);

            if ($stmtBalance->rowCount() === 0) {
                throw new Exception('Solde insuffisant.');
            }

            // Credit sellers + notify them
            foreach ($items as $item) {
                $stmtArticle = $pdo->prepare("SELECT auteur_id, nom FROM articles WHERE id = ?");
                $stmtArticle->execute([$item['article_id']]);
                $articleInfo = $stmtArticle->fetch();

                if ($articleInfo && $articleInfo['auteur_id']) {
                    $itemTotal = $item['prix'] * $item['quantite'];
                    $stmtCredit = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $stmtCredit->execute([$itemTotal, $articleInfo['auteur_id']]);

                    // Notify seller
                    if ($articleInfo['auteur_id'] != $_SESSION['user_id']) {
                        $buyerName = $_SESSION['username'];
                        $msg = $buyerName . ' a acheté ' . $item['quantite'] . 'x "' . $articleInfo['nom'] . '" pour ' . number_format($itemTotal, 2, ',', ' ') . ' €';
                        createNotification($articleInfo['auteur_id'], $msg, 'produit.php?id=' . $item['article_id'], 'sale');
                    }
                }
            }

            // Create invoice
            $stmtFacture = $pdo->prepare("INSERT INTO factures (user_id, montant, adresse, ville, code_postal) VALUES (?, ?, ?, ?, ?)");
            $stmtFacture->execute([$_SESSION['user_id'], $total, $adresse, $ville, $code_postal]);
            $factureId = $pdo->lastInsertId();

            // Create invoice details + decrement stock
            foreach ($items as $item) {
                $stmtDetail = $pdo->prepare("INSERT INTO facture_details (facture_id, article_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
                $stmtDetail->execute([$factureId, $item['article_id'], $item['quantite'], $item['prix']]);

                $stmtStock = $pdo->prepare("UPDATE stock SET quantite = quantite - ? WHERE article_id = ? AND quantite >= ?");
                $stmtStock->execute([$item['quantite'], $item['article_id'], $item['quantite']]);
            }

            // Clear cart
            $stmtClear = $pdo->prepare("DELETE FROM panier WHERE user_id = ?");
            $stmtClear->execute([$_SESSION['user_id']]);

            $pdo->commit();

            $_SESSION['last_facture_id'] = $factureId;
            redirect('confirmation.php');

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Erreur lors de la commande : ' . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1 class="fade-in">Finaliser la commande</h1>
        <p class="fade-in">Vérifiez vos articles et entrez votre adresse de facturation.</p>
    </div>
</div>

<div class="container-wide">
    <?php if ($errors): ?>
        <div class="flash flash-error" style="position: static; animation: none; margin: 24px 0;">
            <?= implode('<br>', array_map('sanitize', $errors)) ?>
        </div>
    <?php endif; ?>

    <div class="checkout-layout">
        <!-- Billing form -->
        <div class="fade-in">
            <h2 class="headline-4 mb-4">Adresse de facturation</h2>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Adresse</label>
                    <input type="text" name="adresse" class="form-control" placeholder="123 Rue de la Tech" value="<?= sanitize($adresse ?? '') ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Ville</label>
                        <input type="text" name="ville" class="form-control" placeholder="Paris" value="<?= sanitize($ville ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Code postal</label>
                        <input type="text" name="code_postal" class="form-control" placeholder="75001" value="<?= sanitize($code_postal ?? '') ?>" required>
                    </div>
                </div>

                <div class="divider"></div>

                <h2 class="headline-4 mb-3">Récapitulatif</h2>
                <?php foreach ($items as $item): ?>
                <div style="display: flex; align-items: center; gap: 16px; padding: 12px 0; border-bottom: 1px solid rgba(0,0,0,0.04);">
                    <?php if ($item['image'] && file_exists("uploads/produits/{$item['image']}")): ?>
                        <img src="uploads/produits/<?= sanitize($item['image']) ?>" alt="" style="width: 60px; height: 60px; border-radius: 10px; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 60px; height: 60px; background: var(--bg-secondary); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-box-seam" style="color: var(--text-tertiary);"></i>
                        </div>
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <strong style="font-size: 15px;"><?= sanitize($item['nom']) ?></strong>
                        <p class="caption">Qté: <?= $item['quantite'] ?></p>
                    </div>
                    <span style="font-weight: 600;"><?= formatPrice($item['prix'] * $item['quantite']) ?></span>
                </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-primary btn-lg w-100 mt-4">
                    Payer <?= formatPrice($total) ?>
                </button>
            </form>
        </div>

        <!-- Order summary sidebar -->
        <div class="cart-summary fade-in">
            <h3>Votre commande</h3>
            <div class="cart-summary-row">
                <span>Articles (<?= count($items) ?>)</span>
                <span><?= formatPrice($total) ?></span>
            </div>
            <div class="cart-summary-row">
                <span>Livraison</span>
                <span style="color: var(--success);">Gratuit</span>
            </div>
            <div class="cart-summary-row total">
                <span>Total</span>
                <span><?= formatPrice($total) ?></span>
            </div>
            <div class="divider"></div>
            <div style="text-align: center;">
                <p class="caption">Votre solde après achat</p>
                <p style="font-size: 24px; font-weight: 700; color: <?= ($balance - $total) >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                    <?= formatPrice($balance - $total) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div style="height: 80px;"></div>

<?php require_once 'includes/footer.php'; ?>
