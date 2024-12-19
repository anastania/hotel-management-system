<?php
require_once "config.php";

// Ajouter la colonne prix à la table reservations
$sql = "ALTER TABLE reservations ADD COLUMN prix DECIMAL(10,2) AFTER date_depart";

if(mysqli_query($conn, $sql)){
    echo "La colonne 'prix' a été ajoutée avec succès à la table reservations.";
} else {
    echo "Erreur lors de l'ajout de la colonne: " . mysqli_error($conn);
}
?>
