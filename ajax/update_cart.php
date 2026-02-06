<?php
require_once '../config/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

$panier_id = (int)($_POST['panier_id'] ?? 0);
$quantite = (int)($_POST['quantite'] ?? 1);

if ($panier_id <= 0 || $quantite <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT pa.id, p.stock FROM panier pa JOIN produits p ON pa.produit_id = p.id WHERE pa.id = ? AND pa.user_id = ?");
    $stmt->execute([$panier_id, $_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("SELECT pa.id, p.stock FROM panier pa JOIN produits p ON pa.produit_id = p.id WHERE pa.id = ? AND pa.session_id = ?");
    $stmt->execute([$panier_id, session_id()]);
}

$item = $stmt->fetch();

if (!$item) {
    echo json_encode(['success' => false]);
    exit;
}

$quantite = min($quantite, $item['stock']);

$stmt = $pdo->prepare("UPDATE panier SET quantite = ? WHERE id = ?");
$stmt->execute([$quantite, $panier_id]);

echo json_encode(['success' => true]);
