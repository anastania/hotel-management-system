<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" 
                   href="../admin/index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Tableau de bord
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'reservations.php' ? 'active' : ''; ?>" 
                   href="../admin/reservations.php">
                    <i class="fas fa-calendar-check"></i>
                    Réservations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'chambres.php' ? 'active' : ''; ?>" 
                   href="../admin/chambres.php">
                    <i class="fas fa-bed"></i>
                    Chambres
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'hotels.php' ? 'active' : ''; ?>" 
                   href="../admin/hotels.php">
                    <i class="fas fa-building"></i>
                    Hôtels
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : ''; ?>" 
                   href="../admin/clients.php">
                    <i class="fas fa-users"></i>
                    Clients
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white">
            <span>Rapports</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'statistiques.php' ? 'active' : ''; ?>" 
                   href="../admin/statistiques.php">
                    <i class="fas fa-chart-bar"></i>
                    Statistiques
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'revenus.php' ? 'active' : ''; ?>" 
                   href="../admin/revenus.php">
                    <i class="fas fa-euro-sign"></i>
                    Revenus
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white">
            <span>Configuration</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'parametres.php' ? 'active' : ''; ?>" 
                   href="../admin/parametres.php">
                    <i class="fas fa-cogs"></i>
                    Paramètres
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
    box-shadow: inset -1px 0 0 rgba(255, 255, 255, .1);
}

.sidebar-sticky {
    position: relative;
    top: 0;
    height: calc(100vh - 48px);
    padding-top: .5rem;
    overflow-x: hidden;
    overflow-y: auto;
}

.sidebar .nav-link {
    font-weight: 500;
    padding: 0.8rem 1rem;
    transition: all 0.3s ease;
}

.sidebar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #fff !important;
}

.sidebar .nav-link.active {
    background-color: rgba(255, 255, 255, 0.2);
    color: #fff !important;
}

.sidebar .nav-link i {
    margin-right: 0.75rem;
    width: 20px;
    text-align: center;
}

.sidebar-heading {
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}
</style>
