<?php
session_start();
require_once 'includes/header.php';
?>

<div class="container mt-5">
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">Erreur</h4>
        <?php if (isset($_SESSION['error'])): ?>
            <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php else: ?>
            <p>Une erreur inattendue s'est produite.</p>
        <?php endif; ?>
        <hr>
        <p class="mb-0">
            <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
