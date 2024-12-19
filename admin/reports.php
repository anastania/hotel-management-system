<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Get statistics
$stats = array();

// Total reservations
$sql = "SELECT COUNT(*) as total FROM reservations";
$result = $conn->query($sql);
$stats['total_reservations'] = $result->fetch_assoc()['total'];

// Total active reservations
$sql = "SELECT COUNT(*) as total FROM reservations WHERE status = 'confirmed'";
$result = $conn->query($sql);
$stats['active_reservations'] = $result->fetch_assoc()['total'];

// Total clients
$sql = "SELECT COUNT(*) as total FROM clients";
$result = $conn->query($sql);
$stats['total_clients'] = $result->fetch_assoc()['total'];

// Total rooms
$sql = "SELECT COUNT(*) as total FROM chambres";
$result = $conn->query($sql);
$stats['total_rooms'] = $result->fetch_assoc()['total'];

// Get monthly reservations for the current year
$sql = "SELECT MONTH(date_arrivee) as month, COUNT(*) as total 
        FROM reservations 
        WHERE YEAR(date_arrivee) = YEAR(CURRENT_DATE) 
        GROUP BY MONTH(date_arrivee)";
$monthly_stats = $conn->query($sql);

// Get hotel occupancy
$sql = "SELECT h.nom_hotel, 
        COUNT(CASE WHEN ch.disponibilite = 0 THEN 1 END) as occupied,
        COUNT(*) as total
        FROM hotels h
        JOIN chambres ch ON h.id_hotel = ch.id_hotel
        GROUP BY h.id_hotel";
$occupancy = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapports - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar {
            height: 100vh;
            position: fixed;
        }
        .main-content {
            margin-left: 220px;
        }
        .stat-card {
            border-radius: 15px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Rapports et Statistiques</h1>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Réservations Totales</h5>
                                <h2 class="card-text"><?php echo $stats['total_reservations']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Réservations Actives</h5>
                                <h2 class="card-text"><?php echo $stats['active_reservations']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Clients Total</h5>
                                <h2 class="card-text"><?php echo $stats['total_clients']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Chambres Totales</h5>
                                <h2 class="card-text"><?php echo $stats['total_rooms']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Réservations Mensuelles</h5>
                                <canvas id="monthlyChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Taux d'Occupation par Hôtel</h5>
                                <canvas id="occupancyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Monthly Reservations Chart
        const monthlyData = {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
            datasets: [{
                label: 'Réservations',
                data: Array(12).fill(0),
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        };

        <?php while($row = $monthly_stats->fetch_assoc()): ?>
            monthlyData.datasets[0].data[<?php echo $row['month']-1; ?>] = <?php echo $row['total']; ?>;
        <?php endwhile; ?>

        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: monthlyData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Hotel Occupancy Chart
        const occupancyData = {
            labels: [],
            datasets: [{
                label: 'Taux d\'occupation (%)',
                data: [],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        };

        <?php while($row = $occupancy->fetch_assoc()): ?>
            occupancyData.labels.push('<?php echo $row['nom_hotel']; ?>');
            occupancyData.datasets[0].data.push(<?php echo ($row['occupied']/$row['total'])*100; ?>);
        <?php endwhile; ?>

        new Chart(document.getElementById('occupancyChart'), {
            type: 'bar',
            data: occupancyData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    </script>
</body>
</html>
