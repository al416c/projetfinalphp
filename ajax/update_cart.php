<?php
require_once '../config/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$panierId = (int) ($_POST['panier_id'] ?? 0);
$quantite = (int) ($_POST['quantite'] ?? 0);

if ($panierId <= 0 || $quantite <= 0) {
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
    exit;
}

try {
    // Verify the cart item belongs to current user/session
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    $sessionId = !isLoggedIn() ? session_id() : null;

    if ($userId) {
        $stmt = $pdo->prepare("
            SELECT p.id, p.article_id, a.prix, COALESCE(s.quantite, 0) AS stock
            FROM panier p
            JOIN articles a ON p.article_id = a.id
            LEFT JOIN stock s ON a.id = s.article_id
            WHERE p.id = ? AND p.user_id = ?
        ");
        $stmt->execute([$panierId, $userId]);
    } else {
        $stmt = $pdo->prepare("
            SELECT p.id, p.article_id, a.prix, COALESCE(s.quantite, 0) AS stock
            FROM panier p
            JOIN articles a ON p.article_id = a.id
            LEFT JOIN stock s ON a.id = s.article_id
            WHERE p.id = ? AND p.session_id = ?
        ");
        $stmt->execute([$panierId, $sessionId]);
    }
    $item = $stmt->fetch();

    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Article non trouvé dans le panier']);
        exit;
    }

    if ($quantite > $item['stock']) {
        echo json_encode(['success' => false, 'message' => 'Stock insuffisant (max: ' . $item['stock'] . ')']);
        exit;
    }

    // Update quantity
    $stmt = $pdo->prepare("UPDATE panier SET quantite = ? WHERE id = ?");
    $stmt->execute([$quantite, $panierId]);

    $lineTotal = $item['prix'] * $quantite;
    $cartCount = getCartCount($pdo);

    // Compute new cart total
    if ($userId) {
        $stmt = $pdo->prepare("SELECT SUM(p.quantite * a.prix) AS total FROM panier p JOIN articles a ON p.article_id = a.id WHERE p.user_id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $pdo->prepare("SELECT SUM(p.quantite * a.prix) AS total FROM panier p JOIN articles a ON p.article_id = a.id WHERE p.session_id = ?");
        $stmt->execute([$sessionId]);
    }
    $cartTotal = $stmt->fetchColumn() ?: 0;

    echo json_encode([
        'success' => true,
        'message' => 'Quantité mise à jour',
        'lineTotal' => formatPrice($lineTotal),
        'cartTotal' => formatPrice($cartTotal),
        'cartCount' => $cartCount
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
