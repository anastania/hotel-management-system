<?php
require_once "../includes/config.php";

// Vérifier si la colonne existe déjà
$check_column = "SHOW COLUMNS FROM reservations LIKE 'prix_total'";
$result = mysqli_query($conn, $check_column);

if(mysqli_num_rows($result) == 0) {
    // La colonne n'existe pas, on l'ajoute
    $sql = "ALTER TABLE reservations ADD COLUMN prix_total DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER date_depart";
    
    if(mysqli_query($conn, $sql)) {
        echo "La colonne prix_total a été ajoutée avec succès.";
    } else {
        echo "Erreur lors de l'ajout de la colonne : " . mysqli_error($conn);
    }
} else {
    echo "La colonne prix_total existe déjà.";
}

mysqli_close($conn);
?>
