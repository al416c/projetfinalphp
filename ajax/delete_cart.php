<?php
require_once '../config/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

$panier_id = (int)($_POST['panier_id'] ?? 0);

if ($panier_id <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

if (isLoggedIn()) {
    $stmt = $pdo->prepare("DELETE FROM panier WHERE id = ? AND user_id = ?");
    $stmt->execute([$panier_id, $_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("DELETE FROM panier WHERE id = ? AND session_id = ?");
    $stmt->execute([$panier_id, session_id()]);
}

echo json_encode(['success' => true]);
