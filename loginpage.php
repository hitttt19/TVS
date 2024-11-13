<?php
session_start();
include('db_connection.php'); // Ensure the correct path to your db connection file

// Initialize variables
$systemName = 'Bogo City Traffic Violations System'; // Default system name
$shortName = 'RegulaDrive'; // Default short name
$logoPath = 'logo/RegLogo.png'; // Default logo

// Fetch current settings
$query = "SELECT * FROM Settings LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$currentSettings = $stmt->fetch(PDO::FETCH_ASSOC);

if ($currentSettings) {
    $systemName = $currentSettings['system_name'] ?: $systemName;
    $shortName = $currentSettings['system_short_name'] ?: $shortName;
    $logoPath = !empty($currentSettings['logo']) ? $currentSettings['logo'] : $logoPath;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($shortName); ?></title>
    <link rel="icon" href="logo/RegLogo.png">
    <link rel="stylesheet" href="loginpage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<header>
    <div class="logo">
        <a href="landingpage.php">
            <img src="logo/RegLogo.png" alt="Reguladrive Logo" class="logo">
            <span class="RDtxt"><?php echo htmlspecialchars($shortName); ?></span>
        </a>
    </div>
    <div class="hamburger" id="hamburger">
        <i class="fas fa-bars"></i>
    </div>
    <nav>
        <ul>
            <li class="announcement">
                <a href="announcementpage.php"><i class="fas fa-bullhorn"></i> Announcement</a>
            </li>
            <li class="aboutUs">
                <a href="about-uspage.php"><i class="fas fa-info-circle"></i> About Us</a>
            </li>
        </ul>
    </nav>
</header>

<main style="background: url(image/trf.jpg) no-repeat center center fixed; background-size: cover; height: calc(100vh - 70px); display: flex; justify-content: center; align-items: center; position: relative;">
    <div class="overlay">
        <div class="container">
            <div class="left-section">
                <img src="logo/RegLogo.png" alt="RegLogo" class="icon">
                <h2>Welcome to RegulaDrive</h2>
                <p>Bogo City Traffic Violation System!</p>
                <a href="registerpage.php" class="register-btn" id="registerBtn">Register</a>
            </div>

            <div class="login-container">
                <img src="logo/RegLogo.png" alt="RegLogo" class="icon">
                <h2 class="modal-title"><?php echo htmlspecialchars($shortName); ?></h2>
                <p class="modal-subtitle"><?php echo htmlspecialchars($systemName); ?></p>
                <form id="loginForm" action="login.php" method="post">
                    <div class="form-group">
                        <input type="text" placeholder="Username or Email" id="login" name="login" required>
                    </div>
                    <div class="form-group password-group">
                        <input type="password" placeholder="Password" id="password" name="password" required>
                        <i class="fas fa-eye-slash password-toggle" id="passwordToggle"></i>
                    </div>
                    <div class="form-group">
                        <button type="submit">Sign In</button>
                    </div>
                    <a href="#" class="forgot-password">Forgot Password</a>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        document.getElementById('passwordToggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            this.classList.toggle('fa-eye-slash');
            this.classList.toggle('fa-eye');
        });

        document.getElementById('hamburger').addEventListener('click', function() {
            document.querySelector('nav').classList.toggle('active');
        });
    });
</script>

<script src="js/landingMobileSidebar.js"></script>

</body>
</html>
