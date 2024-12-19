<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if id parameter exists
if(!isset($_GET["id"]) || empty($_GET["id"])) {
    $_SESSION['error'] = "ID client non spécifié.";
    header("location: clients.php");
    exit;
}

$id_client = $_GET["id"];

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST["nom"]);
    $email = trim($_POST["email"]);
    $telephone = trim($_POST["telephone"]);
    $adresse = trim($_POST["adresse"]);
    
    // Validate input
    $error = false;
    if(empty($nom) || empty($email)) {
        $_SESSION['error'] = "Le nom et l'email sont obligatoires.";
        $error = true;
    }
    
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format d'email invalide.";
        $error = true;
    }
    
    // Check if email already exists (excluding current client)
    $check_email = "SELECT id_client FROM clients WHERE email = ? AND id_client != ?";
    if($stmt = mysqli_prepare($conn, $check_email)) {
        mysqli_stmt_bind_param($stmt, "si", $email, $id_client);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if(mysqli_num_rows($result) > 0) {
            $_SESSION['error'] = "Cet email est déjà utilisé par un autre client.";
            $error = true;
        }
    }
    
    if(!$error) {
        $sql = "UPDATE clients SET nom = ?, email = ?, telephone = ?, adresse = ? WHERE id_client = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssi", $nom, $email, $telephone, $adresse, $id_client);
            
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "Les informations du client ont été mises à jour avec succès.";
                header("location: clients.php");
                exit();
            } else {
                $_SESSION['error'] = "Une erreur s'est produite lors de la mise à jour.";
            }
        }
    }
}

// Get client data
$sql = "SELECT * FROM clients WHERE id_client = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id_client);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) == 0) {
        $_SESSION['error'] = "Client non trouvé.";
        header("location: clients.php");
        exit;
    }
    
    $client = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Client - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Modifier Client</h1>
                </div>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($client['nom']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telephone" class="form-label">Téléphone</label>
                        <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($client['telephone']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse</label>
                        <textarea class="form-control" id="adresse" name="adresse" rows="3"><?php echo htmlspecialchars($client['adresse']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="clients.php" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
