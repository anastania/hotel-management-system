<?php
session_start();
require_once "includes/config.php";

// Récupérer les chambres en vedette
$sql = "SELECT c.*, h.nom_hotel 
        FROM chambres c 
        JOIN hotels h ON c.id_hotel = h.id_hotel 
        WHERE c.disponibilite = 1 
        LIMIT 3";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Accueil";
    include 'includes/head.php'; 
    ?>
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/pictures/hotel1.jpg');
            background-size: cover;
            background-position: center;
            height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .featured-rooms {
            padding: 3rem 0;
        }
        
        .room-card {
            height: 100%;
            transition: transform 0.3s ease;
        }
        
        .room-card:hover {
            transform: translateY(-5px);
        }
        
        .room-image {
            height: 200px;
            object-fit: cover;
        }
        
        .features-section {
            background-color: var(--cloud-white);
            padding: 3rem 0;
            margin: 3rem 0;
        }
        
        .feature-icon {
            font-size: 2rem;
            color: var(--forest-green);
            margin-bottom: 1rem;
        }
        
        .search-box {
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="hero-section">
        <div class="container">
            <h1 class="display-4 mb-4">Bienvenue à l'Hôtel de Luxe</h1>
            <p class="lead mb-4">Découvrez le confort et l'élégance dans nos chambres soigneusement aménagées</p>
            <div class="search-box">
                <form action="chambres.php" method="GET">
                    <input type="text" name="city" placeholder="Nom de la ville" required style="padding: 0.5rem; border: 1px solid var(--sky-blue); border-radius: 4px;">
                    <input type="date" name="checkin_date" required style="padding: 0.5rem; border: 1px solid var(--sky-blue); border-radius: 4px;">
                    <input type="date" name="checkout_date" required style="padding: 0.5rem; border: 1px solid var(--sky-blue); border-radius: 4px;">
                    <button type="submit" style="padding: 0.5rem 1rem; background-color: var(--forest-green); color: white; border: none; border-radius: 4px;">Rechercher</button>
                </form>
            </div>
            <a href="chambres.php" class="btn btn-primary btn-lg">
                <i class="fas fa-bed"></i> Découvrir nos chambres
            </a>
        </div>
    </div>

    <div class="container">
        <section class="featured-rooms">
            <h2 class="text-center mb-4">
                <i class="fas fa-star"></i> Chambres en vedette
            </h2>
            
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php 
                // Array of room images for featured rooms
                $featured_images = [
                    'assets/pictures/g1.jpg',
                    'assets/pictures/g2.jpg',
                    'assets/pictures/g3.jpg',
                    'assets/pictures/g4.jpg',
                    'assets/pictures/g5.jpg',
                    'assets/pictures/g6.jpg',
                    'assets/pictures/g7.jpg',
                    'assets/pictures/g8.jpg',
                    'assets/pictures/g9.jpg',
                    'assets/pictures/g10.jpg'
                ];
                static $image_index = 0;
                while($chambre = mysqli_fetch_assoc($result)): 
                    $current_image = $featured_images[$image_index];
                    $image_index = ($image_index + 1) % count($featured_images);
                ?>
                    <div class="col">
                        <div class="card room-card">
                            <img src="<?php echo $current_image; ?>" class="card-img-top room-image" alt="<?php echo htmlspecialchars($chambre['type_chambre']); ?>">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-bed"></i> <?php echo htmlspecialchars($chambre['type_chambre']); ?>
                                </h5>
                                <p class="card-text room-features">
                                    <i class="fas fa-hotel"></i> <?php echo htmlspecialchars($chambre['nom_hotel']); ?><br>
                                    <i class="fas fa-bed"></i> <?php echo $chambre['nombre_lits']; ?> lit(s)
                                </p>
                                <p class="price">
                                    <i class="fas fa-tag"></i> <?php echo number_format($chambre['prix'], 2); ?> € / nuit
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="chambre_details.php?id=<?php echo $chambre['id_chambre']; ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-info-circle"></i> Voir plus
                                    </a>
                                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                                        <a href="reservation.php?chambre=<?php echo $chambre['id_chambre']; ?>" class="btn btn-primary">
                                            <i class="fas fa-calendar-plus"></i> Réserver
                                        </a>
                                    <?php else: ?>
                                        <a href="login.php?redirect=index.php" class="btn btn-secondary">
                                            <i class="fas fa-sign-in-alt"></i> Connectez-vous pour réserver
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="chambres.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-th-list"></i> Voir toutes nos chambres
                </a>
            </div>
        </section>

        <section class="features-section">
            <div class="container">
                <h2 class="text-center mb-4">Nos Services</h2>
                <div class="row text-center">
                    <div class="col-md-4 mb-4">
                        <div class="feature-icon">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <h3>WiFi Gratuit</h3>
                        <p>Restez connecté pendant votre séjour</p>
                        <img src="assets/pictures/food.jpg" class="img-fluid rounded" alt="WiFi" style="height: 150px; object-fit: cover;">
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="feature-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h3>Restaurant</h3>
                        <p>Savourez notre cuisine raffinée</p>
                        <img src="assets/pictures/swimingpool.jpg" class="img-fluid rounded" alt="Restaurant" style="height: 150px; object-fit: cover;">
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="feature-icon">
                            <i class="fas fa-spa"></i>
                        </div>
                        <h3>Spa & Bien-être</h3>
                        <p>Détendez-vous dans notre espace bien-être</p>
                        <img src="assets/pictures/spa.jpg" class="img-fluid rounded" alt="Spa" style="height: 150px; object-fit: cover;">
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
