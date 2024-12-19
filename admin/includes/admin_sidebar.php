<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reservations.php' ? 'active' : ''; ?>" href="reservations.php">
                    <i class="fas fa-calendar-check"></i>
                    Réservations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'chambres.php' ? 'active' : ''; ?>" href="chambres.php">
                    <i class="fas fa-bed"></i>
                    Chambres
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'hotels.php' ? 'active' : ''; ?>" href="hotels.php">
                    <i class="fas fa-hotel"></i>
                    Hôtels
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : ''; ?>" href="clients.php">
                    <i class="fas fa-users"></i>
                    Clients
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="logout.php">
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
    top: 56px;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 48px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
    width: 250px;
    background-color: #f8f9fa;
}

.sidebar .nav-link {
    font-weight: 500;
    color: #333;
    padding: 0.5rem 1rem;
}

.sidebar .nav-link:hover {
    color: #007bff;
    background-color: #e9ecef;
}

.sidebar .nav-link i {
    margin-right: 0.5rem;
    width: 20px;
    text-align: center;
}

.content-wrapper {
    margin-left: 250px;
    padding: 20px;
}

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        position: relative;
        padding-top: 0;
    }
    .content-wrapper {
        margin-left: 0;
    }
}
</style>
