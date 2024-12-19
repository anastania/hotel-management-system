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
    // Vérifier si la chambre n'a pas de réservations
    $check_sql = "SELECT COUNT(*) as count FROM reservations WHERE id_chambre = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $_GET["delete"]);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $count = mysqli_fetch_assoc($check_result)['count'];

    if($count > 0) {
        $_SESSION['error'] = "Impossible de supprimer cette chambre car elle a des réservations associées.";
    } else {
        $sql = "DELETE FROM chambres WHERE id_chambre = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $_GET["delete"]);
            if(mysqli_stmt_execute($stmt)){
                $_SESSION['success'] = "La chambre a été supprimée avec succès.";
            } else {
                $_SESSION['error'] = "Une erreur s'est produite lors de la suppression.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    header("location: chambres.php");
    exit();
}

// Récupérer toutes les chambres avec les détails de l'hôtel
$sql = "SELECT c.*, h.nom_hotel, 
               (SELECT COUNT(*) FROM reservations r 
                WHERE r.id_chambre = c.id_chambre 
                AND r.date_arrivee <= CURDATE() 
                AND r.date_depart > CURDATE()) as est_occupee
        FROM chambres c 
        JOIN hotels h ON c.id_hotel = h.id_hotel
        ORDER BY h.nom_hotel, c.type_chambre";
$result = mysqli_query($conn, $sql);

// Récupérer la liste des hôtels pour le formulaire d'ajout
$hotels_sql = "SELECT id_hotel, nom_hotel FROM hotels ORDER BY nom_hotel";
$hotels_result = mysqli_query($conn, $hotels_sql);
$hotels = [];
while($hotel = mysqli_fetch_assoc($hotels_result)) {
    $hotels[] = $hotel;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Gestion des Chambres";
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
                        <i class="fas fa-bed"></i> 
                        Gestion des Chambres
                    </h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                        <i class="fas fa-plus"></i> Ajouter une chambre
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
                                        <th><i class="fas fa-hotel"></i> Hôtel</th>
                                        <th><i class="fas fa-bed"></i> Type</th>
                                        <th><i class="fas fa-layer-group"></i> Nombre de lits</th>
                                        <th><i class="fas fa-euro-sign"></i> Prix/nuit</th>
                                        <th><i class="fas fa-check-circle"></i> Disponibilité</th>
                                        <th><i class="fas fa-cogs"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($result) > 0): ?>
                                        <?php while($chambre = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?php echo $chambre['id_chambre']; ?></td>
                                                <td><?php echo htmlspecialchars($chambre['nom_hotel']); ?></td>
                                                <td><?php echo htmlspecialchars($chambre['type_chambre']); ?></td>
                                                <td><?php echo $chambre['nombre_lits']; ?></td>
                                                <td><?php echo number_format($chambre['prix'], 2); ?> €</td>
                                                <td>
                                                    <?php if($chambre['est_occupee'] == 0): ?>
                                                        <span class="badge bg-success">Disponible</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Occupée</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button onclick="editRoom(<?php echo $chambre['id_chambre']; ?>)" 
                                                            class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="javascript:void(0);" 
                                                       onclick="confirmDelete(<?php echo $chambre['id_chambre']; ?>)" 
                                                       class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Aucune chambre trouvée</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Ajout Chambre -->
    <div class="modal fade" id="addRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> 
                        Ajouter une chambre
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_chambre.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="hotel" class="form-label">Hôtel</label>
                            <select class="form-select" name="hotel" id="hotel" required>
                                <option value="">Sélectionner un hôtel</option>
                                <?php foreach($hotels as $hotel): ?>
                                    <option value="<?php echo $hotel['id_hotel']; ?>">
                                        <?php echo htmlspecialchars($hotel['nom_hotel']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Type de chambre</label>
                            <select class="form-select" name="type" id="type" required>
                                <option value="">Sélectionner un type</option>
                                <option value="simple">Simple</option>
                                <option value="double">Double</option>
                                <option value="suite">Suite</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="lits" class="form-label">Nombre de lits</label>
                            <input type="number" class="form-control" name="lits" id="lits" 
                                   min="1" max="4" required>
                        </div>
                        <div class="mb-3">
                            <label for="prix" class="form-label">Prix par nuit (€)</label>
                            <input type="number" class="form-control" name="prix" id="prix" 
                                   min="0" step="0.01" required>
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

    <!-- Modal Modification Chambre -->
    <div class="modal fade" id="editRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> 
                        Modifier la chambre
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="edit_chambre.php" method="POST">
                    <input type="hidden" name="id_chambre" id="edit_id_chambre">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_hotel" class="form-label">Hôtel</label>
                            <select class="form-select" name="hotel" id="edit_hotel" required>
                                <?php foreach($hotels as $hotel): ?>
                                    <option value="<?php echo $hotel['id_hotel']; ?>">
                                        <?php echo htmlspecialchars($hotel['nom_hotel']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_type" class="form-label">Type de chambre</label>
                            <select class="form-select" name="type" id="edit_type" required>
                                <option value="simple">Simple</option>
                                <option value="double">Double</option>
                                <option value="suite">Suite</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_lits" class="form-label">Nombre de lits</label>
                            <input type="number" class="form-control" name="lits" id="edit_lits" 
                                   min="1" max="4" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_prix" class="form-label">Prix par nuit (€)</label>
                            <input type="number" class="form-control" name="prix" id="edit_prix" 
                                   min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id) {
            if(confirm('Êtes-vous sûr de vouloir supprimer cette chambre ?')) {
                window.location.href = 'chambres.php?delete=' + id;
            }
        }

        function editRoom(id) {
            // Récupérer les données de la chambre via AJAX
            fetch('get_chambre.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id_chambre').value = data.id_chambre;
                    document.getElementById('edit_hotel').value = data.id_hotel;
                    document.getElementById('edit_type').value = data.type_chambre;
                    document.getElementById('edit_lits').value = data.nombre_lits;
                    document.getElementById('edit_prix').value = data.prix;
                    
                    // Afficher le modal
                    new bootstrap.Modal(document.getElementById('editRoomModal')).show();
                });
        }
    </script>
</body>
</html>
