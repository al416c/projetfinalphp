<?php
require_once __DIR__ . '/../config/init.php';

if (!isLoggedIn()) {
    redirect('/connexion.php');
}

$stmt = $pdo->prepare("UPDATE notifications SET lu = 1 WHERE user_id = ? AND lu = 0");
$stmt->execute([$_SESSION['user_id']]);

// Return JSON if AJAX, otherwise redirect back
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    $referer = $_SERVER['HTTP_REFERER'] ?? SITE_URL . '/compte.php';
    header('Location: ' . $referer);
}
exit;
