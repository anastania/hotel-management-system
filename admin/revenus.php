<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Période par défaut : mois en cours
$debut = date('Y-m-01');
$fin = date('Y-m-t');

// Si des dates sont spécifiées
if(isset($_GET['debut']) && isset($_GET['fin'])) {
    $debut = $_GET['debut'];
    $fin = $_GET['fin'];
}

// Statistiques globales
$sql = "SELECT 
            COUNT(*) as total_reservations,
            SUM(prix) as revenu_total,
            AVG(prix) as prix_moyen,
            MIN(prix) as prix_min,
            MAX(prix) as prix_max
        FROM reservations 
        WHERE date_arrivee BETWEEN ? AND ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $debut, $fin);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$stats = mysqli_fetch_assoc($result);

// Revenus par hôtel
$sql_hotels = "SELECT 
                h.nom_hotel,
                COUNT(r.id_reservation) as nombre_reservations,
                SUM(r.prix) as revenu_total,
                AVG(r.prix) as prix_moyen
              FROM hotels h
              LEFT JOIN chambres c ON h.id_hotel = c.id_hotel
              LEFT JOIN reservations r ON c.id_chambre = r.id_chambre
              AND r.date_arrivee BETWEEN ? AND ?
              GROUP BY h.id_hotel
              ORDER BY revenu_total DESC";

$stmt_hotels = mysqli_prepare($conn, $sql_hotels);
mysqli_stmt_bind_param($stmt_hotels, "ss", $debut, $fin);
mysqli_stmt_execute($stmt_hotels);
$result_hotels = mysqli_stmt_get_result($stmt_hotels);

// Revenus par type de chambre
$sql_types = "SELECT 
                c.type_chambre,
                COUNT(r.id_reservation) as nombre_reservations,
                SUM(r.prix) as revenu_total,
                AVG(r.prix) as prix_moyen
              FROM chambres c
              LEFT JOIN reservations r ON c.id_chambre = r.id_chambre
              AND r.date_arrivee BETWEEN ? AND ?
              GROUP BY c.type_chambre
              ORDER BY revenu_total DESC";

$stmt_types = mysqli_prepare($conn, $sql_types);
mysqli_stmt_bind_param($stmt_types, "ss", $debut, $fin);
mysqli_stmt_execute($stmt_types);
$result_types = mysqli_stmt_get_result($stmt_types);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Revenus";
    include '../includes/head.php'; 
    ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1>
                        <i class="fas fa-euro-sign"></i> 
                        Revenus
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <form class="row g-3">
                            <div class="col-auto">
                                <input type="text" class="form-control" id="date_debut" name="debut" 
                                       value="<?php echo $debut; ?>" placeholder="Date de début">
                            </div>
                            <div class="col-auto">
                                <input type="text" class="form-control" id="date_fin" name="fin" 
                                       value="<?php echo $fin; ?>" placeholder="Date de fin">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filtrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Résumé global -->
                <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-chart-line"></i> 
                                    Revenus totaux
                                </h5>
                                <p class="card-text">
                                    <span class="h3"><?php echo number_format($stats['revenu_total'], 2); ?> €</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-calculator"></i> 
                                    Prix moyen
                                </h5>
                                <p class="card-text">
                                    <span class="h3"><?php echo number_format($stats['prix_moyen'], 2); ?> €</span><br>
                                    <small class="text-muted">
                                        Min: <?php echo number_format($stats['prix_min'], 2); ?> € | 
                                        Max: <?php echo number_format($stats['prix_max'], 2); ?> €
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-calendar-check"></i> 
                                    Réservations
                                </h5>
                                <p class="card-text">
                                    <span class="h3"><?php echo $stats['total_reservations']; ?></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenus par hôtel -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Revenus par hôtel</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Hôtel</th>
                                        <th>Réservations</th>
                                        <th>Revenus</th>
                                        <th>Prix moyen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($hotel = mysqli_fetch_assoc($result_hotels)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($hotel['nom_hotel']); ?></td>
                                            <td><?php echo $hotel['nombre_reservations']; ?></td>
                                            <td><?php echo number_format($hotel['revenu_total'], 2); ?> €</td>
                                            <td><?php echo number_format($hotel['prix_moyen'], 2); ?> €</td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Revenus par type de chambre -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Revenus par type de chambre</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Type de chambre</th>
                                        <th>Réservations</th>
                                        <th>Revenus</th>
                                        <th>Prix moyen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($type = mysqli_fetch_assoc($result_types)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($type['type_chambre']); ?></td>
                                            <td><?php echo $type['nombre_reservations']; ?></td>
                                            <td><?php echo number_format($type['revenu_total'], 2); ?> €</td>
                                            <td><?php echo number_format($type['prix_moyen'], 2); ?> €</td>
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
        flatpickr.localize(flatpickr.l10ns.fr);
        flatpickr("#date_debut", {
            dateFormat: "Y-m-d"
        });
        flatpickr("#date_fin", {
            dateFormat: "Y-m-d"
        });
    </script>
</body>
</html>
