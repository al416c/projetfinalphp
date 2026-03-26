<?php
require_once 'config/init.php';
$pageTitle = 'Contact';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = cleanInput($_POST['nom'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $sujet = cleanInput($_POST['sujet'] ?? '');
    $message = cleanInput($_POST['message'] ?? '');

    if (!empty($nom) && !empty($email) && !empty($message)) {
        $success = true;
    }
}

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1 class="fade-in">Contact</h1>
        <p class="fade-in">Une question ? Nous sommes là pour vous aider.</p>
    </div>
</div>

<div class="container-wide">
    <div class="contact-grid">
        <div class="fade-in">
            <?php if ($success): ?>
                <div style="text-align: center; padding: 60px 0;">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(48,209,88,0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="bi bi-check-lg" style="font-size: 28px; color: var(--success);"></i>
                    </div>
                    <h3 class="headline-4">Message envoyé !</h3>
                    <p class="text-secondary mt-2">Nous vous répondrons dans les plus brefs délais.</p>
                </div>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" placeholder="Votre nom" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="votre@email.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sujet</label>
                        <input type="text" name="sujet" class="form-control" placeholder="Objet de votre message">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" placeholder="Votre message..." rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Envoyer le message</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="contact-info fade-in">
            <h3>Informations de contact</h3>
            <p>N'hésitez pas à nous contacter par email ou via le formulaire ci-contre.</p>

            <div class="contact-info-item">
                <i class="bi bi-envelope"></i>
                <div>
                    <strong>Email</strong>
                    <p>contact@nova-market.com</p>
                </div>
            </div>
            <div class="contact-info-item">
                <i class="bi bi-clock"></i>
                <div>
                    <strong>Horaires</strong>
                    <p>Lundi - Vendredi, 9h - 18h</p>
                </div>
            </div>
            <div class="contact-info-item">
                <i class="bi bi-geo-alt"></i>
                <div>
                    <strong>Adresse</strong>
                    <p>42 Rue de l'Innovation<br>75001 Paris, France</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
