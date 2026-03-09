<?php
require_once '../config/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$articleId = (int) ($_POST['article_id'] ?? 0);
$quantite = (int) ($_POST['quantite'] ?? 1);

if ($articleId <= 0 || $quantite <= 0) {
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
    exit;
}

try {
    // Check article exists and has stock
    $stmt = $pdo->prepare("
        SELECT a.id, a.nom, a.prix, a.auteur_id, COALESCE(s.quantite, 0) AS stock
        FROM articles a
        LEFT JOIN stock s ON a.id = s.article_id
        WHERE a.id = ?
    ");
    $stmt->execute([$articleId]);
    $article = $stmt->fetch();

    if (!$article) {
        echo json_encode(['success' => false, 'message' => 'Article introuvable']);
        exit;
    }

    // Don't allow buying own articles
    if (isLoggedIn() && $article['auteur_id'] == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas acheter vos propres articles']);
        exit;
    }

    if ($article['stock'] < $quantite) {
        echo json_encode(['success' => false, 'message' => 'Stock insuffisant']);
        exit;
    }

    // Determine user or session
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    $sessionId = !isLoggedIn() ? session_id() : null;

    // Check if article already in cart
    if ($userId) {
        $stmt = $pdo->prepare("SELECT id, quantite FROM panier WHERE user_id = ? AND article_id = ?");
        $stmt->execute([$userId, $articleId]);
    } else {
        $stmt = $pdo->prepare("SELECT id, quantite FROM panier WHERE session_id = ? AND article_id = ?");
        $stmt->execute([$sessionId, $articleId]);
    }
    $existing = $stmt->fetch();

    if ($existing) {
        $newQty = $existing['quantite'] + $quantite;
        if ($newQty > $article['stock']) {
            echo json_encode(['success' => false, 'message' => 'Stock insuffisant pour cette quantité']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE panier SET quantite = ? WHERE id = ?");
        $stmt->execute([$newQty, $existing['id']]);
    } else {
        if ($userId) {
            $stmt = $pdo->prepare("INSERT INTO panier (user_id, article_id, quantite) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $articleId, $quantite]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO panier (session_id, article_id, quantite) VALUES (?, ?, ?)");
            $stmt->execute([$sessionId, $articleId, $quantite]);
        }
    }

    // Get updated cart count
    $cartCount = getCartCount($pdo);

    echo json_encode([
        'success' => true,
        'message' => $article['nom'] . ' ajouté au panier',
        'cartCount' => $cartCount
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
