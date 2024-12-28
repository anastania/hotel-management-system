<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_reservation = isset($_POST['id_reservation']) ? $_POST['id_reservation'] : null;
    $total_amount = isset($_POST['total_amount']) ? $_POST['total_amount'] : null;

    if (!$id_reservation || !$total_amount) {
        $_SESSION['error'] = "Informations de paiement manquantes.";
        header('Location: mes_reservations.php');
        exit();
    }

    try {
        // Get reservation details
        $sql = "SELECT r.*, c.type_chambre, h.nom_hotel 
                FROM reservations r 
                JOIN chambres c ON r.id_chambre = c.id_chambre 
                JOIN hotels h ON c.id_hotel = h.id_hotel 
                WHERE r.id_reservation = ? AND r.id_client = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $id_reservation, $_SESSION['id_client']);
        mysqli_stmt_execute($stmt);
        $reservation = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$reservation) {
            throw new Exception("Réservation non trouvée.");
        }

        // Generate unique order ID
        $order_id = 'TEST-' . time() . '-' . $id_reservation;
        
        // Update reservation with order ID
        $update_sql = "UPDATE reservations SET payment_id = ? WHERE id_reservation = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "si", $order_id, $id_reservation);
        mysqli_stmt_execute($update_stmt);
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Paiement - <?php echo htmlspecialchars($reservation['nom_hotel']); ?></title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <!-- Font Awesome -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
            <style>
                :root {
                    --fresh-cream: #F4EFEB;
                    --forest-green: #5E6C5B;
                    --cloud-white: #FEFCF6;
                    --sky-blue: #D6E0E2;
                    --storm-cloud: #686867;
                    --midnight: #162A2C;
                }
                
                body { 
                    background-color: var(--fresh-cream);
                    font-family: 'Cormorant', serif;
                }
                
                .navbar {
                    background-color: #fff !important;
                }

                .navbar-brand, .nav-link {
                    color: var(--midnight) !important;
                }

                .payment-container {
                    max-width: 800px;
                    margin: 40px auto;
                    background-color: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
                }

                .hotel-info {
                    background-color: var(--forest-green);
                    color: white;
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 30px;
                }

                .reservation-details {
                    background-color: var(--cloud-white);
                    padding: 25px;
                    border-radius: 8px;
                    margin-bottom: 30px;
                    border: 1px solid var(--sky-blue);
                }

                .amount {
                    font-size: 32px;
                    font-weight: bold;
                    color: var(--forest-green);
                    margin: 15px 0;
                    text-align: center;
                }

                .card-input {
                    padding: 12px;
                    border: 1px solid var(--sky-blue);
                    border-radius: 6px;
                    margin-bottom: 15px;
                    width: 100%;
                    font-size: 16px;
                    background-color: var(--cloud-white);
                }

                .btn-primary {
                    background-color: var(--forest-green);
                    border-color: var(--forest-green);
                    padding: 12px 25px;
                    font-size: 18px;
                    font-weight: 500;
                }

                .btn-primary:hover {
                    background-color: var(--midnight);
                    border-color: var(--midnight);
                }

                .btn-outline-secondary {
                    color: var(--forest-green);
                    border-color: var(--forest-green);
                }

                .btn-outline-secondary:hover {
                    background-color: var(--forest-green);
                    color: white;
                }

                .payment-icon {
                    font-size: 24px;
                    margin-right: 10px;
                }

                .card-info {
                    background-color: var(--cloud-white);
                    border: 1px solid var(--sky-blue);
                    border-radius: 8px;
                    padding: 20px;
                    margin-bottom: 20px;
                }

                .section-title {
                    color: var(--midnight);
                    margin-bottom: 20px;
                    font-weight: 600;
                }

                .action-buttons {
                    display: flex;
                    gap: 10px;
                    margin-top: 20px;
                }

                .action-buttons .btn {
                    flex: 1;
                }
            </style>
        </head>
        <body>
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light sticky-top">
                <div class="container">
                    <a class="navbar-brand" href="index.php">
                        <i class="fas fa-hotel"></i> Hôtel de Luxe
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="mes_reservations.php">
                                    <i class="fas fa-calendar-check"></i> Mes Réservations
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container">
                <div class="payment-container">
                    <div class="hotel-info">
                        <h2 class="text-center mb-3">
                            <i class="fas fa-hotel"></i> 
                            <?php echo htmlspecialchars($reservation['nom_hotel']); ?>
                        </h2>
                        <p class="text-center mb-0">
                            <i class="fas fa-bed"></i> 
                            <?php echo htmlspecialchars($reservation['type_chambre']); ?>
                        </p>
                    </div>

                    <div class="reservation-details">
                        <h3 class="section-title text-center">Détails de la Réservation</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <p><i class="fas fa-calendar-check"></i> Check-in: <?php echo date('d/m/Y', strtotime($reservation['date_arrivee'])); ?></p>
                                <p><i class="fas fa-calendar-times"></i> Check-out: <?php echo date('d/m/Y', strtotime($reservation['date_depart'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><i class="fas fa-money-bill-wave"></i> Montant total:</p>
                                <div class="amount"><?php echo number_format($total_amount, 2, ',', ' '); ?> €</div>
                            </div>
                        </div>
                    </div>

                    <form action="payment_success_simple.php" method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <input type="hidden" name="amount" value="<?php echo $total_amount; ?>">
                        
                        <div class="card-info">
                            <h3 class="section-title">
                                <i class="fas fa-credit-card payment-icon"></i>Informations de Paiement
                            </h3>
                            
                            <div class="mb-3">
                                <label class="form-label">Numéro de carte (Test)</label>
                                <input type="text" class="card-input" value="4242 4242 4242 4242" readonly>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Date d'expiration</label>
                                    <input type="text" class="card-input" value="12/25" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">CVV</label>
                                    <input type="text" class="card-input" value="123" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-lock"></i> Confirmer le paiement
                            </button>
                            <a href="mes_reservations.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: mes_reservations.php');
        exit();
    }
} else {
    $_SESSION['error'] = "Méthode de requête invalide.";
    header('Location: mes_reservations.php');
    exit();
}
