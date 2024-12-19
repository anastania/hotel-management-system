<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Récupérer les statistiques
$stats = array();

// Nombre total de réservations
$sql = "SELECT COUNT(*) as total FROM reservations";
$result = mysqli_query($conn, $sql);
$stats['total_reservations'] = mysqli_fetch_assoc($result)['total'];

// Nombre de réservations en cours
$sql = "SELECT COUNT(*) as encours FROM reservations WHERE date_debut <= CURDATE() AND date_fin >= CURDATE()";
$result = mysqli_query($conn, $sql);
$stats['reservations_encours'] = mysqli_fetch_assoc($result)['encours'];

// Nombre total de clients
$sql = "SELECT COUNT(*) as total FROM clients";
$result = mysqli_query($conn, $sql);
$stats['total_clients'] = mysqli_fetch_assoc($result)['total'];

// Chiffre d'affaires total
$sql = "SELECT SUM(prix) as total FROM reservations";
$result = mysqli_query($conn, $sql);
$stats['chiffre_affaires'] = mysqli_fetch_assoc($result)['total'];

// Dernières réservations
$sql = "SELECT r.*, c.nom as client_nom, c.prenom as client_prenom, h.nom_hotel 
        FROM reservations r 
        JOIN clients c ON r.id_client = c.id_client 
        JOIN chambres ch ON r.id_chambre = ch.id_chambre 
        JOIN hotels h ON ch.id_hotel = h.id_hotel 
        ORDER BY r.date_debut DESC LIMIT 5";
$recent_reservations = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Tableau de Bord";
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
                        <i class="fas fa-tachometer-alt"></i> 
                        Tableau de Bord
                    </h1>
                </div>

                <!-- Cartes statistiques -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-primary text-white mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo $stats['total_reservations']; ?></h3>
                                        <div class="small">Réservations totales</div>
                                    </div>
                                    <i class="fas fa-calendar fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-success text-white mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo $stats['reservations_encours']; ?></h3>
                                        <div class="small">Réservations en cours</div>
                                    </div>
                                    <i class="fas fa-bed fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-info text-white mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo $stats['total_clients']; ?></h3>
                                        <div class="small">Clients inscrits</div>
                                    </div>
                                    <i class="fas fa-users fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-warning text-white mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo number_format($stats['chiffre_affaires'], 2); ?> €</h3>
                                        <div class="small">Chiffre d'affaires</div>
                                    </div>
                                    <i class="fas fa-euro-sign fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dernières réservations -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table me-1"></i>
                        Dernières réservations
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-user"></i> Client</th>
                                        <th><i class="fas fa-hotel"></i> Hôtel</th>
                                        <th><i class="fas fa-calendar"></i> Arrivée</th>
                                        <th><i class="fas fa-calendar"></i> Départ</th>
                                        <th><i class="fas fa-euro-sign"></i> Prix</th>
                                        <th><i class="fas fa-clock"></i> Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($reservation = mysqli_fetch_assoc($recent_reservations)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($reservation['client_nom'] . ' ' . $reservation['client_prenom']); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['nom_hotel']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($reservation['date_debut'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($reservation['date_fin'])); ?></td>
                                        <td><?php echo number_format($reservation['prix'], 2); ?> €</td>
                                        <td>
                                            <?php if(strtotime($reservation['date_fin']) < time()): ?>
                                                <span class="badge bg-secondary">Terminée</span>
                                            <?php elseif(strtotime($reservation['date_debut']) <= time()): ?>
                                                <span class="badge bg-success">En cours</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">À venir</span>
                                            <?php endif; ?>
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
</body>
</html>
