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
                        // Set admin session variables
                        session_regenerate_id();
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
                        // Set client session variables
                        session_regenerate_id();
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
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                        url('https://images.unsplash.com/photo-1571896349842-33c89424de2d?q=80&w=1920&auto=format&blur=50');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .card {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
        }

        .btn-primary {
            padding: 12px;
            border-radius: 8px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .alert {
            border-radius: 8px;
        }

        .text-center a {
            color: #0d6efd;
            text-decoration: none;
            transition: color 0.3s;
        }

        .text-center a:hover {
            color: #0a58ca;
            text-decoration: underline;
        }
    </style>
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
