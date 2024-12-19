<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Vérifier si l'ID de la réservation est fourni
if (!isset($_GET['id'])) {
    header("location: reservations.php");
    exit;
}

$id_reservation = $_GET['id'];

// Traitement du formulaire de modification
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date_arrivee = $_POST['date_arrivee'];
    $date_depart = $_POST['date_depart'];
    $status = $_POST['status'];
    
    // Mettre à jour la réservation
    $sql = "UPDATE reservations SET date_arrivee = ?, date_depart = ?, status = ? WHERE id_reservation = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssi", $date_arrivee, $date_depart, $status, $id_reservation);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Réservation mise à jour avec succès.";
            header("location: reservations.php");
            exit;
        } else {
            $error = "Erreur lors de la mise à jour de la réservation.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Récupérer les détails de la réservation
$sql = "SELECT r.*, c.nom as client_nom, c.email as client_email, 
        ch.type_chambre, ch.prix, h.nom_hotel
        FROM reservations r
        JOIN clients c ON r.id_client = c.id_client
        JOIN chambres ch ON r.id_chambre = ch.id_chambre
        JOIN hotels h ON ch.id_hotel = h.id_hotel
        WHERE r.id_reservation = ?";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id_reservation);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $reservation = mysqli_fetch_assoc($result);
    } else {
        header("location: reservations.php");
        exit;
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Modifier la Réservation";
    include './includes/head.php'; 
    ?>
</head>
<body>
    <?php include './admin_header.php'; ?>

    <div class="container mt-4">
        <h2><i class="fas fa-edit"></i> Modifier la Réservation #<?php echo $id_reservation; ?></h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3>Détails de la réservation</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h4>Client</h4>
                        <p>
                            <strong>Nom:</strong> <?php echo htmlspecialchars($reservation['client_nom']); ?><br>
                            <strong>Email:</strong> <?php echo htmlspecialchars($reservation['client_email']); ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h4>Chambre</h4>
                        <p>
                            <strong>Type:</strong> <?php echo htmlspecialchars($reservation['type_chambre']); ?><br>
                            <strong>Hôtel:</strong> <?php echo htmlspecialchars($reservation['nom_hotel']); ?><br>
                            <strong>Prix:</strong> <?php echo number_format($reservation['prix'], 2); ?> € / nuit
                        </p>
                    </div>
                </div>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id_reservation); ?>" method="post">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="date_arrivee" class="form-label">Date d'arrivée</label>
                                <input type="date" class="form-control" id="date_arrivee" name="date_arrivee" 
                                       value="<?php echo $reservation['date_arrivee']; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="date_depart" class="form-label">Date de départ</label>
                                <input type="date" class="form-control" id="date_depart" name="date_depart" 
                                       value="<?php echo $reservation['date_depart']; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="status" class="form-label">Statut</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending" <?php echo $reservation['status'] == 'pending' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="confirmed" <?php echo $reservation['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmée</option>
                                    <option value="cancelled" <?php echo $reservation['status'] == 'cancelled' ? 'selected' : ''; ?>>Annulée</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                        <a href="reservations.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include './includes/footer.php'; ?>
</body>
</html>
