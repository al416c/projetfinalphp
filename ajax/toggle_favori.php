c<?php
require_once '../config/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Connexion requise', 'redirect' => SITE_URL . '/connexion.php']);
    exit;
}

$articleId = (int) ($_POST['article_id'] ?? 0);

if ($articleId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Paramètres inv alides']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];

    // Check if already favorited
    $stmt = $pdo->prepare("SELECT id FROM favoris WHERE user_id = ? AND article_id = ?");
    $stmt->execute([$userId, $articleId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("DELETE FROM favoris WHERE id = ?");
        $stmt->execute([$existing['id']]);
        echo json_encode([
            'success' => true,
            'action' => 'removed',
            'message' => 'Retiré des favoris'
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO favoris (user_id, article_id) VALUES (?, ?)");
        $stmt->execute([$userId, $articleId]);
        echo json_encode([
            'success' => true,
            'action' => 'added',
            'message' => 'Ajouté aux favoris'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
