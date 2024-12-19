<?php
session_start();
require_once "includes/config.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Récupérer les informations du client
$id_client = $_SESSION["id_client"];
$sql = "SELECT * FROM clients WHERE id_client = ?";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id_client);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $client = mysqli_fetch_assoc($result);
        } else {
            // Rediriger si le client n'est pas trouvé
            header("location: logout.php");
            exit;
        }
    }
    mysqli_stmt_close($stmt);
}

// Traitement du formulaire de mise à jour
$update_success = $update_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST["nom"]);
    $email = trim($_POST["email"]);
    $adresse = trim($_POST["adresse"]);
    $telephone = trim($_POST["telephone"]);
    
    // Mettre à jour les informations
    $update_sql = "UPDATE clients SET nom = ?, email = ?, adresse = ?, telephone = ? WHERE id_client = ?";
    
    if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
        mysqli_stmt_bind_param($update_stmt, "ssssi", $nom, $email, $adresse, $telephone, $id_client);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $update_success = "Votre profil a été mis à jour avec succès.";
            // Mettre à jour les données affichées
            $client["nom"] = $nom;
            $client["email"] = $email;
            $client["adresse"] = $adresse;
            $client["telephone"] = $telephone;
            
            // Mettre à jour la session
            $_SESSION["nom"] = $nom;
        } else {
            $update_error = "Une erreur est survenue lors de la mise à jour.";
        }
        mysqli_stmt_close($update_stmt);
    }
}

// Process password change
if(isset($_POST["change_password"])) {
    $current_password = trim($_POST["current_password"]);
    $new_password = trim($_POST["new_password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    
    // Verify current password
    $sql = "SELECT password FROM clients WHERE id_client = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id_client);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        if(password_verify($current_password, $row["password"])) {
            if($new_password === $confirm_password) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE clients SET password = ? WHERE id_client = ?";
                if($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $id_client);
                    if(mysqli_stmt_execute($stmt)) {
                        $update_success = "Password changed successfully.";
                    } else {
                        $update_error = "Error changing password. Please try again.";
                    }
                }
            } else {
                $update_error = "New passwords do not match.";
            }
        } else {
            $update_error = "Current password is incorrect.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Mon Profil";
    include 'includes/head.php'; 
    ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">
                            <i class="fas fa-user-circle"></i> Mon Profil
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($update_success)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo $update_success; ?>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($update_error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $update_error; ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-4">
                                <label for="nom" class="form-label">
                                    <i class="fas fa-user"></i> Nom
                                </label>
                                <input type="text" class="form-control" id="nom" name="nom" 
                                       value="<?php echo htmlspecialchars($client["nom"]); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($client["email"]); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="adresse" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> Adresse
                                </label>
                                <textarea class="form-control" id="adresse" name="adresse" rows="3"
                                          ><?php echo htmlspecialchars($client["adresse"]); ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="telephone" class="form-label">
                                    <i class="fas fa-phone"></i> Téléphone
                                </label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" 
                                       value="<?php echo htmlspecialchars($client["telephone"]); ?>">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-4">
                                <label for="current_password" class="form-label">
                                    <i class="fas fa-lock"></i> Mot de passe actuel
                                </label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>

                            <div class="mb-4">
                                <label for="new_password" class="form-label">
                                    <i class="fas fa-lock"></i> Nouveau mot de passe
                                </label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock"></i> Confirmer le nouveau mot de passe
                                </label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="change_password" class="btn btn-secondary">
                                    <i class="fas fa-key"></i> Changer mon mot de passe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Statistiques</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get reservation stats
                        $sql = "SELECT 
                                COUNT(*) as total_reservations,
                                COUNT(CASE WHEN date_arrivee > CURDATE() THEN 1 END) as upcoming_reservations
                                FROM reservations 
                                WHERE id_client = ?";
                        if($stmt = mysqli_prepare($conn, $sql)) {
                            mysqli_stmt_bind_param($stmt, "i", $id_client);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            $stats = mysqli_fetch_assoc($result);
                            mysqli_stmt_close($stmt);
                        }
                        ?>
                        <p>Total des réservations: <?php echo $stats['total_reservations']; ?></p>
                        <p>Réservations à venir: <?php echo $stats['upcoming_reservations']; ?></p>
                        <a href="mes_reservations.php" class="btn btn-primary">Voir mes réservations</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
