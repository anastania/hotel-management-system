<?php
session_start();
require_once "includes/config.php";

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST["nom"]);
    $email = trim($_POST["email"]);
    $sujet = trim($_POST["sujet"]);
    $message = trim($_POST["message"]);
    
    // Ici vous pouvez ajouter le code pour envoyer l'email ou sauvegarder le message
    $success = "Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Contact";
    include 'includes/head.php'; 
    ?>
    <style>
        .contact-form {
            background-color: var(--cloud-white);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .contact-info {
            background-color: var(--forest-green);
            color: var(--cloud-white);
            padding: 2rem;
            border-radius: 8px;
            height: 100%;
        }
        .contact-info i {
            width: 30px;
            margin-right: 10px;
        }
        .social-links a {
            color: var(--cloud-white);
            margin-right: 15px;
            font-size: 1.5rem;
            transition: opacity 0.3s ease;
        }
        .social-links a:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <h1 class="text-center mb-5">
            <i class="fas fa-envelope"></i> Contactez-nous
        </h1>

        <div class="row">
            <div class="col-md-8">
                <div class="contact-form">
                    <?php if($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <?php if($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="nom" class="form-label">
                                <i class="fas fa-user"></i> Nom complet
                            </label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="sujet" class="form-label">
                                <i class="fas fa-heading"></i> Sujet
                            </label>
                            <input type="text" class="form-control" id="sujet" name="sujet" required>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">
                                <i class="fas fa-comment"></i> Message
                            </label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Envoyer le message
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-md-4">
                <div class="contact-info">
                    <h3 class="mb-4">Informations de contact</h3>
                    
                    <p>
                        <i class="fas fa-map-marker-alt"></i>
                        123 Rue de l'Hôtel<br>
                        75001 Paris, France
                    </p>
                    
                    <p>
                        <i class="fas fa-phone"></i>
                        +33 1 23 45 67 89
                    </p>
                    
                    <p>
                        <i class="fas fa-envelope"></i>
                        contact@hotel-luxe.com
                    </p>
                    
                    <h4 class="mt-4 mb-3">Suivez-nous</h4>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
