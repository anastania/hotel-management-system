<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hotel_reservation');

// Configuration PayPal
define('PAYPAL_CLIENT_ID', 'AYs_S3OeZStBVyPWxPaL6t0A5Zb_c5_xL_lZsp8WomREKuZHUTKY-PiUqGfrs-d-s7rPKHmW0xDcbvhd');
define('PAYPAL_CLIENT_SECRET', 'EHqac7i8xhcTZai2i442_IYeFru25OlpB4xdlAkAPo_XjPQuS83FlHhJC8iPFFuwizfW7Q-71QKHeWB-');
define('PAYPAL_MODE', 'sandbox'); // ou 'live' pour la production
define('PAYPAL_CURRENCY', 'EUR');

// URLs de l'application
define('BASE_URL', 'http://localhost/Gestion_reservation_hotel');
define('PAYPAL_RETURN_URL', BASE_URL . '/payment_success.php');
define('PAYPAL_CANCEL_URL', BASE_URL . '/payment_cancel.php');

// Configuration des emails
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'votre@gmail.com');
define('SMTP_PASS', 'votre_mot_de_passe');
define('SMTP_FROM', 'hotel@example.com');
define('SMTP_FROM_NAME', 'Système de Réservation d\'Hôtel');
?>
