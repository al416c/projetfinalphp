<?php
require_once 'config/init.php';
$pageTitle = 'Inscription';

if (isLoggedIn()) redirect('compte.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = 'Tous les champs sont requis.';
    }
    if (strlen($username) < 3) {
        $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email invalide.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    }
    if ($password !== $password_confirm) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }

    // Check unique username
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Ce nom d\'utilisateur est déjà pris.';
        }
    }

    // Check unique email
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Cet email est déjà utilisé.';
        }
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword]);

        $userId = $pdo->lastInsertId();

        // Auto-login after registration (per PDF requirement)
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = 'user';

        // Transfer session cart
        $stmtTransfer = $pdo->prepare("UPDATE panier SET user_id = ?, session_id = NULL WHERE session_id = ?");
        $stmtTransfer->execute([$userId, session_id()]);

        setFlash('success', 'Bienvenue sur NOVA, ' . $username . ' ! Votre compte a été créé avec un solde de 500,00 €.');
        redirect('index.php');
    }
}

require_once 'includes/header.php';
?>

<section class="section-alt" style="min-height: calc(100vh - var(--nav-height)); display: flex; align-items: center;">
    <div class="container">
        <div class="form-card fade-in">
            <h2>Créer un compte</h2>
            <p class="form-subtitle">Rejoignez la communauté NOVA.</p>

            <?php if ($errors): ?>
                <div class="flash flash-error" style="position: static; animation: none; margin-bottom: 20px;">
                    <?= implode('<br>', array_map('sanitize', $errors)) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nom d'utilisateur</label>
                    <input type="text" name="username" class="form-control" placeholder="alex_tech" value="<?= sanitize($username ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="votre@email.com" value="<?= sanitize($email ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimum 6 caractères" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirm" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary">Créer mon compte</button>
            </form>
            <p class="form-footer">
                Déjà un compte ? <a href="connexion.php">Se connecter</a>
            </p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
