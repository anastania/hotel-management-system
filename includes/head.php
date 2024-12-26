<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? $page_title . " - Hôtel Réservation" : "Hôtel Réservation"; ?></title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<!-- Google Fonts - Roboto -->
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

<!-- Custom Fonts - Blackberry Jam -->
<link href="https://fonts.cdnfonts.com/css/blackberry-jam" rel="stylesheet">

<!-- Custom CSS -->
<link href="<?php echo isset($isAdmin) ? '../assets/css/style.css' : 'assets/css/style.css'; ?>" rel="stylesheet">
<link href="<?php echo isset($isAdmin) ? '../assets/css/fonts.css' : 'assets/css/fonts.css'; ?>" rel="stylesheet">

<style>
    /* Image styling */
    .room-image {
        height: 250px;
        object-fit: cover;
        width: 100%;
    }
    
    @media (max-width: 768px) {
        .room-image {
            height: 200px;
        }
    }
    
    /* Card styling */
    .card {
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
    }
</style>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
