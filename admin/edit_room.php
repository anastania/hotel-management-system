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
    header("location: rooms.php");
    exit;
}

$id = $_GET["id"];

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $type_chambre = trim($_POST["type_chambre"]);
    $prix = trim($_POST["prix"]);
    $disponibilite = isset($_POST["disponibilite"]) ? 1 : 0;
    $id_hotel = trim($_POST["id_hotel"]);
    
    // Update room
    $sql = "UPDATE chambres SET type_chambre = ?, prix = ?, disponibilite = ?, id_hotel = ? WHERE id_chambre = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sdiii", $type_chambre, $prix, $disponibilite, $id_hotel, $id);
        
        if(mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "La chambre a été modifiée avec succès.";
            header("location: rooms.php");
            exit();
        } else {
            $error = "Une erreur s'est produite lors de la modification de la chambre.";
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Get room data
$sql = "SELECT * FROM chambres WHERE id_chambre = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1) {
            $room = mysqli_fetch_assoc($result);
        } else {
            header("location: rooms.php");
            exit();
        }
    }
    
    mysqli_stmt_close($stmt);
}

// Get hotels for dropdown
$hotels_sql = "SELECT id_hotel, nom_hotel FROM hotels";
$hotels_result = mysqli_query($conn, $hotels_sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier la Chambre - Admin</title>
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
                    <h1 class="h2">Modifier la Chambre</h1>
                </div>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $id; ?>" method="post">
                            <div class="mb-3">
                                <label for="id_hotel" class="form-label">Hôtel</label>
                                <select class="form-select" id="id_hotel" name="id_hotel" required>
                                    <?php while($hotel = mysqli_fetch_assoc($hotels_result)): ?>
                                        <option value="<?php echo $hotel['id_hotel']; ?>" 
                                                <?php echo ($hotel['id_hotel'] == $room['id_hotel']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($hotel['nom_hotel']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="type_chambre" class="form-label">Type de Chambre</label>
                                <input type="text" class="form-control" id="type_chambre" name="type_chambre" 
                                       value="<?php echo htmlspecialchars($room['type_chambre']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="prix" class="form-label">Prix</label>
                                <input type="number" step="0.01" class="form-control" id="prix" name="prix" 
                                       value="<?php echo htmlspecialchars($room['prix']); ?>" required>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="disponibilite" name="disponibilite" 
                                       <?php echo $room['disponibilite'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="disponibilite">Disponible</label>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="rooms.php" class="btn btn-secondary me-md-2">Annuler</a>
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
