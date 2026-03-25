<?php
session_start();

require_once __DIR__ . '/database.php';

// ── Site config ──────────────────────────────────────
define('SITE_NAME', 'NOVA');
define('SITE_URL', 'http://localhost:8888/projetfinalphp'); // MAMP default port
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// ── Helper functions ─────────────────────────────────

function redirect(string $url): void {
    header("Location: $url");
    exit();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function sanitize(string $data): string {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function generateToken(): string {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function verifyToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function getCartCount(): int {
    global $pdo;
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantite), 0) FROM panier WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantite), 0) FROM panier WHERE session_id = ?");
        $stmt->execute([session_id()]);
    }
    return (int) $stmt->fetchColumn();
}

function getUserBalance(): float {
    global $pdo;
    if (!isLoggedIn()) return 0;
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return (float) $stmt->fetchColumn();
}

function formatPrice(float $price): string {
    return number_format($price, 2, ',', ' ') . ' €';
}

function timeAgo(string $datetime): string {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return 'il y a ' . $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
    if ($diff->m > 0) return 'il y a ' . $diff->m . ' mois';
    if ($diff->d > 0) return 'il y a ' . $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
    if ($diff->h > 0) return 'il y a ' . $diff->h . 'h';
    if ($diff->i > 0) return 'il y a ' . $diff->i . ' min';
    return 'à l\'instant';
}

function getAverageRating(int $articleId): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT AVG(note) as avg_note, COUNT(*) as count FROM commentaires WHERE article_id = ?");
    $stmt->execute([$articleId]);
    $result = $stmt->fetch();
    return [
        'average' => $result['avg_note'] ? round($result['avg_note'], 1) : 0,
        'count'   => (int) $result['count']
    ];
}

function isFavorited(int $articleId): bool {
    global $pdo;
    if (!isLoggedIn()) return false;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE user_id = ? AND article_id = ?");
    $stmt->execute([$_SESSION['user_id'], $articleId]);
    return $stmt->fetchColumn() > 0;
}

function renderStars(float $rating): string {
    $html = '<div class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($rating)) {
            $html .= '<i class="bi bi-star-fill"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $html .= '<i class="bi bi-star-half"></i>';
        } else {
            $html .= '<i class="bi bi-star"></i>';
        }
    }
    $html .= '</div>';
    return $html;
}

// ── Notifications ────────────────────────────────────

function getUnreadNotificationCount(): int {
    global $pdo;
    if (!isLoggedIn()) return 0;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND lu = 0");
    $stmt->execute([$_SESSION['user_id']]);
    return (int) $stmt->fetchColumn();
}

function createNotification(int $userId, string $message, string $lien = null, string $type = 'sale'): void {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message, lien) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $type, $message, $lien]);
}

// ── Flash messages ───────────────────────────────────

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
