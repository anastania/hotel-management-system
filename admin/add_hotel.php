<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_hotel = trim($_POST["nom_hotel"]);
    $adresse = trim($_POST["adresse"]);
    $description = trim($_POST["description"]);
    $email = trim($_POST["email"]);
    $telephone = trim($_POST["telephone"]);
    $site_web = trim($_POST["site_web"]);
    
    $sql = "INSERT INTO hotels (nom_hotel, adresse, description, email, telephone, site_web) VALUES (?, ?, ?, ?, ?, ?)";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssssss", $nom_hotel, $adresse, $description, $email, $telephone, $site_web);
        
        if(mysqli_stmt_execute($stmt)) {
            header("location: hotels.php");
            exit();
        } else {
            $error = "Une erreur s'est produite. Veuillez réessayer plus tard.";
        }
        
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Hôtel - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Ajouter un Hôtel</h1>
                </div>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group mb-3">
                                <label>Nom de l'hôtel</label>
                                <input type="text" name="nom_hotel" class="form-control" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label>Adresse</label>
                                <textarea name="adresse" class="form-control" required></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="4"></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            
                            <div class="form-group mb-3">
                                <label>Téléphone</label>
                                <input type="tel" name="telephone" class="form-control">
                            </div>
                            
                            <div class="form-group mb-3">
                                <label>Site web</label>
                                <input type="url" name="site_web" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Ajouter l'hôtel</button>
                                <a href="hotels.php" class="btn btn-secondary">Annuler</a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
