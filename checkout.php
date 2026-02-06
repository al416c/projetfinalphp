<?php
require_once 'config/init.php';

if (!isLoggedIn()) {
    redirect('connexion.php');
}

$stmt = $pdo->prepare("SELECT pa.*, p.nom, p.prix, p.image, p.stock FROM panier pa JOIN produits p ON pa.produit_id = p.id WHERE pa.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

if (empty($items)) {
    redirect('panier.php');
}

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['prix'] * $item['quantite'];
}
$shipping = $subtotal >= 50 ? 0 : 5;
$total = $subtotal + $shipping;

$pageTitle = 'Finaliser la commande - ' . SITE_NAME;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adresse = sanitize($_POST['adresse'] ?? '');

    if (empty($adresse)) {
        $errors[] = "L'adresse de livraison est requise.";
    }

    foreach ($items as $item) {
        if ($item['quantite'] > $item['stock']) {
            $errors[] = "Stock insuffisant pour " . $item['nom'];
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO commandes (user_id, total, adresse_livraison, statut) VALUES (?, ?, ?, 'en_attente')");
            $stmt->execute([$_SESSION['user_id'], $total, $adresse]);
            $commandeId = $pdo->lastInsertId();

            foreach ($items as $item) {
                $stmt = $pdo->prepare("INSERT INTO commande_details (commande_id, produit_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
                $stmt->execute([$commandeId, $item['produit_id'], $item['quantite'], $item['prix']]);

                $stmt = $pdo->prepare("UPDATE produits SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantite'], $item['produit_id']]);
            }

            $stmt = $pdo->prepare("DELETE FROM panier WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            $pdo->commit();

            redirect('confirmation.php?id=' . $commandeId);
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Une erreur est survenue lors de la commande.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Finaliser la commande</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-7">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Adresse de livraison</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="checkout-form">
                        <div class="mb-3">
                            <label class="form-label">Adresse complète</label>
                            <textarea name="adresse" class="form-control" rows="3" required><?= htmlspecialchars($user['adresse'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-lock"></i> Confirmer la commande
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Récapitulatif</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($items as $item): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span><?= htmlspecialchars($item['nom']) ?> x<?= $item['quantite'] ?></span>
                        <span><?= number_format($item['prix'] * $item['quantite'], 2, ',', ' ') ?> €</span>
                    </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sous-total</span>
                        <span><?= number_format($subtotal, 2, ',', ' ') ?> €</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Livraison</span>
                        <span><?= $shipping > 0 ? number_format($shipping, 2, ',', ' ') . ' €' : 'Gratuite' ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total</strong>
                        <strong class="text-primary"><?= number_format($total, 2, ',', ' ') ?> €</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
