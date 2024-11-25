<?php
session_start();
include('db_connection.php'); // Ensure you have the correct path to your db connection file

// Initialize default values
$shortName = 'RegulaDrive'; // Default short name
$logoPath = 'logo/RegLogo.png'; // Default logo

// Fetch system settings
$query = "SELECT system_short_name, logo FROM Settings LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$currentSettings = $stmt->fetch(PDO::FETCH_ASSOC);

if ($currentSettings) {
    $shortName = $currentSettings['system_short_name'] ?: $shortName; // Use the system short name from DB
    $logoPath = !empty($currentSettings['logo']) ? $currentSettings['logo'] : $logoPath; // Use logo from DB if available
}

// Fetch latest announcements
$stmt = $pdo->query("SELECT title, content FROM announcements ORDER BY created_at DESC");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($shortName); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="<?php echo !empty($logoPath) ? htmlspecialchars($logoPath) : 'logo/RegLogo.png'; ?>" id="favicon">
    <link rel="stylesheet" href="css/LandingP.css">
    <link rel="stylesheet" href="Landingheadercss/announcementpage.css">
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
                <li><a href="about-uspage.php"><i class="fas fa-info-circle"></i> About Us</a></li>
                <li><a href="landingpage.php"><i class="fas fa-home"></i> Home</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Announcements</h1>
        <?php if (empty($announcements)): ?>
            <p>No announcements available at the moment.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($announcements as $announcement): ?>
                    <li>
                        <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
    <script>
    // When the page has finished loading, trigger the scroll-down animation
    window.addEventListener('load', function () {
        document.body.classList.add('loaded');
    });
    </script>
    
</body>
</html>
