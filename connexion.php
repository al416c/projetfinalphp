<?php
require_once 'config/init.php';
$pageTitle = 'Connexion';

if (isLoggedIn()) redirect('compte.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = 'Tous les champs sont requis.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];

            // Transfer session cart to user cart
            $stmtTransfer = $pdo->prepare("UPDATE panier SET user_id = ?, session_id = NULL WHERE session_id = ?");
            $stmtTransfer->execute([$user['id'], session_id()]);

            setFlash('success', 'Bienvenue, ' . $user['username'] . ' !');
            redirect('index.php');
        } else {
            $errors[] = 'Email ou mot de passe incorrect.';
        }
    }
}

require_once 'includes/header.php';
?>

<section class="section-alt" style="min-height: calc(100vh - var(--nav-height)); display: flex; align-items: center;">
    <div class="container">
        <div class="form-card fade-in">
            <h2>Connexion</h2>
            <p class="form-subtitle">Content de vous revoir sur NOVA.</p>

            <?php if ($errors): ?>
                <div class="flash flash-error" style="position: static; animation: none; margin-bottom: 20px;">
                    <?= implode('<br>', array_map('sanitize', $errors)) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="votre@email.com" value="<?= sanitize($email ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>
            <p class="form-footer">
                Pas encore de compte ? <a href="inscription.php">Créer un compte</a>
            </p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
