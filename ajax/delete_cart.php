<?php
require_once '../config/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$panierId = (int) ($_POST['panier_id'] ?? 0);

if ($panierId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
    exit;
}

try {
    // Verify ownership
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    $sessionId = !isLoggedIn() ? session_id() : null;

    if ($userId) {
        $stmt = $pdo->prepare("SELECT id FROM panier WHERE id = ? AND user_id = ?");
        $stmt->execute([$panierId, $userId]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM panier WHERE id = ? AND session_id = ?");
        $stmt->execute([$panierId, $sessionId]);
    }

    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Article non trouvé']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM panier WHERE id = ?");
    $stmt->execute([$panierId]);

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
        'message' => 'Article retiré du panier',
        'cartTotal' => formatPrice($cartTotal),
        'cartCount' => $cartCount
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
