<?php
session_start();
require_once 'config.php';
require_once 'includes/db.php';
require_once 'src/PaymentService.php';

use App\PaymentService;

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Si on a des données de réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_chambre = $_POST['id_chambre'];
    $date_arrivee = $_POST['date_arrivee'];
    $date_depart = $_POST['date_depart'];
    $nombre_personnes = $_POST['nombre_personnes'];
    
    // Récupérer le prix de la chambre
    $stmt = $conn->prepare("SELECT prix_par_nuit FROM chambres WHERE id_chambre = ?");
    $stmt->execute([$id_chambre]);
    $chambre = $stmt->fetch();
    
    // Calculer le nombre de nuits
    $date1 = new DateTime($date_arrivee);
    $date2 = new DateTime($date_depart);
    $interval = $date1->diff($date2);
    $nombre_nuits = $interval->days;
    
    // Calculer le prix total
    $prix_total = $chambre['prix_par_nuit'] * $nombre_nuits;
    
    // Créer la réservation
    $stmt = $conn->prepare("INSERT INTO reservations (id_utilisateur, id_chambre, date_arrivee, date_depart, nombre_personnes, prix_total, status) 
                           VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$_SESSION['user_id'], $id_chambre, $date_arrivee, $date_depart, $nombre_personnes, $prix_total]);
    
    $_SESSION['last_reservation_id'] = $conn->lastInsertId();
    
    // Initialiser le service de paiement PayPal
    $paymentService = new PaymentService(
        PAYPAL_CLIENT_ID,
        PAYPAL_CLIENT_SECRET,
        PAYPAL_MODE,
        PAYPAL_CURRENCY
    );
    
    try {
        // Créer le paiement PayPal
        $payment = $paymentService->createPayment(
            $prix_total,
            "Réservation chambre d'hôtel #" . $_SESSION['last_reservation_id'],
            PAYPAL_RETURN_URL,
            PAYPAL_CANCEL_URL
        );
        
        // Enregistrer les détails du paiement
        $stmt = $conn->prepare("INSERT INTO payments (id_reservation, amount, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$_SESSION['last_reservation_id'], $prix_total]);
        
        // Rediriger vers PayPal
        foreach($payment->links as $link) {
            if($link->rel === 'approval_url') {
                header('Location: ' . $link->href);
                exit();
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Erreur lors de la création du paiement: " . $e->getMessage();
        header('Location: error.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - Hôtel</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <h2>Réservation de chambre</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="id_chambre">Chambre</label>
                <select class="form-control" id="id_chambre" name="id_chambre" required>
                    <?php
                    $stmt = $conn->query("SELECT id_chambre, numero, type, prix_par_nuit FROM chambres WHERE disponible = 1");
                    while ($chambre = $stmt->fetch()) {
                        echo "<option value='" . $chambre['id_chambre'] . "'>" . 
                             "Chambre " . $chambre['numero'] . " - " . $chambre['type'] . 
                             " (" . $chambre['prix_par_nuit'] . "€/nuit)</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="date_arrivee">Date d'arrivée</label>
                <input type="date" class="form-control" id="date_arrivee" name="date_arrivee" required>
            </div>

            <div class="form-group">
                <label for="date_depart">Date de départ</label>
                <input type="date" class="form-control" id="date_depart" name="date_depart" required>
            </div>

            <div class="form-group">
                <label for="nombre_personnes">Nombre de personnes</label>
                <input type="number" class="form-control" id="nombre_personnes" name="nombre_personnes" min="1" required>
            </div>

            <button type="submit" class="btn btn-primary">Réserver et payer avec PayPal</button>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Définir la date minimale à aujourd'hui
        var today = new Date().toISOString().split('T')[0];
        $('#date_arrivee').attr('min', today);
        $('#date_depart').attr('min', today);
        
        // Mettre à jour la date minimale de départ en fonction de la date d'arrivée
        $('#date_arrivee').change(function() {
            $('#date_depart').attr('min', $(this).val());
            if($('#date_depart').val() < $(this).val()) {
                $('#date_depart').val($(this).val());
            }
        });
    });
    </script>
</body>
</html>
