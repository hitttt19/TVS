<?php
session_start();
include('db_connection.php'); // Ensure you have the correct path to your db connection file

// Fetch current settings
$query = "SELECT * FROM Settings LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$currentSettings = $stmt->fetch(PDO::FETCH_ASSOC);

$aboutUs = ''; 
$systemName = 'Bogo City Traffic Violations System'; // Default system name
$shortName = 'RegulaDrive'; // Default short name
$logoPath = 'logo/RegLogo.png'; // Default logo

if ($currentSettings) {
    $systemName = $currentSettings['system_name'] ?: $systemName; // Default if empty
    $shortName = $currentSettings['system_short_name'] ?: $shortName; // Default if empty
    $aboutUs = $currentSettings['about_us'] ?: ''; // Default to empty if not set
    $logoPath = !empty($currentSettings['logo']) ? $currentSettings['logo'] : $logoPath;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(!empty($shortName) ? $shortName : 'RegulaDrive'); ?></title>
    <link rel="icon" href="<?php echo !empty($logoPath) ? htmlspecialchars($logoPath) : 'logo/RegLogo.png'; ?>" id="favicon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/LandingP.css">
    <link rel="stylesheet" href="Landingheadercss/about-uspage.css">
</head>
<body>
    <header>
    <div class="logo">
        <!-- Make logo clickable and redirect to landing page -->
        <a href="landingpage.php">
          <img src="logo/RegLogo.png" alt="Reguladrive Logo" class="logo">
            <span class="RDtxt"><?php echo htmlspecialchars($shortName); ?></span>
        </a>
    </div>
        <nav>
            <ul>
                <li><a href="announcementpage.php"><i class="fas fa-bullhorn"></i> Announcement</a></li>
                <li><a href="landingpage.php"><i class="fas fa-home"></i> Home</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="about-us">
            <h1>About Us</h1>
            <p><?php echo nl2br(htmlspecialchars($aboutUs)); ?></p>
        </div>
    </main>
    

    <script>
    // When the page has finished loading, trigger the scroll-down animation
    window.addEventListener('load', function () {
        document.body.classList.add('loaded');
    });
</script>


</body>
</html>
