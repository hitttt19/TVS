<?php
session_start();
error_reporting(E_ALL); // Enable error reporting
ini_set('display_errors', 1);

include('db_connection.php'); // Ensure the correct path to your db connection file

// Initialize variables
$aboutUs = ''; 
$systemName = 'Bogo City Traffic Violations System'; // Default system name
$shortName = 'RegulaDrive'; // Default short name
$logoPath = 'logo/RegLogo.png'; // Default logo

try {
    // Fetch current settings
    $query = "SELECT * FROM Settings LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $currentSettings = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($currentSettings) {
        // Assign values from the Settings table or fallback to default values
        $systemName = $currentSettings['system_name'] ?: $systemName; // Default if empty
        $shortName = $currentSettings['system_short_name'] ?: $shortName; // Default if empty
        $aboutUs = $currentSettings['about_us'] ?: ''; // Default to empty if not set

        // Check if the logo path is set and if the file exists
        $logoPath = !empty($currentSettings['logo']) && file_exists($currentSettings['logo']) 
                    ? $currentSettings['logo'] 
                    : $logoPath; // Default to 'logo/RegLogo.png' if not set or invalid
    }

    // Debugging step: print the logo path
    echo "<!-- Debug: logo path is: " . htmlspecialchars($logoPath) . " -->"; 

    // Fetch announcements (optional, just to show functionality)
    $stmt = $pdo->query("SELECT title, content FROM announcements ORDER BY created_at DESC LIMIT 5");
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(!empty($shortName) ? $shortName : 'RegulaDrive'); ?></title>
    <link rel="icon" href="<?php echo !empty($logoPath) ? htmlspecialchars($logoPath) : 'logo/RegLogo.png'; ?>" id="favicon">
    <link rel="stylesheet" href="css/LandingP.css">
    <link rel="stylesheet" href="css/toast.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.2/toastify.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.2/toastify.min.js"></script>
</head>
<body>
<header>
<div class="logo">
        <!-- Make logo clickable and redirect to landing page -->
        <a href="landingpage.php">
            <!-- Ensure the logo path is correctly injected into the HTML -->
            <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="RD Logo">
            <span class="RDtxt"><?php echo htmlspecialchars($shortName); ?></span>
        </a>
    </div>
    <div class="hamburger" id="hamburger">
        <i class="fas fa-bars"></i>
    </div>
    <div id="sidebar" class="sidebar">
    <!-- Logo and Header Text -->
    <div class="sidebar-header">
    <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Logo" class="sidebar-logo">
        <h2 class="sidebar-title">REGULADRIVE</h2>
    </div>

    <!-- Navigation Links -->
    <ul>
        <li><a href="loginpage.php">Login</a></li>
        <li><a href="registerpage.php">Register</a></li>
        <li><a href="announcementpage.php">Announcements</a></li>
        <li><a href="about-uspage.php">About Us</a></li>
    </ul>
    <!-- Footer Section -->
<footer style="background-color: #0A3B31; color: #fff; text-align: center; padding: 20px 10px; margin-top: 12.4rem;">
    <div>
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($systemName); ?>. All Rights Reserved.</p>
        <p>
            <a href="about-uspage.php" style="color: #fff; text-decoration: underline;">About Us</a> | 
            <a href="privacy-policy.php" style="color: #fff; text-decoration: underline;">Privacy Policy</a> | 
            <a href="terms-of-service.php" style="color: #fff; text-decoration: underline;">Terms of Service</a>
        </p>
    </div>
</footer>

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

<!-- Toast notifications -->
<?php if (isset($_SESSION['message_success'])): ?>
<script>
    Toastify({
        text: "<?php echo addslashes(htmlspecialchars($_SESSION['message_success'])); ?>",
        duration: 2500,  
        close: true,
        gravity: "top",  
        position: 'right',  
        backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)", 
        stopOnFocus: true, 
        className: "toastify-enter toastify-glass",  
        onClick: function() {}  
    }).showToast();
</script>
<?php unset($_SESSION['message_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['message_error'])): ?>
<script>
    Toastify({
        text: "<?php echo addslashes(htmlspecialchars($_SESSION['message_error'])); ?>",
        duration: 2500, 
        close: true,
        gravity: "top",  
        position: 'right',  
        backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",  
        stopOnFocus: true,  
        className: "toastify-enter toastify-glass",  
        onClick: function() {}  
    }).showToast();
</script>
<?php unset($_SESSION['message_error']); ?>
<?php endif; ?>

<main style="background: url(image/traffic.jpg) no-repeat center center fixed; background-size: cover; height: calc(100vh - 70px); display: flex; justify-content: center; align-items: center; position: relative;">
    <div class="overlay">
        <h1 style="font-weight: normal;"><?php echo htmlspecialchars($systemName); ?></h1>
        <!-- Ensure the logo is correctly displayed -->
        <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="RD Logo">
        <h2 style="color:white"><?php echo htmlspecialchars($shortName); ?> Portal</h2>
        <div class="buttons">
            <a href="registerpage.php" class="btn red" id="registerBtn">Register now</a>
            <a href="loginpage.php" class="btn gr" id="loginBtn">Login</a>
        </div>
    </div>
</main>

<!-- Add your JavaScript at the end of the body -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password toggle functionality
        const passwordToggle = document.getElementById('passwordToggle');
        if (passwordToggle) {
            passwordToggle.addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    passwordToggle.classList.remove('fa-eye-slash');
                    passwordToggle.classList.add('fa-eye');
                } else {
                    passwordInput.type = 'password';
                    passwordToggle.classList.remove('fa-eye');
                    passwordToggle.classList.add('fa-eye-slash');
                }
            });
        }
    });

document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to buttons
    const buttons = document.querySelectorAll('.animated-button');
    buttons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default action
            const targetUrl = this.getAttribute('href'); // Get the target URL

            // Add the clicked class to trigger the animation
            this.classList.add('clicked');

            // Remove the clicked class after animation ends
            setTimeout(() => {
                this.classList.remove('clicked');
                window.location.href = targetUrl; // Redirect to the target URL
            }, 300); // Match the duration of the animation
        });
    });
});

</script>
<!-- Footer Section -->
<footer style="background-color: #0A3B31; color: #fff; text-align: center; padding: 20px 10px;">
    <div>
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($systemName); ?>. All Rights Reserved.</p>
        <p>
            <a href="about-uspage.php" style="color: #fff; text-decoration: underline;">About Us</a> | 
            <a href="privacy-policy.php" style="color: #fff; text-decoration: underline;">Privacy Policy</a> | 
            <a href="terms-of-service.php" style="color: #fff; text-decoration: underline;">Terms of Service</a>
        </p>
    </div>
</footer>

<script src="js/landingMobileSidebar.js"></script>
<script src="js/login.js"></script>
</body>
</html>
