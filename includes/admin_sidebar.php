<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="../admin/index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Tableau de bord
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'hotels.php' ? 'active' : ''; ?>" href="../admin/hotels.php">
                    <i class="fas fa-building"></i>
                    Hôtels
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'chambres.php' ? 'active' : ''; ?>" href="../admin/chambres.php">
                    <i class="fas fa-bed"></i>
                    Chambres
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reservations.php' ? 'active' : ''; ?>" href="../admin/reservations.php">
                    <i class="fas fa-calendar-check"></i>
                    Réservations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : ''; ?>" href="../admin/clients.php">
                    <i class="fas fa-users"></i>
                    Clients
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'parametres.php' ? 'active' : ''; ?>" href="../admin/parametres.php">
                    <i class="fas fa-cogs"></i>
                    Paramètres
                </a>
            </li>
        </ul>

        <hr class="my-3">

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-danger" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 48px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
}

.sidebar .nav-link {
    font-weight: 500;
    color: #333;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    margin: 0.2rem 0.5rem;
}

.sidebar .nav-link:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.sidebar .nav-link.active {
    color: #2470dc;
    background-color: rgba(36, 112, 220, 0.1);
}

.sidebar .nav-link i {
    margin-right: 0.5rem;
    width: 1.25rem;
    text-align: center;
}

.sidebar hr {
    margin: 1rem 0.5rem;
}

.text-danger:hover {
    background-color: rgba(220, 53, 69, 0.1) !important;
}
</style>
