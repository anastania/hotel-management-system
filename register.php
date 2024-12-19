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
    <meta charset="UTF-8">
    <title>Register - Hotel Reservation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Inscription</h3>
                    </div>
                    <div class="card-body">
                        <?php 
                        if(!empty($register_err)){
                            echo '<div class="alert alert-danger">';
                            echo '<p>' . $register_err . '</p>';
                            echo '</div>';
                        }        
                        ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group mb-3">
                                <label>Nom</label>
                                <input type="text" name="nom" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label>Mot de passe</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label>Adresse</label>
                                <textarea name="adresse" class="form-control" required></textarea>
                            </div>
                            <div class="form-group mb-3">
                                <label>Téléphone</label>
                                <input type="tel" name="telephone" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <input type="submit" name="register" class="btn btn-primary w-100" value="S'inscrire">
                            </div>
                            <p>Déjà inscrit? <a href="login.php">Se connecter ici</a>.</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
