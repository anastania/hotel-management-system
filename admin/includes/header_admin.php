<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-hotel"></i> Administration
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservations.php">
                        <i class="fas fa-calendar-check"></i> Réservations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="chambres.php">
                        <i class="fas fa-bed"></i> Chambres
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="clients.php">
                        <i class="fas fa-users"></i> Clients
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="administrateurs.php">
                        <i class="fas fa-users-cog"></i> Administrateurs
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> 
                        <?php echo htmlspecialchars($_SESSION["username"]); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
