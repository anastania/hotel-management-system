<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Get all rooms with hotel information
$sql = "SELECT c.*, h.nom_hotel, 
        (SELECT COUNT(*) FROM reservations r WHERE r.id_chambre = c.id_chambre) as total_reservations 
        FROM chambres c 
        JOIN hotels h ON c.id_hotel = h.id_hotel 
        ORDER BY h.nom_hotel, c.type_chambre";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Chambres - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestion des Chambres</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                        <i class="fas fa-plus"></i> Ajouter une chambre
                    </button>
                </div>

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Hôtel</th>
                                <th>Type de Chambre</th>
                                <th>Prix</th>
                                <th>Disponibilité</th>
                                <th>Total Réservations</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($room = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($room['nom_hotel']); ?></td>
                                <td><?php echo htmlspecialchars($room['type_chambre']); ?></td>
                                <td><?php echo number_format($room['prix'], 2); ?> €</td>
                                <td>
                                    <span class="badge <?php echo $room['disponibilite'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $room['disponibilite'] ? 'Disponible' : 'Occupée'; ?>
                                    </span>
                                </td>
                                <td><?php echo $room['total_reservations']; ?></td>
                                <td>
                                    <a href="edit_room.php?id=<?php echo $room['id_chambre']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="delete_room.php?id=<?php echo $room['id_chambre']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette chambre ? Cette action est irréversible.')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une nouvelle chambre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="add_room.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="id_hotel" class="form-label">Hôtel</label>
                            <select class="form-select" id="id_hotel" name="id_hotel" required>
                                <?php
                                $hotels_sql = "SELECT id_hotel, nom_hotel FROM hotels ORDER BY nom_hotel";
                                $hotels_result = mysqli_query($conn, $hotels_sql);
                                while($hotel = mysqli_fetch_assoc($hotels_result)):
                                ?>
                                    <option value="<?php echo $hotel['id_hotel']; ?>">
                                        <?php echo htmlspecialchars($hotel['nom_hotel']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="type_chambre" class="form-label">Type de Chambre</label>
                            <select class="form-select" id="type_chambre" name="type_chambre" required>
                                <option value="Simple">Chambre Simple</option>
                                <option value="Double">Chambre Double</option>
                                <option value="Suite">Suite</option>
                                <option value="Suite Deluxe">Suite Deluxe</option>
                                <option value="Suite Présidentielle">Suite Présidentielle</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prix" class="form-label">Prix par nuit (€)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="prix" name="prix" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="disponibilite" name="disponibilite" checked>
                            <label class="form-check-label" for="disponibilite">Disponible</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Auto-dismiss alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
    </script>
</body>
</html>
