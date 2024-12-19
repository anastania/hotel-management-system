<?php
session_start();
require_once "includes/config.php";

// Récupérer toutes les chambres avec les détails de l'hôtel
$sql = "SELECT c.*, h.nom_hotel, h.adresse 
        FROM chambres c 
        JOIN hotels h ON c.id_hotel = h.id_hotel";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Nos Chambres - Hôtels de Luxe</title>
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
        .price {
            font-size: 1.25rem;
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-bed"></i> Nos Chambres
        </h1>
        
        <div class="row">
            <?php while($chambre = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4">
                    <div class="card hotel-card">
                        <img src="pictures/<?php echo strtolower($chambre['type_chambre']); ?>.jpg" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($chambre['type_chambre']); ?>"
                             onerror="this.src='pictures/default-room.jpg'">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($chambre['type_chambre']); ?>
                            </h5>
                            <h6 class="card-subtitle mb-2 text-muted">
                                <i class="fas fa-hotel"></i> 
                                <?php echo htmlspecialchars($chambre['nom_hotel']); ?>
                            </h6>
                            <p class="card-text">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($chambre['adresse']); ?><br>
                                <i class="fas fa-bed"></i> 
                                <?php echo $chambre['nombre_lits']; ?> lit(s)
                            </p>
                            <p class="price mb-3">
                                <i class="fas fa-tag"></i> 
                                <?php echo number_format($chambre['prix'], 2); ?> € / nuit
                            </p>
                            <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                                <a href="chambre_details.php?id=<?php echo $chambre['id_chambre']; ?>" 
                                   class="btn btn-primary btn-block">
                                    <i class="fas fa-calendar-plus"></i> Réserver maintenant
                                </a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-secondary btn-block">
                                    <i class="fas fa-sign-in-alt"></i> Connectez-vous pour réserver
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
