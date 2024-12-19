<?php
session_start();
require_once "includes/config.php";

// Récupérer tous les hôtels
$sql = "SELECT * FROM hotels";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Nos Hôtels";
    include 'includes/head.php'; 
    ?>
    <title>Nos Hôtels - Hôtels de Luxe</title>
    <style>
        .hotel-card {
            transition: transform 0.3s ease;
            margin-bottom: 30px;
        }
        .hotel-card:hover {
            transform: translateY(-5px);
        }
        .card-img-top {
            height: 250px;
            object-fit: cover;
            width: 100%;
        }
        .hotel-features {
            color: var(--storm-cloud);
        }
        .hotel-features i {
            width: 20px;
            text-align: center;
            margin-right: 5px;
        }
        .price-range {
            color: var(--forest-green);
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-hotel"></i> Nos Hôtels
        </h1>
        
        <?php if(mysqli_num_rows($result) > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php while($hotel = mysqli_fetch_assoc($result)): ?>
                    <div class="col">
                        <div class="card hotel-card">
                            <img src="<?php echo !empty($hotel['image']) ? htmlspecialchars($hotel['image']) : 'assets/images/hotels/hotel'.rand(1,3).'.jpg'; ?>" 
                                 class="card-img-top hotel-image" 
                                 alt="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-hotel"></i> <?php echo htmlspecialchars($hotel['nom_hotel']); ?>
                                </h5>
                                <p class="card-text hotel-features">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['adresse']); ?><br>
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($hotel['telephone']); ?><br>
                                    <i class="fas fa-star"></i> <?php echo $hotel['etoiles']; ?> étoiles
                                </p>
                                <a href="hotel_details.php?id=<?php echo $hotel['id_hotel']; ?>" 
                                   class="btn btn-primary btn-block">
                                    <i class="fas fa-info-circle"></i> Plus de détails
                                </a>
                                <div class="d-grid gap-2">
                                    <a href="chambres.php?hotel=<?php echo $hotel['id_hotel']; ?>" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Voir les chambres
                                    </a>
                                    <a href="contact.php?hotel=<?php echo $hotel['id_hotel']; ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-envelope"></i> Contacter l'hôtel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> Aucun hôtel disponible pour le moment.
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
