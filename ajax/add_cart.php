<?php
require_once '../config/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$produit_id = (int)($_POST['produit_id'] ?? 0);
$quantite = (int)($_POST['quantite'] ?? 1);

if ($produit_id <= 0 || $quantite <= 0) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$stmt = $pdo->prepare("SELECT stock FROM produits WHERE id = ?");
$stmt->execute([$produit_id]);
$produit = $stmt->fetch();

if (!$produit) {
    echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
    exit;
}

if ($quantite > $produit['stock']) {
    echo json_encode(['success' => false, 'message' => 'Stock insuffisant']);
    exit;
}

if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT id, quantite FROM panier WHERE user_id = ? AND produit_id = ?");
    $stmt->execute([$_SESSION['user_id'], $produit_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        $newQty = min($existing['quantite'] + $quantite, $produit['stock']);
        $stmt = $pdo->prepare("UPDATE panier SET quantite = ? WHERE id = ?");
        $stmt->execute([$newQty, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO panier (user_id, produit_id, quantite) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $produit_id, $quantite]);
    }

    $stmt = $pdo->prepare("SELECT SUM(quantite) as total FROM panier WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("SELECT id, quantite FROM panier WHERE session_id = ? AND produit_id = ?");
    $stmt->execute([session_id(), $produit_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        $newQty = min($existing['quantite'] + $quantite, $produit['stock']);
        $stmt = $pdo->prepare("UPDATE panier SET quantite = ? WHERE id = ?");
        $stmt->execute([$newQty, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO panier (session_id, produit_id, quantite) VALUES (?, ?, ?)");
        $stmt->execute([session_id(), $produit_id, $quantite]);
    }

    $stmt = $pdo->prepare("SELECT SUM(quantite) as total FROM panier WHERE session_id = ?");
    $stmt->execute([session_id()]);
}

$result = $stmt->fetch();
$cartCount = $result['total'] ?? 0;

echo json_encode(['success' => true, 'cartCount' => $cartCount]);
