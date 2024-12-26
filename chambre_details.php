<?php
session_start();
require_once "includes/config.php";
require_once "includes/hotel_images.php";

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Vérifier si l'ID de la chambre est fourni
if(!isset($_GET["id"]) || empty($_GET["id"])){
    header("location: chambres.php");
    exit;
}

$id_chambre = $_GET["id"];
$date_debut = isset($_GET["check_in"]) ? $_GET["check_in"] : "";
$date_fin = isset($_GET["check_out"]) ? $_GET["check_out"] : "";
$date_debut_err = $date_fin_err = "";
$message = "";

// Récupérer les détails de la chambre
$sql = "SELECT c.*, h.nom_hotel, h.adresse,
        COALESCE((SELECT hi.image_url 
                  FROM hotel_images hi 
                  WHERE hi.id_hotel = h.id_hotel 
                  ORDER BY hi.is_primary DESC, hi.id_image 
                  LIMIT 1), 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800') as image_url
        FROM chambres c 
        JOIN hotels h ON c.id_hotel = h.id_hotel 
        WHERE c.id_chambre = ?";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $id_chambre);
    
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1){
            $chambre = mysqli_fetch_array($result, MYSQLI_ASSOC);
        } else {
            header("location: error.php");
            exit();
        }
    } else {
        header("location: error.php");
        exit();
    }
    mysqli_stmt_close($stmt);
}

// Traitement du formulaire de réservation
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Valider la date de début
    if(empty(trim($_POST["date_debut"]))){
        $date_debut_err = "Veuillez entrer une date de début.";
    } else {
        $date_debut = trim($_POST["date_debut"]);
    }
    
    // Valider la date de fin
    if(empty(trim($_POST["date_fin"]))){
        $date_fin_err = "Veuillez entrer une date de fin.";
    } else {
        $date_fin = trim($_POST["date_fin"]);
    }
    
    // Vérifier les erreurs avant d'insérer dans la base de données
    if(empty($date_debut_err) && empty($date_fin_err)){
        // Vérifier si la chambre est disponible pour ces dates
        $check_sql = "SELECT id_reservation FROM reservations 
                     WHERE id_chambre = ? 
                     AND status IN ('confirmed', 'pending')
                     AND (
                         (date_arrivee <= ? AND date_depart >= ?) 
                         OR (date_arrivee <= ? AND date_depart >= ?)
                         OR (date_arrivee >= ? AND date_depart <= ?)
                     )";
        
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "issssss", 
            $id_chambre, $date_fin, $date_debut, $date_debut, $date_debut, $date_debut, $date_fin);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if(mysqli_num_rows($check_result) > 0) {
            $message = '<div class="alert alert-danger">Cette chambre n\'est pas disponible pour les dates sélectionnées.</div>';
        } else {
            // Calculer le nombre de nuits et le prix total
            $date1 = new DateTime($date_debut);
            $date2 = new DateTime($date_fin);
            $interval = $date1->diff($date2);
            $nombre_nuits = $interval->days;
            $prix_total = $nombre_nuits * $chambre["prix"];
            
            // Vérifier si l'ID client existe dans la session
            if(!isset($_SESSION["id_client"])){
                $message = '<div class="alert alert-danger">Erreur: Session utilisateur invalide. Veuillez vous reconnecter.</div>';
            } else {
                // Préparer la requête d'insertion
                $sql = "INSERT INTO reservations (id_chambre, id_client, date_arrivee, date_depart, prix, status) 
                        VALUES (?, ?, ?, ?, ?, 'pending')";
                
                if($stmt = mysqli_prepare($conn, $sql)){
                    mysqli_stmt_bind_param($stmt, "iissd", $id_chambre, $_SESSION["id_client"], $date_debut, $date_fin, $prix_total);
                    
                    if(mysqli_stmt_execute($stmt)){
                        $message = '<div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Réservation effectuée avec succès!<br>
                            <strong>Prix total:</strong> ' . number_format($prix_total, 2) . ' € pour ' . $nombre_nuits . ' nuit(s)
                        </div>';
                    } else {
                        $message = '<div class="alert alert-danger">Une erreur est survenue. Veuillez réessayer plus tard.</div>';
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
        mysqli_stmt_close($check_stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Réserver une chambre - <?php echo $chambre["type_chambre"]; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <style>
        .room-image {
            height: 400px;
            object-fit: cover;
            width: 100%;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }
        .room-image:hover {
            transform: scale(1.02);
        }
        .price {
            font-size: 1.5rem;
            color: var(--forest-green);
            font-weight: bold;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .room-details {
            padding: 20px;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            font-weight: 600;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <img src="<?php echo htmlspecialchars($chambre['image_url']); ?>" 
                         class="room-image" 
                         alt="<?php echo htmlspecialchars($chambre['type_chambre']); ?>"
                         onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800'">
                    <div class="room-details">
                        <h5 class="card-title"><?php echo htmlspecialchars($chambre["type_chambre"]); ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            <i class="fas fa-hotel"></i> <?php echo htmlspecialchars($chambre["nom_hotel"]); ?>
                        </h6>
                        <p class="card-text">
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($chambre["adresse"]); ?><br>
                            <i class="fas fa-bed"></i> <?php echo $chambre["nombre_lits"]; ?> lit(s)
                        </p>
                        <p class="price">
                            <i class="fas fa-tag"></i> <?php echo number_format($chambre["prix"], 2); ?> € / nuit
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="fas fa-calendar-alt"></i> Réserver cette chambre
                        </h4>
                        
                        <?php if(!empty($message)) echo $message; ?>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $id_chambre; ?>" method="post">
                            <div class="mb-3">
                                <label for="date_debut" class="form-label">Date d'arrivée</label>
                                <input type="text" class="form-control <?php echo (!empty($date_debut_err)) ? 'is-invalid' : ''; ?>" 
                                       id="date_debut" name="date_debut" value="<?php echo $date_debut; ?>" required>
                                <div class="invalid-feedback"><?php echo $date_debut_err; ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="date_fin" class="form-label">Date de départ</label>
                                <input type="text" class="form-control <?php echo (!empty($date_fin_err)) ? 'is-invalid' : ''; ?>" 
                                       id="date_fin" name="date_fin" value="<?php echo $date_fin; ?>" required>
                                <div class="invalid-feedback"><?php echo $date_fin_err; ?></div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calendar-check"></i> Confirmer la réservation
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
    flatpickr("#date_debut, #date_fin", {
        locale: "fr",
        dateFormat: "Y-m-d",
        minDate: "today",
        defaultDate: "today",
        allowInput: true
    });

    // Set initial values from URL parameters if they exist
    const urlParams = new URLSearchParams(window.location.search);
    const checkIn = urlParams.get('check_in');
    const checkOut = urlParams.get('check_out');
    
    if (checkIn) {
        document.getElementById('date_debut').value = checkIn;
    }
    if (checkOut) {
        document.getElementById('date_fin').value = checkOut;
    }
    </script>
</body>
</html>
