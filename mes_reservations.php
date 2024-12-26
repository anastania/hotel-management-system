<?php
session_start();
require_once "includes/config.php";
require_once "includes/db.php";

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Get client ID from session
if(!isset($_SESSION["id_client"])) {
    header("location: login.php");
    exit;
}

$id_client = $_SESSION["id_client"];

// Récupérer les réservations de l'utilisateur
$sql = "SELECT r.*, c.type_chambre, c.prix, h.nom_hotel,
        DATEDIFF(r.date_depart, r.date_arrivee) as nombre_nuits
        FROM reservations r 
        JOIN chambres c ON r.id_chambre = c.id_chambre 
        JOIN hotels h ON c.id_hotel = h.id_hotel 
        WHERE r.id_client = ? 
        ORDER BY r.date_arrivee DESC";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id_client);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Mes Réservations";
    include 'includes/head.php'; 
    ?>
    <style>
        .reservation-card {
            transition: transform 0.2s;
        }
        .reservation-card:hover {
            transform: translateY(-5px);
        }
        .price-details {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <h2 class="mb-4">
            <i class="fas fa-list"></i> Mes Réservations
        </h2>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> 
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> 
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if(mysqli_num_rows($result) > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php while($reservation = mysqli_fetch_assoc($result)): 
                    $prix_total = $reservation['prix'] * $reservation['nombre_nuits'];
                ?>
                    <div class="col">
                        <div class="card reservation-card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-hotel"></i> 
                                    <?php echo htmlspecialchars($reservation['nom_hotel']); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <p class="mb-1">
                                        <i class="fas fa-bed"></i> 
                                        Type: <?php echo htmlspecialchars($reservation['type_chambre']); ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="fas fa-calendar-alt"></i> 
                                        Du <?php echo date('d/m/Y', strtotime($reservation['date_arrivee'])); ?>
                                        au <?php echo date('d/m/Y', strtotime($reservation['date_depart'])); ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="fas fa-moon"></i> 
                                        Durée: <?php echo $reservation['nombre_nuits']; ?> nuit(s)
                                    </p>
                                </div>
                                
                                <div class="price-details p-2 bg-light rounded">
                                    <p class="mb-1">
                                        <i class="fas fa-euro-sign"></i> 
                                        Prix par nuit: <?php echo number_format($reservation['prix'], 2); ?> €
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-calculator"></i> 
                                        Prix total: <?php echo number_format($prix_total, 2); ?> €
                                    </p>
                                </div>
                                
                                <div class="mt-3">
                                    <span class="badge bg-<?php echo $reservation['status'] == 'confirmed' ? 'success' : 'warning'; ?>">
                                        <i class="fas fa-<?php echo $reservation['status'] == 'confirmed' ? 'check' : 'clock'; ?>"></i>
                                        <?php echo $reservation['status'] == 'confirmed' ? 'Confirmée' : 'En attente'; ?>
                                    </span>
                                </div>
                                
                                <?php if($reservation['status'] !== 'confirmed'): ?>
                                    <div class="mt-3">
                                        <form method="POST" action="process_payment.php">
                                            <input type="hidden" name="id_chambre" value="<?php echo $reservation['id_chambre']; ?>">
                                            <input type="hidden" name="date_arrivee" value="<?php echo $reservation['date_arrivee']; ?>">
                                            <input type="hidden" name="date_depart" value="<?php echo $reservation['date_depart']; ?>">
                                            <input type="hidden" name="total_amount" value="<?php echo $prix_total; ?>">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-credit-card"></i> Payer maintenant
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($reservation['date_arrivee'] > date('Y-m-d') && $reservation['status'] !== 'confirmed'): ?>
                                <div class="card-footer">
                                    <a href="annuler_reservation.php?id=<?php echo $reservation['id_reservation']; ?>" 
                                       class="btn btn-danger btn-sm w-100"
                                       onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                        <i class="fas fa-times"></i> Annuler la réservation
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Vous n'avez pas encore de réservations.
                <a href="chambres.php" class="alert-link">Parcourir les chambres disponibles</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
