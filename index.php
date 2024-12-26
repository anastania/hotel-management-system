<?php
session_start();
require_once "includes/config.php";

// Récupérer les chambres en vedette avec toutes les informations nécessaires
$sql = "SELECT c.id_chambre, c.type_chambre, c.prix, c.nombre_lits, c.disponibilite,
        h.nom_hotel, h.ville
        FROM chambres c 
        JOIN hotels h ON c.id_hotel = h.id_hotel 
        WHERE c.disponibilite = 1 
        LIMIT 6";
$result = mysqli_query($conn, $sql);

// Vérifier s'il y a une erreur dans la requête
if (!$result) {
    error_log("Erreur MySQL: " . mysqli_error($conn));
}

// Tableau d'images par défaut
$default_images = [
    'assets/pictures/g1.jpg',
    'assets/pictures/g2.jpg',
    'assets/pictures/g3.jpg',
    'assets/pictures/hotel1.jpg'
];
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
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/pictures/hotel1.jpg');
            background-size: cover;
            background-position: center;
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            position: relative;
        }
        
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            backdrop-filter: blur(5px);
            opacity: 0;
            transform: translateY(50px);
            animation: slideUp 1s ease forwards;
        }
        
        .search-box {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            margin-top: 2rem;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease forwards;
            animation-delay: 0.5s;
        }
        
        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            align-items: center;
        }
        
        .search-form .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .search-form .btn {
            min-width: 150px;
        }
        
        .featured-rooms {
            padding: 5rem 0;
            background-color: #f8f9fa;
        }
        
        .room-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }
        
        .room-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .room-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .room-image-container {
            position: relative;
            padding-top: 66.67%; /* 3:2 Aspect Ratio */
            overflow: hidden;
        }
        
        .room-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .room-card:hover .room-image {
            transform: scale(1.1);
        }
        
        .room-details {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .room-price {
            font-size: 1.25rem;
            color: var(--forest-green);
            font-weight: bold;
            margin: 1rem 0;
        }

        .room-features {
            margin: 1rem 0;
        }

        .room-features p {
            margin-bottom: 0.5rem;
            color: #666;
        }

        .room-actions {
            margin-top: 1rem;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background-color: var(--forest-green);
        }
        
        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 768px) {
            .hero-section {
                min-height: 60vh;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .search-form .form-group {
                width: 100%;
            }
            
            .room-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="display-4 mb-4">Découvrez le confort ultime</h1>
                <p class="lead mb-4">Réservez votre séjour de rêve dans nos hôtels de luxe</p>
                
                <div class="search-box">
                    <form action="search.php" method="GET" class="search-form">
                        <div class="form-group">
                            <input type="text" name="destination" class="form-control" placeholder="Destination" required>
                        </div>
                        <div class="form-group">
                            <input type="date" name="check_in" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <input type="date" name="check_out" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Rechercher</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <section class="featured-rooms">
            <div class="container">
                <h2 class="section-title">Chambres en vedette</h2>
                <div class="room-grid">
                    <?php
                    if ($result && mysqli_num_rows($result) > 0) {
                        $image_index = 0;
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Utiliser une image par défaut de manière cyclique
                            $image = $default_images[$image_index % count($default_images)];
                            $image_index++;
                            ?>
                            <div class="room-card">
                                <div class="room-image-container">
                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($row['type_chambre']); ?>" class="room-image">
                                </div>
                                <div class="room-details">
                                    <div>
                                        <h3><?php echo htmlspecialchars($row['type_chambre']); ?></h3>
                                        <p class="text-muted">
                                            <i class="fas fa-hotel"></i> <?php echo htmlspecialchars($row['nom_hotel']); ?><br>
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['ville']); ?>
                                        </p>
                                        <div class="room-features">
                                            <p><i class="fas fa-bed"></i> <?php echo htmlspecialchars($row['nombre_lits']); ?> lit(s)</p>
                                        </div>
                                        <div class="room-price">
                                            <i class="fas fa-tag"></i> <?php echo number_format($row['prix'], 2); ?> € / nuit
                                        </div>
                                    </div>
                                    <div class="room-actions">
                                        <a href="chambre_details.php?id=<?php echo $row['id_chambre']; ?>" class="btn btn-primary w-100">
                                            <i class="fas fa-info-circle"></i> Voir les détails
                                        </a>
                                        <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                                            <a href="reservation.php?chambre=<?php echo $row['id_chambre']; ?>" class="btn btn-success mt-2 w-100">
                                                <i class="fas fa-calendar-plus"></i> Réserver
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        ?>
                        <div class="alert alert-info text-center w-100">
                            <i class="fas fa-info-circle"></i> Aucune chambre disponible pour le moment.
                        </div>
                        <?php
                    }
                    ?>
                </div>
                
                <div class="text-center mt-4">
                    <a href="chambres.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-th-list"></i> Voir toutes nos chambres
                    </a>
                </div>
            </div>
        </section>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Set minimum date for check-in and check-out
        const today = new Date().toISOString().split('T')[0];
        document.querySelector('input[name="check_in"]').min = today;
        document.querySelector('input[name="check_out"]').min = today;
        
        // Update check-out minimum date when check-in is selected
        document.querySelector('input[name="check_in"]').addEventListener('change', function() {
            document.querySelector('input[name="check_out"]').min = this.value;
        });
    </script>
</body>
</html>
