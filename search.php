<?php
session_start();
require_once "includes/config.php";

$destination = isset($_GET['destination']) ? trim($_GET['destination']) : '';
$date_arrivee = isset($_GET['date_arrivee']) ? $_GET['date_arrivee'] : '';
$date_depart = isset($_GET['date_depart']) ? $_GET['date_depart'] : '';
$type_chambre = isset($_GET['type_chambre']) ? trim($_GET['type_chambre']) : '';

// Build the search query
$sql = "SELECT DISTINCT h.*, MIN(c.prix) as prix_min 
        FROM hotels h 
        LEFT JOIN chambres c ON h.id_hotel = c.id_hotel 
        WHERE 1=1";

$params = array();
$types = "";

if (!empty($destination)) {
    $sql .= " AND (h.nom_hotel LIKE ? OR h.adresse LIKE ?)";
    $search_term = "%$destination%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

if (!empty($type_chambre)) {
    $sql .= " AND EXISTS (SELECT 1 FROM chambres c2 WHERE c2.id_hotel = h.id_hotel AND c2.type_chambre = ?)";
    $params[] = $type_chambre;
    $types .= "s";
}

if (!empty($date_arrivee) && !empty($date_depart)) {
    $sql .= " AND EXISTS (
        SELECT 1 FROM chambres c3 
        WHERE c3.id_hotel = h.id_hotel 
        AND c3.id_chambre NOT IN (
            SELECT r.id_chambre 
            FROM reservations r 
            WHERE (r.date_arrivee BETWEEN ? AND ?)
            OR (r.date_depart BETWEEN ? AND ?)
            OR (? BETWEEN r.date_arrivee AND r.date_depart)
        )
    )";
    $params[] = $date_arrivee;
    $params[] = $date_depart;
    $params[] = $date_arrivee;
    $params[] = $date_depart;
    $params[] = $date_arrivee;
    $types .= "sssss";
}

$sql .= " GROUP BY h.id_hotel";

// Prepare and execute the query
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats de recherche - Hotel Reservation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <!-- Search Form -->
        <div class="search-form mb-4">
            <form action="search.php" method="get" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="destination" class="form-control" placeholder="Destination" value="<?php echo htmlspecialchars($destination); ?>">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_arrivee" class="form-control" value="<?php echo htmlspecialchars($date_arrivee); ?>">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_depart" class="form-control" value="<?php echo htmlspecialchars($date_depart); ?>">
                </div>
                <div class="col-md-3">
                    <select name="type_chambre" class="form-control">
                        <option value="">Type de chambre</option>
                        <option value="simple" <?php echo $type_chambre == 'simple' ? 'selected' : ''; ?>>Simple</option>
                        <option value="double" <?php echo $type_chambre == 'double' ? 'selected' : ''; ?>>Double</option>
                        <option value="suite" <?php echo $type_chambre == 'suite' ? 'selected' : ''; ?>>Suite</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                </div>
            </form>
        </div>

        <!-- Results -->
        <h2 class="mb-4">Résultats de recherche</h2>
        
        <?php if(mysqli_num_rows($result) > 0): ?>
            <div class="row">
                <?php while($hotel = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card hotel-card">
                            <img src="images/hotels/<?php echo $hotel['id_hotel']; ?>.jpg" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>"
                                 onerror="this.src='images/hotel-default.jpg'">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($hotel['nom_hotel']); ?></h5>
                                <p class="card-text"><?php echo substr(htmlspecialchars($hotel['description']), 0, 100); ?>...</p>
                                <p class="card-text">
                                    <small class="text-muted">À partir de <?php echo $hotel['prix_min']; ?> MAD/nuit</small>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="hotel_detail.php?id=<?php echo $hotel['id_hotel']; ?>" class="btn btn-primary">Voir détails</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Aucun hôtel ne correspond à vos critères de recherche.
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
