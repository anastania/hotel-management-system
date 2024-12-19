<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Statistiques globales
$sql = "SELECT 
            (SELECT COUNT(*) FROM reservations) as total_reservations,
            (SELECT COUNT(*) FROM clients) as total_clients,
            (SELECT COUNT(*) FROM chambres) as total_chambres,
            (SELECT COUNT(*) FROM hotels) as total_hotels,
            (SELECT COUNT(*) 
             FROM reservations r 
             WHERE r.date_arrivee <= CURDATE() 
             AND r.date_depart > CURDATE()) as reservations_en_cours,
            (SELECT COUNT(*) 
             FROM reservations r 
             WHERE r.date_arrivee > CURDATE()) as reservations_futures,
            (SELECT COUNT(*) 
             FROM chambres c 
             WHERE NOT EXISTS (
                 SELECT 1 FROM reservations r 
                 WHERE r.id_chambre = c.id_chambre 
                 AND r.date_arrivee <= CURDATE() 
                 AND r.date_depart > CURDATE()
             )) as chambres_disponibles";

$result = mysqli_query($conn, $sql);
$stats = mysqli_fetch_assoc($result);

// Statistiques mensuelles
$sql_mensuel = "SELECT 
                    YEAR(date_arrivee) as annee,
                    MONTH(date_arrivee) as mois,
                    COUNT(*) as nombre_reservations,
                    SUM(prix) as revenu_total
                FROM reservations
                GROUP BY YEAR(date_arrivee), MONTH(date_arrivee)
                ORDER BY annee DESC, mois DESC
                LIMIT 12";

$result_mensuel = mysqli_query($conn, $sql_mensuel);
$stats_mensuels = [];
while($row = mysqli_fetch_assoc($result_mensuel)) {
    $stats_mensuels[] = $row;
}

// Top 5 des chambres les plus réservées
$sql_top_chambres = "SELECT 
                        c.id_chambre,
                        c.type_chambre,
                        h.nom_hotel,
                        COUNT(r.id_reservation) as nombre_reservations
                     FROM chambres c
                     JOIN hotels h ON c.id_hotel = h.id_hotel
                     LEFT JOIN reservations r ON c.id_chambre = r.id_chambre
                     GROUP BY c.id_chambre
                     ORDER BY nombre_reservations DESC
                     LIMIT 5";

$result_top_chambres = mysqli_query($conn, $sql_top_chambres);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Statistiques";
    include '../includes/head.php'; 
    ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1>
                        <i class="fas fa-chart-line"></i> 
                        Statistiques
                    </h1>
                </div>

                <!-- Cartes de statistiques -->
                <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-calendar-check"></i> 
                                    Réservations
                                </h5>
                                <p class="card-text">
                                    Total: <?php echo $stats['total_reservations']; ?><br>
                                    En cours: <?php echo $stats['reservations_en_cours']; ?><br>
                                    À venir: <?php echo $stats['reservations_futures']; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-bed"></i> 
                                    Chambres
                                </h5>
                                <p class="card-text">
                                    Total: <?php echo $stats['total_chambres']; ?><br>
                                    Disponibles: <?php echo $stats['chambres_disponibles']; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-users"></i> 
                                    Clients
                                </h5>
                                <p class="card-text">
                                    Total: <?php echo $stats['total_clients']; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graphique des réservations mensuelles -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Réservations mensuelles</h5>
                        <canvas id="reservationsChart"></canvas>
                    </div>
                </div>

                <!-- Top 5 des chambres -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top 5 des chambres les plus réservées</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Hôtel</th>
                                        <th>Type de chambre</th>
                                        <th>Nombre de réservations</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($chambre = mysqli_fetch_assoc($result_top_chambres)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($chambre['nom_hotel']); ?></td>
                                            <td><?php echo htmlspecialchars($chambre['type_chambre']); ?></td>
                                            <td><?php echo $chambre['nombre_reservations']; ?></td>
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
        // Données pour le graphique
        const statsData = <?php echo json_encode($stats_mensuels); ?>;
        const labels = statsData.map(d => {
            const date = new Date(d.annee, d.mois - 1);
            return date.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
        });
        const reservations = statsData.map(d => d.nombre_reservations);
        const revenus = statsData.map(d => d.revenu_total);

        // Création du graphique
        const ctx = document.getElementById('reservationsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Nombre de réservations',
                    data: reservations,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }, {
                    label: 'Revenus (€)',
                    data: revenus,
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Nombre de réservations'
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Revenus (€)'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
