<?php
session_start();
require_once "includes/config.php";

if(!isset($_GET["id"])) {
    header("location: index.php");
    exit;
}

$id_hotel = $_GET["id"];

// Get hotel details
$sql = "SELECT * FROM hotels WHERE id_hotel = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id_hotel);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $hotel = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// Get available rooms
$sql = "SELECT * FROM chambres WHERE id_hotel = ? AND disponibilite = 1";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id_hotel);
    mysqli_stmt_execute($stmt);
    $rooms = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $hotel['nom_hotel']; ?> - Hotel Reservation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="row">
            <div class="col-md-8">
                <h1><?php echo $hotel['nom_hotel']; ?></h1>
                <p class="lead"><?php echo $hotel['adresse']; ?></p>
                
                <div class="mb-4">
                    <img src="images/hotels/<?php echo $hotel['id_hotel']; ?>.jpg" class="img-fluid rounded" alt="<?php echo $hotel['nom_hotel']; ?>">
                </div>
                
                <div class="mb-4">
                    <h3>Description</h3>
                    <p><?php echo $hotel['description']; ?></p>
                </div>
                
                <div class="mb-4">
                    <h3>Contact</h3>
                    <p>
                        Email: <?php echo $hotel['email']; ?><br>
                        Téléphone: <?php echo $hotel['telephone']; ?><br>
                        Site web: <a href="<?php echo $hotel['site_web']; ?>" target="_blank"><?php echo $hotel['site_web']; ?></a>
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Chambres disponibles</h3>
                    </div>
                    <div class="card-body">
                        <?php while($room = mysqli_fetch_assoc($rooms)): ?>
                        <div class="room-item mb-3 p-3 border rounded">
                            <h5><?php echo $room['type_chambre']; ?></h5>
                            <p>
                                Prix: <?php echo $room['prix']; ?> MAD/nuit<br>
                                Lits: <?php echo $room['nombre_lits']; ?>
                            </p>
                            <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                                <a href="reservation.php?id_chambre=<?php echo $room['id_chambre']; ?>" class="btn btn-primary">Réserver</a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-secondary">Connectez-vous pour réserver</a>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
