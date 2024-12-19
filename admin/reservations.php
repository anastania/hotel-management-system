<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Traitement de la suppression
if(isset($_GET["delete"]) && !empty($_GET["delete"])){
    $sql = "DELETE FROM reservations WHERE id_reservation = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $_GET["delete"]);
        if(mysqli_stmt_execute($stmt)){
            $_SESSION['success'] = "La réservation a été supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur s'est produite lors de la suppression.";
        }
        mysqli_stmt_close($stmt);
    }
    header("location: reservations.php");
    exit();
}

// Récupérer toutes les réservations avec les détails
$sql = "SELECT r.*, c.nom as client_nom, 
               ch.type_chambre, ch.id_chambre, h.nom_hotel,
               DATEDIFF(r.date_depart, r.date_arrivee) as duree_sejour
        FROM reservations r 
        JOIN clients c ON r.id_client = c.id_client 
        JOIN chambres ch ON r.id_chambre = ch.id_chambre 
        JOIN hotels h ON ch.id_hotel = h.id_hotel
        ORDER BY r.date_arrivee DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Gestion des Réservations";
    include '../includes/head.php'; 
    ?>
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1>
                        <i class="fas fa-calendar-check"></i> 
                        Gestion des Réservations
                    </h1>
                </div>

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

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag"></i> ID</th>
                                        <th><i class="fas fa-user"></i> Client</th>
                                        <th><i class="fas fa-hotel"></i> Hôtel</th>
                                        <th><i class="fas fa-bed"></i> Chambre</th>
                                        <th><i class="fas fa-calendar"></i> Arrivée</th>
                                        <th><i class="fas fa-calendar"></i> Départ</th>
                                        <th><i class="fas fa-clock"></i> Durée</th>
                                        <th><i class="fas fa-euro-sign"></i> Prix</th>
                                        <th><i class="fas fa-cogs"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($reservation = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo $reservation['id_reservation']; ?></td>
                                        <td><?php echo htmlspecialchars($reservation['client_nom']); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['nom_hotel']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($reservation['type_chambre']); ?>
                                            (N°<?php echo $reservation['id_chambre']; ?>)
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($reservation['date_arrivee'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($reservation['date_depart'])); ?></td>
                                        <td><?php echo $reservation['duree_sejour']; ?> nuits</td>
                                        <td><?php echo number_format($reservation['prix'], 2); ?> €</td>
                                        <td>
                                            <a href="edit_reservation.php?id=<?php echo $reservation['id_reservation']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="#" onclick="confirmDelete(<?php echo $reservation['id_reservation']; ?>)"
                                               class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id) {
            if(confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')) {
                window.location.href = 'reservations.php?delete=' + id;
            }
        }
    </script>
</body>
</html>
