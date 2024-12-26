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
            position: relative;
            overflow: hidden;
        }
        
        .hero-content {
            opacity: 0;
            transform: translateY(50px);
            animation: slideUp 1s ease forwards;
        }
        
        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .search-box {
            margin-bottom: 2rem;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease forwards;
            animation-delay: 1s;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .featured-rooms {
            padding: 3rem 0;
        }
        
        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 2rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: var(--forest-green);
            animation: lineWidth 1s ease forwards;
            animation-delay: 0.5s;
        }
        
        @keyframes lineWidth {
            to {
                width: 100%;
            }
        }
        
        .room-card {
            height: 100%;
            transition: all 0.5s ease;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .room-card.animate {
            opacity: 1;
            transform: translateY(0);
        }
        
        .room-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .room-image {
            height: 200px;
            object-fit: cover;
            transition: all 0.5s ease;
        }
        
        .room-card:hover .room-image {
            transform: scale(1.1);
        }
        
        .features-section {
            background-color: var(--cloud-white);
            padding: 3rem 0;
            margin: 3rem 0;
        }
        
        .feature-item {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.5s ease;
        }
        
        .feature-item.animate {
            opacity: 1;
            transform: translateY(0);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--forest-green);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .feature-item:hover .feature-icon {
            transform: scale(1.2) rotate(360deg);
        }
        
        .feature-image {
            transform: scale(0.9);
            transition: all 0.5s ease;
        }
        
        .feature-item:hover .feature-image {
            transform: scale(1);
        }
        
        .btn-primary {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .search-form input, .search-form button {
            opacity: 0;
            transform: translateY(20px);
            animation: formElements 0.5s ease forwards;
        }
        
        .search-form input:nth-child(1) { animation-delay: 0.2s; }
        .search-form input:nth-child(2) { animation-delay: 0.4s; }
        .search-form input:nth-child(3) { animation-delay: 0.6s; }
        .search-form button { animation-delay: 0.8s; }
        
        @keyframes formElements {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .btn:hover {
            transform: translateY(-2px);
            transition: transform 0.3s ease;
        }
        
        .feature-icon {
            transition: transform 0.3s ease;
        }
        
        .feature-icon:hover {
            transform: scale(1.1) rotate(10deg);
        }
        
        .room-image {
            transition: transform 0.5s ease;
        }
        
        .room-card:hover .room-image {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="display-4 mb-4" data-aos="fade-down">Bienvenue à l'Hôtel de Luxe</h1>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="200">Découvrez le confort et l'élégance dans nos chambres soigneusement aménagées</p>
                <div class="search-box">
                    <form action="chambres.php" method="GET" class="search-form">
                        <input type="text" name="city" placeholder="Nom de la ville" required class="form-control mb-2">
                        <input type="date" name="checkin_date" required class="form-control mb-2">
                        <input type="date" name="checkout_date" required class="form-control mb-2">
                        <button type="submit" class="btn btn-primary">Rechercher</button>
                    </form>
                </div>
                <a href="chambres.php" class="btn btn-primary btn-lg" data-aos="zoom-in" data-aos-delay="400">
                    <i class="fas fa-bed"></i> Découvrir nos chambres
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <section class="featured-rooms">
            <h2 class="text-center mb-4" data-aos="fade-up">
                <i class="fas fa-star"></i> Chambres en vedette
            </h2>
            
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php 
                $featured_images = [
                    'assets/pictures/g1.jpg',
                    'assets/pictures/g2.jpg',
                    'assets/pictures/g3.jpg'
                ];
                $delay = 0;
                while($chambre = mysqli_fetch_assoc($result)): 
                    $current_image = $featured_images[$delay % count($featured_images)];
                ?>
                    <div class="col">
                        <div class="card room-card" data-aos="fade-up" data-aos-delay="<?php echo $delay += 200; ?>">
                            <img src="<?php echo $current_image; ?>" class="card-img-top room-image" alt="<?php echo htmlspecialchars($chambre['type_chambre']); ?>">
                            <div class="card-body">
                                <h5 class="card-title" data-aos="fade-right" data-aos-delay="<?php echo $delay + 100; ?>">
                                    <i class="fas fa-bed"></i> <?php echo htmlspecialchars($chambre['type_chambre']); ?>
                                </h5>
                                <p class="card-text room-features" data-aos="fade-right" data-aos-delay="<?php echo $delay + 200; ?>">
                                    <i class="fas fa-hotel"></i> <?php echo htmlspecialchars($chambre['nom_hotel']); ?><br>
                                    <i class="fas fa-bed"></i> <?php echo $chambre['nombre_lits']; ?> lit(s)
                                </p>
                                <p class="price" data-aos="fade-up" data-aos-delay="<?php echo $delay + 300; ?>">
                                    <i class="fas fa-tag"></i> <?php echo number_format($chambre['prix'], 2); ?> € / nuit
                                </p>
                                <div class="d-grid gap-2" data-aos="fade-up" data-aos-delay="<?php echo $delay + 400; ?>">
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
            
            <div class="text-center mt-4" data-aos="fade-up" data-aos-delay="600">
                <a href="chambres.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-th-list"></i> Voir toutes nos chambres
                </a>
            </div>
        </section>

        <section class="features-section">
            <div class="container">
                <h2 class="text-center mb-4" data-aos="fade-up">Nos Services</h2>
                <div class="row text-center">
                    <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-wifi"></i>
                            </div>
                            <h3>WiFi Gratuit</h3>
                            <p>Restez connecté pendant votre séjour</p>
                            <img src="assets/pictures/food.jpg" class="img-fluid rounded feature-image" alt="WiFi">
                        </div>
                    </div>
                    <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="400">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-utensils"></i>
                            </div>
                            <h3>Restaurant</h3>
                            <p>Savourez notre cuisine raffinée</p>
                            <img src="assets/pictures/swimingpool.jpg" class="img-fluid rounded feature-image" alt="Restaurant">
                        </div>
                    </div>
                    <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="600">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-spa"></i>
                            </div>
                            <h3>Spa & Bien-être</h3>
                            <p>Détendez-vous dans notre espace bien-être</p>
                            <img src="assets/pictures/spa.jpg" class="img-fluid rounded feature-image" alt="Spa">
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-out',
            once: true
        });
        
        // Add animation class to room cards when they come into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                }
            });
        }, {
            threshold: 0.1
        });
        
        document.querySelectorAll('.room-card, .feature-item').forEach((el) => observer.observe(el));
        
        // Add date validation
        document.querySelector('input[name="checkin_date"]').min = new Date().toISOString().split('T')[0];
        document.querySelector('input[name="checkin_date"]').addEventListener('change', function() {
            document.querySelector('input[name="checkout_date"]').min = this.value;
        });
    </script>
</body>
</html>
