<?php
session_start();
require_once "includes/config.php";
require_once "includes/hotel_images.php";

// Validate input parameters
$destination = isset($_GET['destination']) ? trim($_GET['destination']) : '';
$check_in = isset($_GET['check_in']) ? trim($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? trim($_GET['check_out']) : '';

// Validate dates
$errors = [];

// Check if dates are valid
if (!empty($check_in) && !empty($check_out)) {
    $check_in_date = new DateTime($check_in);
    $check_out_date = new DateTime($check_out);
    $today = new DateTime();
    $today->setTime(0, 0, 0); // Reset time part to compare dates only

    // Validate check-in date
    if ($check_in_date < $today) {
        $errors[] = "La date d'arrivée ne peut pas être dans le passé.";
    }

    // Validate check-out date
    if ($check_out_date <= $check_in_date) {
        $errors[] = "La date de départ doit être au moins un jour après la date d'arrivée.";
    }
}

// If there are errors, redirect back with error message
if (!empty($errors)) {
    $_SESSION['search_error'] = implode('<br>', $errors);
    header("Location: index.php");
    exit;
}

// Current date for comparing with reservation end dates
$current_date = date('Y-m-d');

// Update reservation status for completed stays
$update_sql = "UPDATE reservations 
               SET status = 'completed' 
               WHERE date_depart < ? 
               AND status = 'confirmed'";
$update_stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($update_stmt, "s", $current_date);
mysqli_stmt_execute($update_stmt);

// Build the search query
$sql = "SELECT c.*, h.nom_hotel, h.adresse,
        COALESCE((SELECT hi.image_url 
                  FROM hotel_images hi 
                  WHERE hi.id_hotel = h.id_hotel 
                  ORDER BY hi.is_primary DESC, hi.id_image 
                  LIMIT 1), 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800') as image_url
        FROM chambres c 
        JOIN hotels h ON c.id_hotel = h.id_hotel 
        WHERE c.disponibilite = 1";

$params = array();
$types = "";

if (!empty($destination)) {
    $sql .= " AND (h.ville LIKE ? OR h.nom_hotel LIKE ?)";
    $search_term = "%$destination%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

if (!empty($check_in) && !empty($check_out)) {
    $sql .= " AND c.id_chambre NOT IN (
        SELECT r.id_chambre 
        FROM reservations r 
        WHERE r.status IN ('confirmed', 'pending')
        AND (
            (r.date_arrivee <= ? AND r.date_depart >= ?) 
            OR (r.date_arrivee <= ? AND r.date_depart >= ?)
            OR (r.date_arrivee >= ? AND r.date_depart <= ?)
        )
    )";
    $params[] = $check_out;
    $params[] = $check_in;
    $params[] = $check_in;
    $params[] = $check_in;
    $params[] = $check_in;
    $params[] = $check_out;
    $types .= "ssssss";
} else {
    // If no dates provided, exclude rooms that are currently occupied
    $sql .= " AND c.id_chambre NOT IN (
        SELECT r.id_chambre 
        FROM reservations r 
        WHERE r.status IN ('confirmed', 'pending')
        AND r.date_arrivee <= ? 
        AND r.date_depart >= ?
    )";
    $params[] = $current_date;
    $params[] = $current_date;
    $types .= "ss";
}

// Add ordering
$sql .= " ORDER BY h.nom_hotel, c.prix";

// Prepare and execute the query
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if any rooms were found
$rooms_found = mysqli_num_rows($result) > 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $page_title = "Résultats de recherche";
    include 'includes/head.php'; 
    ?>
    <style>
        .search-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .hotel-card {
            transition: transform 0.3s ease;
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .hotel-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .card-img-wrapper {
            position: relative;
            padding-top: 66.67%;
            overflow: hidden;
        }
        
        .card-img-wrapper img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .hotel-card:hover .card-img-wrapper img {
            transform: scale(1.1);
        }
        
        .room-status {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            z-index: 1;
        }
        
        .hotel-name {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        
        .room-type {
            color: #666;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        
        .price {
            font-size: 1.25rem;
            color: var(--forest-green);
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-search"></i> Résultats de recherche
        </h1>

        <div class="search-form">
            <form action="search.php" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="destination" class="form-label">Destination</label>
                    <input type="text" name="destination" id="destination" class="form-control" 
                           value="<?php echo htmlspecialchars($destination); ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="check_in" class="form-label">Date d'arrivée</label>
                    <input type="date" name="check_in" id="check_in" class="form-control" 
                           value="<?php echo htmlspecialchars($check_in); ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="check_out" class="form-label">Date de départ</label>
                    <input type="date" name="check_out" id="check_out" class="form-control" 
                           value="<?php echo htmlspecialchars($check_out); ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                </div>
            </form>
        </div>

        <?php if (!empty($destination) && !$rooms_found): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                Aucune chambre disponible à <?php echo htmlspecialchars($destination); ?> 
                <?php if (!empty($check_in) && !empty($check_out)): ?>
                    pour la période du <?php echo date('d/m/Y', strtotime($check_in)); ?> 
                    au <?php echo date('d/m/Y', strtotime($check_out)); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php 
            $counter = 0;
            while($chambre = mysqli_fetch_assoc($result)): 
                $delay = ($counter++ % 3) * 100;
            ?>
                <div class="col-md-4">
                    <div class="card hotel-card" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <div class="room-status">Disponible</div>
                        <div class="card-img-wrapper">
                            <img src="<?php echo htmlspecialchars($chambre['image_url']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($chambre['nom_hotel']); ?>"
                                 onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800'"
                                 loading="lazy">
                        </div>
                        <div class="card-body">
                            <h5 class="hotel-name" data-aos="fade-right" data-aos-delay="<?php echo $delay + 100; ?>">
                                <?php echo htmlspecialchars($chambre['nom_hotel']); ?>
                            </h5>
                            <h6 class="room-type text-muted" data-aos="fade-right" data-aos-delay="<?php echo $delay + 200; ?>">
                                <?php echo htmlspecialchars($chambre['type_chambre']); ?>
                            </h6>
                            <p class="card-text" data-aos="fade-right" data-aos-delay="<?php echo $delay + 300; ?>">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($chambre['adresse']); ?><br>
                                <i class="fas fa-bed"></i> 
                                <?php echo $chambre['nombre_lits']; ?> lit(s)
                            </p>
                            <p class="price mb-3" data-aos="fade-up" data-aos-delay="<?php echo $delay + 400; ?>">
                                <i class="fas fa-tag"></i> 
                                <?php echo number_format($chambre['prix'], 2); ?> € / nuit
                            </p>
                            <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                                <a href="chambre_details.php?id=<?php echo $chambre['id_chambre']; ?>&check_in=<?php 
                                    echo urlencode($check_in); ?>&check_out=<?php echo urlencode($check_out); ?>" 
                                   class="btn btn-primary w-100" data-aos="fade-up" data-aos-delay="<?php echo $delay + 500; ?>">
                                    <i class="fas fa-calendar-plus"></i> Réserver maintenant
                                </a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-secondary w-100">
                                    <i class="fas fa-sign-in-alt"></i> Connectez-vous pour réserver
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init();
        
        // Set minimum date for check-in and check-out
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('check_in').min = today;
        document.getElementById('check_out').min = today;
        
        // Update check-out minimum date when check-in is selected
        document.getElementById('check_in').addEventListener('change', function() {
            document.getElementById('check_out').min = this.value;
        });
    </script>
</body>
</html>
