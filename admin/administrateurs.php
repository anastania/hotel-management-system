<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Récupérer tous les administrateurs
$sql = "SELECT * FROM admin ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Gestion des Administrateurs";
    include 'includes/head.php'; 
    ?>
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1>
                        <i class="fas fa-users-cog"></i>
                        Gestion des Administrateurs
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                            <i class="fas fa-plus"></i> Ajouter un Administrateur
                        </button>
                    </div>
                </div>

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Liste des administrateurs -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> Liste des Administrateurs
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-user"></i> Nom d'utilisateur</th>
                                        <th><i class="fas fa-envelope"></i> Email</th>
                                        <th><i class="fas fa-calendar-alt"></i> Date de création</th>
                                        <th><i class="fas fa-cogs"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($admin = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                        <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($admin['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" title="Modifier" 
                                                    onclick="editAdmin(<?php echo $admin['id_admin']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if($_SESSION['admin_id'] != $admin['id_admin']): ?>
                                            <button class="btn btn-danger btn-sm" title="Supprimer" 
                                                    onclick="deleteAdmin(<?php echo $admin['id_admin']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal Ajout Administrateur -->
                <div class="modal fade" id="addAdminModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-user-plus"></i> Ajouter un Administrateur
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="insert_admin.php" method="post">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Nom d'utilisateur</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Mot de passe</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
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

                <!-- Modal Modification Administrateur -->
                <div class="modal fade" id="editAdminModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-user-edit"></i> Modifier l'Administrateur
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="editAdminForm" action="update_admin.php" method="post">
                                <input type="hidden" name="admin_id" id="edit_admin_id">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="edit_username" class="form-label">Nom d'utilisateur</label>
                                        <input type="text" class="form-control" id="edit_username" name="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="edit_email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_password" class="form-label">Nouveau mot de passe (laisser vide si inchangé)</label>
                                        <input type="password" class="form-control" id="edit_password" name="password">
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
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function deleteAdmin(id) {
        if(confirm('Êtes-vous sûr de vouloir supprimer cet administrateur ?')) {
            window.location.href = 'delete_admin.php?id=' + id;
        }
    }

    function editAdmin(id) {
        fetch('get_admin.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_admin_id').value = data.id_admin;
                document.getElementById('edit_username').value = data.username;
                document.getElementById('edit_email').value = data.email;
                
                new bootstrap.Modal(document.getElementById('editAdminModal')).show();
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la récupération des données');
            });
    }
    </script>
</body>
</html>
