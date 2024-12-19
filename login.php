<?php
session_start();
require_once "includes/config.php";

// Rediriger si déjà connecté
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: profile.php");
    exit;
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $user_type = isset($_POST["user_type"]) ? $_POST["user_type"] : "client";

    if ($user_type === "admin") {
        // Tentative de connexion admin
        $sql = "SELECT * FROM admin WHERE email = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if(mysqli_num_rows($result) == 1) {
                    $row = mysqli_fetch_array($result);
                    if(password_verify($password, $row['password'])) {
                        $_SESSION["admin_loggedin"] = true;
                        $_SESSION["admin_id"] = $row['id_admin'];
                        $_SESSION["admin_username"] = $row['username'];
                        $_SESSION["admin_email"] = $row['email'];
                        header("location: admin/index.php");
                        exit();
                    } else {
                        $error = "Mot de passe administrateur incorrect.";
                    }
                } else {
                    $error = "Aucun compte administrateur trouvé avec cet email.";
                }
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        // Tentative de connexion client
        $sql = "SELECT * FROM clients WHERE email = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if(mysqli_num_rows($result) == 1) {
                    $row = mysqli_fetch_array($result);
                    if(password_verify($password, $row['password'])) {
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id_client"] = $row['id_client'];
                        $_SESSION["client_name"] = $row['nom'];
                        $_SESSION["client_email"] = $row['email'];
                        header("location: profile.php");
                        exit();
                    } else {
                        $error = "Mot de passe incorrect.";
                    }
                } else {
                    $error = "Aucun compte trouvé avec cet email.";
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Connexion";
    include 'includes/head.php'; 
    ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>
                            <i class="fas fa-sign-in-alt"></i> Connexion
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Mot de passe
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label d-block">Type de compte</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="user_type" id="client" value="client" checked>
                                    <label class="form-check-label" for="client">
                                        <i class="fas fa-user"></i> Client
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="user_type" id="admin" value="admin">
                                    <label class="form-check-label" for="admin">
                                        <i class="fas fa-user-shield"></i> Administrateur
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Se connecter
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p>Pas encore de compte ? 
                                <a href="register.php">
                                    <i class="fas fa-user-plus"></i> Inscrivez-vous
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
