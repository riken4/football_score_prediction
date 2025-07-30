
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
    <div class="container">
        <a class="navbar-brand" href="user_dashboard.php">⚽ Score Predictor</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-item">
                        <a class="nav-link disabled text-white">
                             <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                    </li>
           
                    <li class="">
                        <a class="nav-link" href="index.php">Predict</a>
                    </li> 
                    <li class="nav-item">
                        <a class="nav-link" href="display.php">Match Detail</a>
                    </li> 
                    <li class="nav-item">
                        <a class="nav-link" href="calculation.php">Calculate</a>
                    </li> 

                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"> Logout</a>
                    </li>
               
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="index.php">🏠 Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">📝 Register</a>
                    </li> -->
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li> -->
                    
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
