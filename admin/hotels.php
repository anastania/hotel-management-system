<?php
session_start();
require_once "../includes/config.php";

// Check if user is logged in and is an admin
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

// Traitement de la suppression
if(isset($_GET["delete"]) && !empty($_GET["delete"])){
    $id = $_GET["delete"];
    
    // Vérifier s'il y a des chambres pour cet hôtel
    $check_sql = "SELECT COUNT(*) as count FROM chambres WHERE id_hotel = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $row = mysqli_fetch_assoc($check_result);
    
    if ($row['count'] > 0) {
        $_SESSION['error'] = "Impossible de supprimer cet hôtel car il a des chambres associées.";
    } else {
        $sql = "DELETE FROM hotels WHERE id_hotel = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $id);
            if(mysqli_stmt_execute($stmt)){
                $_SESSION['success'] = "L'hôtel a été supprimé avec succès.";
            } else {
                $_SESSION['error'] = "Une erreur s'est produite lors de la suppression.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    header("location: hotels.php");
    exit();
}

// Get all hotels with room counts
$sql = "SELECT h.*, COUNT(c.id_chambre) as nb_chambres 
        FROM hotels h 
        LEFT JOIN chambres c ON h.id_hotel = c.id_hotel 
        GROUP BY h.id_hotel 
        ORDER BY h.id_hotel";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Hôtels - Administration</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .hotel-image {
            max-width: 150px;
            height: auto;
            border-radius: 5px;
        }
        .actions {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1>
                        <i class="fas fa-hotel"></i> 
                        Gestion des Hôtels
                    </h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHotelModal">
                        <i class="fas fa-plus"></i> Ajouter un hôtel
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
                                        <th><i class="fas fa-hashtag"></i> ID</th>
                                        <th><i class="fas fa-image"></i> Image</th>
                                        <th><i class="fas fa-hotel"></i> Nom</th>
                                        <th><i class="fas fa-map-marker-alt"></i> Adresse</th>
                                        <th><i class="fas fa-phone"></i> Téléphone</th>
                                        <th><i class="fas fa-bed"></i> Chambres</th>
                                        <th><i class="fas fa-cogs"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($hotel = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string)$hotel['id_hotel']); ?></td>
                                        <td>
                                            <img src="<?php echo htmlspecialchars((string)($hotel['image_url'] ?? '../assets/img/hotel-placeholder.jpg')); ?>" 
                                                 alt="<?php echo htmlspecialchars((string)$hotel['nom_hotel']); ?>"
                                                 class="hotel-image">
                                        </td>
                                        <td><?php echo htmlspecialchars((string)$hotel['nom_hotel']); ?></td>
                                        <td><?php echo htmlspecialchars((string)$hotel['adresse']); ?></td>
                                        <td><?php echo htmlspecialchars((string)$hotel['telephone']); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $hotel['nb_chambres']; ?> chambres
                                            </span>
                                        </td>
                                        <td class="actions">
                                            <button onclick="editHotel(<?php echo $hotel['id_hotel']; ?>)" 
                                                    class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="#" onclick="confirmDelete(<?php echo $hotel['id_hotel']; ?>)"
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

    <!-- Modal d'ajout d'hôtel -->
    <div class="modal fade" id="addHotelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> 
                        Ajouter un nouvel hôtel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_hotel.php" method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nom" class="form-label">
                                <i class="fas fa-hotel"></i> Nom de l'hôtel
                            </label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label for="adresse" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Adresse
                            </label>
                            <input type="text" class="form-control" id="adresse" name="adresse" required>
                        </div>
                        <div class="mb-3">
                            <label for="telephone" class="form-label">
                                <i class="fas fa-phone"></i> Téléphone
                            </label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" required>
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

    <!-- Modal de modification d'hôtel -->
    <div class="modal fade" id="editHotelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> 
                        Modifier l'hôtel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="update_hotel.php" method="post">
                    <input type="hidden" name="id_hotel" id="edit_id_hotel">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_nom" class="form-label">
                                <i class="fas fa-hotel"></i> Nom de l'hôtel
                            </label>
                            <input type="text" class="form-control" id="edit_nom" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_adresse" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Adresse
                            </label>
                            <input type="text" class="form-control" id="edit_adresse" name="adresse" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_telephone" class="form-label">
                                <i class="fas fa-phone"></i> Téléphone
                            </label>
                            <input type="tel" class="form-control" id="edit_telephone" name="telephone" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery first, then Bootstrap Bundle with Popper -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id) {
            if(confirm('Êtes-vous sûr de vouloir supprimer cet hôtel ?')) {
                window.location.href = 'hotels.php?delete=' + id;
            }
        }

        function editHotel(id) {
            fetch('get_hotel.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id_hotel').value = data.id_hotel;
                    document.getElementById('edit_nom').value = data.nom_hotel;
                    document.getElementById('edit_adresse').value = data.adresse;
                    document.getElementById('edit_telephone').value = data.telephone;
                    
                    new bootstrap.Modal(document.getElementById('editHotelModal')).show();
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la récupération des données');
                });
        }
    </script>
</body>
</html>
