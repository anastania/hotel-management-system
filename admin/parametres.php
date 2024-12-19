<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Traitement du formulaire de profil
if(isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $id_admin = $_SESSION['admin_id'];
    
    // Validation de base
    if(empty($username)) {
        $_SESSION['error'] = "Le nom d'utilisateur est requis.";
    } else {
        // Vérifier si le nom d'utilisateur existe déjà
        $sql = "SELECT id_admin FROM admin WHERE username = ? AND id_admin != ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $username, $id_admin);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if(mysqli_stmt_num_rows($stmt) > 0) {
            $_SESSION['error'] = "Ce nom d'utilisateur est déjà utilisé.";
        } else {
            // Mettre à jour le profil
            $sql = "UPDATE admin SET username = ?, email = ? WHERE id_admin = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $id_admin);
            
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "Profil mis à jour avec succès.";
                $_SESSION['admin_username'] = $username;
            } else {
                $_SESSION['error'] = "Une erreur s'est produite lors de la mise à jour du profil.";
            }
        }
    }
}

// Traitement du formulaire de mot de passe
if(isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $id_admin = $_SESSION['admin_id'];
    
    // Validation
    if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "Tous les champs sont requis.";
    } elseif($new_password !== $confirm_password) {
        $_SESSION['error'] = "Les nouveaux mots de passe ne correspondent pas.";
    } elseif(strlen($new_password) < 6) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        // Vérifier le mot de passe actuel
        $sql = "SELECT password FROM admin WHERE id_admin = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_admin);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if($row = mysqli_fetch_assoc($result)) {
            if(password_verify($current_password, $row['password'])) {
                // Mettre à jour le mot de passe
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE admin SET password = ? WHERE id_admin = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $hashed_password, $id_admin);
                
                if(mysqli_stmt_execute($stmt)) {
                    $_SESSION['success'] = "Mot de passe mis à jour avec succès.";
                } else {
                    $_SESSION['error'] = "Une erreur s'est produite lors de la mise à jour du mot de passe.";
                }
            } else {
                $_SESSION['error'] = "Le mot de passe actuel est incorrect.";
            }
        }
    }
}

// Récupérer les informations de l'admin
$sql = "SELECT username, email FROM admin WHERE id_admin = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['admin_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Paramètres";
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
                        <i class="fas fa-cog"></i> 
                        Paramètres
                    </h1>
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

                <div class="row">
                    <!-- Profil -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-user"></i> 
                                    Profil
                                </h5>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Nom d'utilisateur</label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($admin['email']); ?>">
                                    </div>
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="fas fa-save"></i> 
                                        Enregistrer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Mot de passe -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-key"></i> 
                                    Changer le mot de passe
                                </h5>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Mot de passe actuel</label>
                                        <input type="password" class="form-control" id="current_password" 
                                               name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                        <input type="password" class="form-control" id="new_password" 
                                               name="new_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                        <input type="password" class="form-control" id="confirm_password" 
                                               name="confirm_password" required>
                                    </div>
                                    <button type="submit" name="update_password" class="btn btn-primary">
                                        <i class="fas fa-save"></i> 
                                        Changer le mot de passe
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
