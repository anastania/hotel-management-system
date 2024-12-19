<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Traitement de la suppression
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Vérifier s'il y a des réservations pour ce client
    $check_sql = "SELECT COUNT(*) as count FROM reservations WHERE id_client = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $row = mysqli_fetch_assoc($check_result);
    
    if ($row['count'] > 0) {
        $_SESSION['error'] = "Impossible de supprimer ce client car il a des réservations associées.";
    } else {
        // Supprimer le client
        $sql = "DELETE FROM clients WHERE id_client = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "Client supprimé avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression du client.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    header("location: clients.php");
    exit;
}

// Get all clients with their reservation count
$sql = "SELECT c.*, COUNT(r.id_reservation) as total_reservations 
        FROM clients c 
        LEFT JOIN reservations r ON c.id_client = r.id_client 
        GROUP BY c.id_client 
        ORDER BY c.nom";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Gestion des Clients";
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
                        <i class="fas fa-users"></i> 
                        Gestion des Clients
                    </h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
                        <i class="fas fa-plus"></i> Ajouter un client
                    </button>
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
                                        <th><i class="fas fa-user"></i> Nom</th>
                                        <th><i class="fas fa-envelope"></i> Email</th>
                                        <th><i class="fas fa-phone"></i> Téléphone</th>
                                        <th><i class="fas fa-map-marker-alt"></i> Adresse</th>
                                        <th><i class="fas fa-calendar-check"></i> Total Réservations</th>
                                        <th><i class="fas fa-cogs"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($client = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($client['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($client['email']); ?></td>
                                        <td><?php echo htmlspecialchars($client['telephone']); ?></td>
                                        <td><?php echo htmlspecialchars($client['adresse']); ?></td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo $client['total_reservations']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit_client.php?id=<?php echo $client['id_client']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="post" style="display: inline-block;">
                                                <input type="hidden" name="id" value="<?php echo $client['id_client']; ?>">
                                                <button type="submit" name="delete" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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

    <!-- Modal d'ajout de client -->
    <div class="modal fade" id="addClientModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus"></i> 
                        Ajouter un nouveau client
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="add_client.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nom" class="form-label">
                                <i class="fas fa-user"></i> Nom
                            </label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telephone" class="form-label">
                                <i class="fas fa-phone"></i> Téléphone
                            </label>
                            <input type="tel" class="form-control" id="telephone" name="telephone">
                        </div>
                        
                        <div class="mb-3">
                            <label for="adresse" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Adresse
                            </label>
                            <textarea class="form-control" id="adresse" name="adresse" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="mot_de_passe" class="form-label">
                                <i class="fas fa-lock"></i> Mot de passe
                            </label>
                            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Ajouter
                        </button>
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
