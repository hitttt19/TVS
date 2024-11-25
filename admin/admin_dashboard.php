<?php
include('../db_connection.php');
session_start();
// Prevent the browser from caching the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: landingpage.php');
    exit;
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../landingpage.php");
    exit();
}

try {
    // Fetch counts for different entities
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM offense_records WHERE DATE(datetime) = CURDATE()");
    $stmt->execute();
    $todayOffenses = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM offense_records");
    $stmt->execute();
    $totalOffenses = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM drivers");
    $stmt->execute();
    $totalDrivers = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM traffic_enforcers");
    $stmt->execute();
    $totalEnforcers = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("A database error occurred. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegulaDrive</title>
    <link rel="icon" href="../logo/RegLogo.png">
    <link rel="stylesheet" href="../css/Index.css">
    <link rel="stylesheet" href="../css/toast.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.2/toastify.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.2/toastify.min.js"></script>
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <img src="../logo/RegLogo.png" alt="Reguladrive Logo" class="logo">
                <h2 class="title">REGULADRIVE</h2>
            </div>
            <ul class="nav-list">
                <li class="nav-item active" onclick="window.location='admin_dashboard.php';">
                    <a href="#"><img src="../icons/Dashboard.png" alt="Dashboard Icon" class="icon"><span class="text">Dashboard</span></a>
                </li>
                <li class="nav-item" onclick="window.location='offense_records.php';">
                    <a href="#"><img src="../icons/Important Note.png" alt="Offense Records Icon" class="icon"><span class="text">Offense Records</span></a>
                </li>
                <li class="nav-item" onclick="window.location='enforcers_list.php';">
                    <a href="#"><img src="../icons/enforcer.png" alt="Enforcer Icon" class="icon"><span class="text">Traffic Enforcers</span></a>
                </li>
                <li class="nav-item" onclick="window.location='drivers_list.php';">
                    <a href="#"><img src="../icons/Driver License.png" alt="Drivers List Icon" class="icon"><span class="text">Drivers List</span></a>
                </li>
                <li class="nav-item" onclick="window.location='reports.php';">
                    <a href="#"><img src="../icons/Business Report.png" alt="Reports Icon" class="icon"><span class="text">Reports</span></a>
                </li>
                <li class="nav-item" onclick="window.location='contact.php';">
                    <a href="#"><img src="../icons/Call.png" alt="Contact Icon" class="icon"><span class="text">Contact</span></a>
                </li>
                <li class="nav-item" onclick="window.location='announcement.php';">
                    <a href="#"><img src="../icons/Commercial.png" alt="Announcement Icon" class="icon"><span class="text">Announcement</span></a>
                </li>
                <li class="nav-section"><span class="text">Maintenance</span></li>
                <li class="nav-item" onclick="window.location='offenses_list.php';">
                    <a href="#"><img src="../icons/Important Note.png" alt="Offenses List Icon" class="icon"><span class="text">Offenses List</span></a>
                </li>
                <li class="nav-item" onclick="window.location='settings.php';">
                    <a href="#"><img src="../icons/Settings.png" alt="Settings Icon" class="icon"><span class="text">Settings</span></a>
                </li>
                <li class="nav-item" onclick="window.location='../logout.php';">
                    <a href="#"><img src="../icons/logout.png" alt="Logout Icon" class="icon"><span class="text">Logout</span></a>
                </li>
            </ul>
            
        </nav>
        <!-- Mobile Sidebar -->
<nav class="mobile-sidebar">
    <div class="sidebar-header">
        <img src="../logo/RegLogo.png" alt="Reguladrive Logo" class="logo">
        <span class="logo-text">Reguladrive</span>
    </div>
    <div class="hamburger" id="hamburger">
        <i class="fas fa-bars"></i>
    </div>
    <ul class="nav-list">
        <li class="nav-item active" onclick="window.location='admin_dashboard.php';">
            <a href="#"><img src="../icons/Dashboard.png" alt="Dashboard Icon" class="icon"><span class="text">Dashboard</span></a>
        </li>
        <li class="nav-item" onclick="window.location='offense_records.php';">
                    <a href="#"><img src="../icons/Important Note.png" alt="Offense Records Icon" class="icon"><span class="text">Offense Records</span></a>
                </li>
                <li class="nav-item" onclick="window.location='enforcers_list.php';">
                    <a href="#"><img src="../icons/enforcer.png" alt="Enforcer Icon" class="icon"><span class="text">Traffic Enforcers</span></a>
                </li>
                <li class="nav-item" onclick="window.location='drivers_list.php';">
                    <a href="#"><img src="../icons/Driver License.png" alt="Drivers List Icon" class="icon"><span class="text">Drivers List</span></a>
                </li>
                <li class="nav-item" onclick="window.location='reports.php';">
                    <a href="#"><img src="../icons/Business Report.png" alt="Reports Icon" class="icon"><span class="text">Reports</span></a>
                </li>
                <li class="nav-item" onclick="window.location='contact.php';">
                    <a href="#"><img src="../icons/Call.png" alt="Contact Icon" class="icon"><span class="text">Contact</span></a>
                </li>
                <li class="nav-item" onclick="window.location='announcement.php';">
                    <a href="#"><img src="../icons/Commercial.png" alt="Announcement Icon" class="icon"><span class="text">Announcement</span></a>
                </li>
                <li class="nav-section"><span class="text">Maintenance</span></li>
                <li class="nav-item" onclick="window.location='offenses_list.php';">
                    <a href="#"><img src="../icons/Important Note.png" alt="Offenses List Icon" class="icon"><span class="text">Offenses List</span></a>
                </li>
                <li class="nav-item" onclick="window.location='settings.php';">
                    <a href="#"><img src="../icons/Settings.png" alt="Settings Icon" class="icon"><span class="text">Settings</span></a>
                </li>
                <li class="nav-item" onclick="window.location='../logout.php';">
                    <a href="#"><img src="../icons/logout.png" alt="Logout Icon" class="icon"><span class="text">Logout</span></a>
                </li>
    </ul>
</nav>
        <!-- Overlay for mobile sidebar -->
        <div class="overlay" onclick="toggleSidebar()"></div>

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h2>Bogo City Traffic Violations System</h2>
                </div>
                <div class="user-menu">
                    <img src="../icons/profile.png" class="user-icon">
                    <span class="user-name">Admin</span>
                </div>
            </header>

            <!-- Dashboard Content -->
            <section id="dashboard" class="content-section active">
                <div class="cards">
                    <a href="offense_records.php" class="card">
                        <div class="card-icon-box"><img src="../icons/calendar.png" alt="Calendar Icon" class="card-icon"></div>
                        <div class="card-text">Today's Offenses</div>
                        <div class="card-value"><?php echo htmlspecialchars($todayOffenses); ?></div>
                    </a>
                    <a href="enforcers_list.php" class="card">
                        <div class="card-icon-box"><img src="../icons/enforcer.png" alt="Enforcer Icon" class="card-icon"></div>
                        <div class="card-text">Total Traffic Enforcers</div>
                        <div class="card-value"><?php echo htmlspecialchars($totalEnforcers); ?></div>
                    </a>
                    <a href="drivers_list.php" class="card">
                        <div class="card-icon-box"><img src="../icons/Driver License.png" alt="Driver Icon" class="card-icon"></div>
                        <div class="card-text">Total Drivers Listed</div>
                        <div class="card-value"><?php echo htmlspecialchars($totalDrivers); ?></div>
                    </a>
                    <a href="offense_records.php" class="card">
                        <div class="card-icon-box"><img src="../icons/TrafficOff.png" alt="Offense Icon" class="card-icon"></div>
                        <div class="card-text">Total Traffic Offenses</div>
                        <div class="card-value"><?php echo htmlspecialchars($totalOffenses); ?></div>
                    </a>
                </div>
            </section>
        </div>
    </div>

    <!-- Toast Notifications -->
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
            className: "toastify-enter toastify-glass"
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
            className: "toastify-enter toastify-glass"
        }).showToast();
    </script>
    <?php unset($_SESSION['message_error']); ?>
    <?php endif; ?>
    <script src="../js/script.js"></script>
</body>
</html>