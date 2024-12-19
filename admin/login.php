<?php
session_start();
require_once "../includes/config.php";

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    
    $sql = "SELECT id_admin, username, password FROM admin WHERE email = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if(mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_array($result);
                if(password_verify($password, $row['password'])) {
                    $_SESSION["admin_id"] = $row['id_admin'];
                    $_SESSION["admin_username"] = $row['username'];
                    header("location: index.php");
                } else {
                    $error = "Le mot de passe que vous avez entré n'est pas valide.";
                }
            } else {
                $error = "Aucun compte administrateur trouvé avec cet email.";
            }
        } else {
            $error = "Une erreur est survenue. Veuillez réessayer plus tard.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Administration - Connexion";
    include '../includes/head.php'; 
    ?>
</head>
<body class="admin-login">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="text-center mb-4">
                    <a href="../index.php">
                        <i class="fas fa-hotel fa-3x text-primary"></i>
                    </a>
                    <h1 class="h3 mt-3">Administration</h1>
                    <p class="text-muted">Connectez-vous pour accéder au panneau d'administration</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="text-center mb-0">
                            <i class="fas fa-user-shield"></i> Connexion Admin
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-4">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email administrateur
                                </label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Mot de passe
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Connexion
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="../index.php" class="text-muted">
                        <i class="fas fa-arrow-left"></i> Retour au site
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .admin-login {
            background-color: var(--fresh-cream);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .admin-login .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .admin-login .card-header {
            background-color: var(--midnight);
            color: var(--cloud-white);
            border-radius: 8px 8px 0 0;
        }
        
        .admin-login .btn-primary {
            background-color: var(--midnight);
            border-color: var(--midnight);
        }
        
        .admin-login .btn-primary:hover {
            background-color: var(--storm-cloud);
            border-color: var(--storm-cloud);
        }
        
        .admin-login .text-primary {
            color: var(--midnight) !important;
        }
    </style>
</body>
</html>
