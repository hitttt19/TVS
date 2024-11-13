<?php
session_start();

// Redirect to landing page if the user is not logged in
if (!isset($_SESSION['enforcer_id'])) {
    header("Location: ../landingpage.php");
    exit();
}

// Database connection
include('../db_connection.php');

// Get the count of today's offenses
$today = date('Y-m-d');  // Get today's date
$todayOffensesStmt = $pdo->prepare("SELECT COUNT(*) FROM offense_records WHERE DATE(datetime) = ? AND enforcer_id = ?");
$todayOffensesStmt->execute([$today, $_SESSION['enforcer_id']]);
$todayOffenses = $todayOffensesStmt->fetchColumn();

// Get the total number of offenses added by this enforcer
$totalOffensesStmt = $pdo->prepare("SELECT COUNT(*) FROM offense_records WHERE enforcer_id = ?");
$totalOffensesStmt->execute([$_SESSION['enforcer_id']]);
$totalOffenses = $totalOffensesStmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegulaDrive</title>
    <link rel="icon" href="../logo/RegLogo.png" id="favicon">
    <link rel="stylesheet" href="../Enforcercss/EnforcerIndex.css">
    <link rel="stylesheet" href="../css/toast.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.2/toastify.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.2/toastify.min.js"></script>
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <div class="sidebar-header">
                <img src="../logo/RegLogo.png" alt="Reguladrive Logo" class="logo">
                <h2 class="title">REGULADRIVE</h2>
            </div>
            <ul class="nav-list">
                <li class="nav-item active" onclick="window.location='enforcer_dashboard.php';">
                    <a href="#">
                        <img src="../icons/Dashboard.png" alt="Dashboard Icon" class="icon">
                        <span class="text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item" onclick="window.location='enforcer_offenseRecords.php';">
                    <a href="#">
                        <img src="../icons/Note.png" alt="Offense Records Icon" class="icon">
                        <span class="text">Offense Records</span>
                    </a>
                </li>
                <li class="nav-item" onclick="window.location='enforcer_reports.php';">
                    <a href="#">
                        <img src="../icons/Business Report.png" alt="Reports Icon" class="icon">
                        <span class="text">Reports</span>
                    </a>
                </li>
                <li class="nav-item" onclick="window.location='enforcer_offenseList.php';">
                    <a href="#">
                        <img src="../icons/Important Note.png" alt="Offense List Icon" class="icon">
                        <span class="text">Offense List</span>
                    </a>
                </li>
                <li class="nav-item" onclick="window.location='enforcer_notifications.php';">
                    <a href="#">
                        <img src="../icons/Commercial.png" alt="Notification Icon" class="icon">
                        <span class="text">Notification</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="main-content">
            <header class="header">
                <div class="header-left">
                    <div class="hamburger-menu" onclick="toggleSidebar()">&#9776;</div>
                    <h2>Bogo City Traffic Violations System</h2>
                </div>
                <div class="user-menu">
                    <img src="../icons/profile.png" class="user-icon">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Unknown'); ?></span>
                    <div class="user-dropdown">
                            <a href="enforcer_profile.php">
                                <span class="text">My Profile</span>
                            </a>
                        <a href="../logout.php">Logout</a>
                    </div>
                </div>
            </header>

            <section id="dashboard" class="content-section active">
                <div class="cards">
                    <div class="card violet">
                        <div class="card-content">
                            <div class="card-icon">📈</div>
                            <div class="card-text">Today's Offenses</div>
                            <p class="card-value"><?php echo htmlspecialchars($todayOffenses); ?></p>
                        </div>
                    </div>

                    <div class="card violet">
                        <div class="card-content">
                            <div class="card-icon">✅</div>
                            <div class="card-text">Total Offenses Added</div>
                            <p class="card-value"><?php echo htmlspecialchars($totalOffenses); ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Show Toast Notifications -->
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

        </div>
    </div>

    <script src="../js/script.js"></script>
</body>
</html>
