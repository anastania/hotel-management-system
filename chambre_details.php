<?php
session_start();
require_once "includes/config.php";

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
$date_debut = $date_fin = "";
$date_debut_err = $date_fin_err = "";
$message = "";

// Récupérer les détails de la chambre
$sql = "SELECT c.*, h.nom_hotel, h.adresse 
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
        // Calculer le nombre de nuits et le prix total
        $date1 = new DateTime($date_debut);
        $date2 = new DateTime($date_fin);
        $interval = $date1->diff($date2);
        $nombre_nuits = $interval->days;
        $prix_total = $nombre_nuits * $chambre["prix"];
        
        // Vérifier si l'ID client existe dans la session
        if(!isset($_SESSION["id_client"])){
            $message = "Erreur: Session utilisateur invalide. Veuillez vous reconnecter.";
        } else {
            // Préparer la requête d'insertion
            $sql = "INSERT INTO reservations (id_chambre, id_client, date_arrivee, date_depart, prix, status) VALUES (?, ?, ?, ?, ?, 'en_attente')";
            
            if($stmt = mysqli_prepare($conn, $sql)){
                mysqli_stmt_bind_param($stmt, "iissd", $id_chambre, $_SESSION["id_client"], $date_debut, $date_fin, $prix_total);
                
                if(mysqli_stmt_execute($stmt)){
                    $message = "Réservation effectuée avec succès! Prix total: " . number_format($prix_total, 2) . " €";
                } else {
                    $message = "Une erreur est survenue. Veuillez réessayer plus tard.";
                }
                mysqli_stmt_close($stmt);
            }
        }
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
            height: 300px;
            object-fit: cover;
            width: 100%;
        }
        .price {
            font-size: 1.5rem;
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <img src="pictures/<?php echo strtolower($chambre['type_chambre']); ?>.jpg" 
                         class="card-img-top room-image" 
                         alt="<?php echo htmlspecialchars($chambre['type_chambre']); ?>"
                         onerror="this.src='pictures/default-room.jpg'">
                    <div class="card-body">
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
                        <h4 class="card-title">Réserver cette chambre</h4>
                        
                        <?php if(!empty($message)): ?>
                            <div class="alert alert-success"><?php echo $message; ?></div>
                        <?php endif; ?>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $id_chambre; ?>" method="post">
                            <div class="mb-3">
                                <label for="date_debut" class="form-label">Date d'arrivée</label>
                                <input type="text" class="form-control <?php echo (!empty($date_debut_err)) ? 'is-invalid' : ''; ?>" 
                                       id="date_debut" name="date_debut" value="<?php echo $date_debut; ?>">
                                <div class="invalid-feedback"><?php echo $date_debut_err; ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="date_fin" class="form-label">Date de départ</label>
                                <input type="text" class="form-control <?php echo (!empty($date_fin_err)) ? 'is-invalid' : ''; ?>" 
                                       id="date_fin" name="date_fin" value="<?php echo $date_fin; ?>">
                                <div class="invalid-feedback"><?php echo $date_fin_err; ?></div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
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
        allowInput: true
    });
    </script>
</body>
</html>
