<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Réservation d'Hôtel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/Gestion_reservation_hotel/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/Gestion_reservation_hotel/index.php">
                <i class="fas fa-hotel"></i> Hôtel de Luxe
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/Gestion_reservation_hotel/index.php">
                            <i class="fas fa-home"></i> Accueil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Gestion_reservation_hotel/chambres.php">
                            <i class="fas fa-bed"></i> Chambres
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Gestion_reservation_hotel/contact.php">
                            <i class="fas fa-envelope"></i> Contact
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/Gestion_reservation_hotel/profile.php">
                                <i class="fas fa-user"></i> Mon Profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Gestion_reservation_hotel/mes_reservations.php">
                                <i class="fas fa-calendar-check"></i> Mes Réservations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Gestion_reservation_hotel/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </li>
                    <?php elseif(isset($_SESSION["admin_loggedin"]) && $_SESSION["admin_loggedin"] === true): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/Gestion_reservation_hotel/admin/">
                                <i class="fas fa-user-shield"></i> Administration
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Gestion_reservation_hotel/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/Gestion_reservation_hotel/login.php">
                                <i class="fas fa-sign-in-alt"></i> Connexion
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Gestion_reservation_hotel/register.php">
                                <i class="fas fa-user-plus"></i> Inscription
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <style>
    .navbar.sticky-top {
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,.1);
    }
    </style>
