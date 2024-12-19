<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if id parameter exists
if(!isset($_GET["id"])) {
    header("location: hotels.php");
    exit;
}

$id = $_GET["id"];

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $nom_hotel = trim($_POST["nom_hotel"]);
    $adresse = trim($_POST["adresse"]);
    $description = trim($_POST["description"]);
    $email = trim($_POST["email"]);
    $telephone = trim($_POST["telephone"]);
    $site_web = trim($_POST["site_web"]);
    
    // Update hotel
    $sql = "UPDATE hotels SET nom_hotel = ?, adresse = ?, description = ?, email = ?, telephone = ?, site_web = ? WHERE id_hotel = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssssssi", $nom_hotel, $adresse, $description, $email, $telephone, $site_web, $id);
        
        if(mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "L'hôtel a été modifié avec succès.";
            header("location: hotels.php");
            exit();
        } else {
            $error = "Une erreur s'est produite lors de la modification de l'hôtel.";
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Get hotel data
$sql = "SELECT * FROM hotels WHERE id_hotel = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1) {
            $hotel = mysqli_fetch_assoc($result);
        } else {
            header("location: hotels.php");
            exit();
        }
    }
    
    mysqli_stmt_close($stmt);
} else {
    header("location: hotels.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier l'hôtel - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Modifier l'hôtel</h1>
                </div>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $id; ?>" method="post">
                            <div class="mb-3">
                                <label for="nom_hotel" class="form-label">Nom de l'hôtel</label>
                                <input type="text" class="form-control" id="nom_hotel" name="nom_hotel" value="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo htmlspecialchars($hotel['adresse']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($hotel['description']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($hotel['email']); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($hotel['telephone']); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="site_web" class="form-label">Site Web</label>
                                <input type="url" class="form-control" id="site_web" name="site_web" value="<?php echo htmlspecialchars($hotel['site_web']); ?>">
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="hotels.php" class="btn btn-secondary me-md-2">Annuler</a>
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
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
