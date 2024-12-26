<?php
session_start();
require_once "includes/config.php";
require_once "includes/hotel_images.php";

// Get search parameters
$search_city = isset($_GET['city']) ? trim($_GET['city']) : '';
$check_in = isset($_GET['checkin_date']) ? $_GET['checkin_date'] : '';
$check_out = isset($_GET['checkout_date']) ? $_GET['checkout_date'] : '';

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

// Base query to get available rooms
$sql = "SELECT c.*, h.nom_hotel, h.adresse,
        COALESCE((SELECT hi.image_url 
                  FROM hotel_images hi 
                  WHERE hi.id_hotel = h.id_hotel 
                  ORDER BY hi.is_primary DESC, hi.id_image 
                  LIMIT 1), 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800') as image_url
        FROM chambres c 
        JOIN hotels h ON c.id_hotel = h.id_hotel
        WHERE 1=1";

$params = array();
$types = "";

// Add city filter if provided
if (!empty($search_city)) {
    $sql .= " AND h.nom_hotel LIKE ?";
    $params[] = "%$search_city%";
    $types .= "s";
}

// Add date availability check if dates are provided
if (!empty($check_in) && !empty($check_out)) {
    // Check if the room is not already booked for the requested dates
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
    <?php include 'includes/head.php'; ?>
    <title>Nos Chambres - Hôtels de Luxe</title>
    <style>
        .hotel-card {
            transition: transform 0.3s ease;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .hotel-card:hover {
            transform: translateY(-5px);
        }
        .card-img-top {
            height: 250px;
            object-fit: cover;
            width: 100%;
        }
        .price {
            font-size: 1.25rem;
            color: #28a745;
            font-weight: bold;
        }
        .search-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .hotel-name {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .room-type {
            color: #666;
            font-size: 1rem;
        }
        .card-body {
            padding: 1.5rem;
        }
        .room-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            color: white;
            font-weight: bold;
            background-color: #28a745;
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-bed"></i> Nos Chambres
        </h1>

        <!-- Search Form -->
        <div class="search-form">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="city" class="form-label">Ville</label>
                    <select class="form-select" id="city" name="city">
                        <option value="">Toutes les villes</option>
                        <?php
                        // Get unique cities from hotel names
                        $cities_query = "SELECT DISTINCT nom_hotel FROM hotels ORDER BY nom_hotel";
                        $cities_result = mysqli_query($conn, $cities_query);
                        while ($row = mysqli_fetch_assoc($cities_result)) {
                            $hotel_name = $row['nom_hotel'];
                            // Extract city name from hotel name
                            $city = preg_replace('/^.*\s([\w-]+)$/', '$1', $hotel_name);
                            echo '<option value="' . htmlspecialchars($city) . '"' . 
                                 ($search_city == $city ? ' selected' : '') . '>' . 
                                 htmlspecialchars($city) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="checkin_date" class="form-label">Date d'arrivée</label>
                    <input type="date" class="form-control" id="checkin_date" name="checkin_date" 
                           value="<?php echo $check_in; ?>" min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-3">
                    <label for="checkout_date" class="form-label">Date de départ</label>
                    <input type="date" class="form-control" id="checkout_date" name="checkout_date" 
                           value="<?php echo $check_out; ?>" min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                </div>
            </form>
        </div>

        <?php if (!empty($search_city) && !$rooms_found): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                Aucune chambre disponible à <?php echo htmlspecialchars($search_city); ?> 
                <?php if (!empty($check_in) && !empty($check_out)): ?>
                    pour la période du <?php echo date('d/m/Y', strtotime($check_in)); ?> 
                    au <?php echo date('d/m/Y', strtotime($check_out)); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <?php $counter = 0; while($chambre = mysqli_fetch_assoc($result)): 
                // Extract city from hotel name
                $hotel_name = $chambre['nom_hotel'];
                $city = preg_replace('/^.*\s([\w-]+)$/', '$1', $hotel_name);
                $delay = ($counter++ % 3) * 100; // Add delay for staggered animation
            ?>
                <div class="col-md-4">
                    <div class="card hotel-card" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <div class="room-status">Disponible</div>
                        <?php
                        // Get room image from hotel_images or fallback
                        $room_image = !empty($chambre['image_url']) ? $chambre['image_url'] : 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800';
                        ?>
                        <div class="card-img-wrapper">
                            <img src="<?php echo htmlspecialchars($room_image); ?>" 
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
                                <a href="chambre_details.php?id=<?php echo $chambre['id_chambre']; ?><?php 
                                    echo (!empty($check_in) ? '&checkin_date=' . urlencode($check_in) : ''); 
                                    echo (!empty($check_out) ? '&checkout_date=' . urlencode($check_out) : ''); 
                                    ?>" 
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
        // Add date validation
        document.getElementById('checkin_date').addEventListener('change', function() {
            document.getElementById('checkout_date').min = this.value;
        });
    </script>
</body>
</html>
