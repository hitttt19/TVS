<?php
session_start();
if (!isset($_SESSION['driver_id'])) {
    header("Location: ../landingpage.php");
    exit();
}

// Database connection
include('../db_connection.php');

// Get the driver's license ID
$driver_id = $_SESSION['driver_id'];
$stmt = $pdo->prepare("SELECT license_id FROM drivers WHERE id = ?");
$stmt->execute([$driver_id]);
$driver = $stmt->fetch(PDO::FETCH_ASSOC);

if ($driver === false) {
    die('Driver not found or no data returned.');
}

$license_id = $driver['license_id'];

// Fetch offenses for the specific driver
$stmt = $pdo->prepare("SELECT * FROM offense_records WHERE license_id = ? ORDER BY datetime DESC");
$stmt->execute([$license_id]);
$driver_offenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total offenses, resolved offenses, pending offenses, and total fines
$total_offenses = count($driver_offenses);
$resolved_offenses = 0;
$pending_offenses = 0;
$total_fines_resolved = 0;
$total_fines_pending = 0;

foreach ($driver_offenses as $offense) {
    if ($offense['status'] === 'Resolved') {
        $resolved_offenses++;
        $total_fines_resolved += $offense['offense_rate']; // Assuming `offense_rate` is the fine amount
    } elseif ($offense['status'] === 'Pending') {
        $pending_offenses++;
        $total_fines_pending += $offense['offense_rate']; // Add fines for pending offenses
    }
}

// Set the total fines for display purposes to be the pending fines
$total_fines = $total_fines_pending;

// Fetch all offenses (if needed)
$stmt = $pdo->prepare("SELECT * FROM offenses ORDER BY date_created DESC");
$stmt->execute();
$all_offenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch contacts
$stmt = $pdo->prepare("SELECT * FROM contacts ORDER BY id DESC");
$stmt->execute();
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegulaDrive</title>
    <link rel="icon" href="../logo/RegLogo.png" id="favicon">
    <link rel="stylesheet" href="../Drivercss/DriverIndex.css">
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
                <li class="nav-item active" onclick="window.location='driver_dashboard.php';">
                    <a href="#">
                        <img src="../icons/Dashboard.png" alt="Dashboard Icon" class="icon">
                        <span class="text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item" onclick="window.location='driver_offenseRecords.php';">
                    <a href="#">
                        <img src="../icons/Note.png" alt="Offense Records Icon" class="icon">
                        <span class="text">Offense Records</span>
                    </a>
                </li>
                <li class="nav-item" onclick="window.location='driver_offenseList.php';">
                    <a href="#">
                        <img src="../icons/Important Note.png" alt="Offense List Icon" class="icon">
                        <span class="text">Offense List</span>
                    </a>
                </li>
                <li class="nav-item" onclick="window.location='driver_notifications.php';">
                    <a href="#">
                        <img src="../icons/Commercial.png" alt="Notification Icon" class="icon">
                        <span class="text">Notification</span>
                    </a>
                </li>
                <li class="nav-section" onclick="window.location='driver_helpDesk.php';">
                    <span class="text">Help Desk</span>
                    <div class="help-desk-dropdown">
                        <ul>
                            <?php foreach ($contacts as $contact): ?>
                                <li>
                                    <strong><?php echo htmlspecialchars($contact['service_provider']); ?></strong> - 
                                    <?php echo htmlspecialchars($contact['mobile_no']); ?> 
                                    <em>(<?php echo htmlspecialchars($contact['service_type']); ?>)</em>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
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
                    <span class="user-name"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Unknown'; ?></span>
                    <div class="user-dropdown">
                            <a href="driver_profile.php">
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
                            <div class="card-text">Total Offenses</div>
                            <p class="card-value"><?php echo htmlspecialchars($total_offenses); ?></p>
                        </div>
                    </div>

                    <div class="card green">
                        <div class="card-content">
                            <div class="card-icon">✅</div>
                            <div class="card-text">Resolved Offenses</div>
                            <p class="card-value"><?php echo htmlspecialchars($resolved_offenses); ?></p>
                        </div>
                    </div>
                    <div class="card orange">
                        <div class="card-content">
                            <div class="card-icon">🕒</div>
                            <div class="card-text">Pending Offenses</div>
                            <p class="card-value"><?php echo htmlspecialchars($pending_offenses); ?></p>
                        </div>
                    </div>
                    <div class="card blue">
                        <div class="card-content">
                            <div class="card-icon">💰</div>
                            <div class="card-text">Pending Fines</div> <!-- Updated text -->
                            <p class="card-value"><?php echo htmlspecialchars('₱' . number_format($total_fines, 2)); ?></p>
                        </div>
                    </div>
                </div>
            </section>

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

            <script src="../js/script.js"></script>
        </div>
    </div>
</body>
</html>
