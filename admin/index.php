<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Statistiques
$stats = array();

// Total des réservations
$sql = "SELECT COUNT(*) as total FROM reservations";
$result = mysqli_query($conn, $sql);
$stats['reservations'] = mysqli_fetch_assoc($result)['total'];

// Total des clients
$sql = "SELECT COUNT(*) as total FROM clients";
$result = mysqli_query($conn, $sql);
$stats['clients'] = mysqli_fetch_assoc($result)['total'];

// Total des chambres
$sql = "SELECT COUNT(*) as total FROM chambres";
$result = mysqli_query($conn, $sql);
$stats['chambres'] = mysqli_fetch_assoc($result)['total'];

// Chambres disponibles
$sql = "SELECT COUNT(*) as total FROM chambres WHERE disponibilite = 1";
$result = mysqli_query($conn, $sql);
$stats['chambres_dispo'] = mysqli_fetch_assoc($result)['total'];

// Dernières réservations
$sql = "SELECT r.*, c.nom as client_nom, ch.type_chambre 
        FROM reservations r 
        JOIN clients c ON r.id_client = c.id_client 
        JOIN chambres ch ON r.id_chambre = ch.id_chambre 
        ORDER BY r.created_at DESC 
        LIMIT 5";
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

                <!-- Statistiques -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Réservations Totales</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['reservations']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Clients Enregistrés</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['clients']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Total des Chambres</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['chambres']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-bed fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Chambres Disponibles</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['chambres_dispo']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-door-open fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dernières Réservations -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clock"></i> Dernières Réservations
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-user"></i> Client</th>
                                        <th><i class="fas fa-bed"></i> Chambre</th>
                                        <th><i class="fas fa-calendar-alt"></i> Date d'arrivée</th>
                                        <th><i class="fas fa-calendar-alt"></i> Date de départ</th>
                                        <th><i class="fas fa-clock"></i> Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($reservation = mysqli_fetch_assoc($recent_reservations)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($reservation['client_nom']); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['type_chambre']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($reservation['date_arrivee'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($reservation['date_depart'])); ?></td>
                                        <td>
                                            <?php if(strtotime($reservation['date_depart']) < time()): ?>
                                                <span class="badge bg-secondary">Terminée</span>
                                            <?php elseif(strtotime($reservation['date_arrivee']) > time()): ?>
                                                <span class="badge bg-primary">À venir</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">En cours</span>
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
