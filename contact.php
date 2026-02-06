<?php
require_once 'config/init.php';

$pageTitle = 'Contact - ' . SITE_NAME;
$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = sanitize($_POST['nom'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $sujet = sanitize($_POST['sujet'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($nom)) $errors[] = "Le nom est requis.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
    if (empty($sujet)) $errors[] = "Le sujet est requis.";
    if (empty($message)) $errors[] = "Le message est requis.";

    if (empty($errors)) {
        $success = true;
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Contactez-nous</h1>

    <div class="row">
        <div class="col-md-6">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sujet</label>
                        <input type="text" name="sujet" class="form-control" value="<?= htmlspecialchars($_POST['sujet'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="5" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Envoyer
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5><i class="fas fa-map-marker-alt text-primary"></i> Adresse</h5>
                    <p>123 Rue du Commerce<br>75001 Paris, France</p>

                    <h5><i class="fas fa-phone text-primary"></i> Téléphone</h5>
                    <p>01 23 45 67 89</p>

                    <h5><i class="fas fa-envelope text-primary"></i> Email</h5>
                    <p>contact@monshop.com</p>

                    <h5><i class="fas fa-clock text-primary"></i> Horaires</h5>
                    <p>Lundi - Vendredi : 9h - 18h<br>Samedi : 10h - 16h</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
