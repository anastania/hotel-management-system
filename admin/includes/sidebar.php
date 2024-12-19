<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Tableau de bord
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'hotels.php' ? 'active' : ''; ?>" href="hotels.php">
                    <i class="fas fa-hotel"></i>
                    Hôtels
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : ''; ?>" href="rooms.php">
                    <i class="fas fa-bed"></i>
                    Chambres
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : ''; ?>" href="clients.php">
                    <i class="fas fa-users"></i>
                    Clients
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reservations.php' ? 'active' : ''; ?>" href="reservations.php">
                    <i class="fas fa-calendar-alt"></i>
                    Réservations
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Administration</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                    <i class="fas fa-user-cog"></i>
                    Mon Profil
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </li>
        </ul>
    </div>
</nav>
