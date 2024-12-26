<?php
session_start();
require_once "includes/config.php";

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if(isset($_GET['redirect'])) {
        header("location: " . $_GET['redirect']);
    } else {
        header("location: index.php");
    }
    exit;
}

if(isset($_POST["register"])) {
    $nom = trim($_POST["nom"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $adresse = trim($_POST["adresse"]);
    $telephone = trim($_POST["telephone"]);
    
    // Check if email exists
    $sql = "SELECT id_client FROM clients WHERE email = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        if(mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) > 0) {
                $register_err = "Cet email est déjà utilisé.";
            }
        }
        mysqli_stmt_close($stmt);
    }
    
    if(empty($register_err)) {
        $sql = "INSERT INTO clients (nom, email, password, adresse, telephone) VALUES (?, ?, ?, ?, ?)";
        if($stmt = mysqli_prepare($conn, $sql)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            mysqli_stmt_bind_param($stmt, "sssss", $nom, $email, $hashed_password, $adresse, $telephone);
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION["loggedin"] = true;
                $_SESSION["id_client"] = mysqli_insert_id($conn);
                $_SESSION["client_name"] = $nom;
                $_SESSION["client_email"] = $email;
                header("location: profile.php");
                exit();
            } else {
                $register_err = "Une erreur est survenue. Veuillez réessayer plus tard.";
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
    $page_title = "Inscription";
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
                            <i class="fas fa-user-plus"></i> Inscription
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($register_err)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $register_err; ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="nom" class="form-label">
                                    <i class="fas fa-user"></i> Nom
                                </label>
                                <input type="text" class="form-control" id="nom" name="nom" required>
                            </div>

                            <div class="mb-3">
                                <label for="adresse" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> Adresse
                                </label>
                                <textarea class="form-control" id="adresse" name="adresse" required></textarea>
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
                                <input type="tel" class="form-control" id="telephone" name="telephone" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Mot de passe
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" name="register">
                                    <i class="fas fa-user-plus"></i> S'inscrire
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <p>Déjà inscrit ? 
                                <a href="login.php">
                                    <i class="fas fa-sign-in-alt"></i> Connectez-vous
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
