<?php
include('../db_connection.php');
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../landingpage.php");
    exit();
}

$error = '';
$success = false;
$systemName = '';
$shortName = '';
$aboutUs = '';

// Fetch current settings
$query = "SELECT * FROM Settings LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$currentSettings = $stmt->fetch(PDO::FETCH_ASSOC);

if ($currentSettings) {
    $systemName = $currentSettings['system_name'];
    $shortName = $currentSettings['system_short_name'];
    $aboutUs = $currentSettings['about_us'];
    $logoPath = $currentSettings['logo'];
} else {
    $logoPath = '../logo/default.png'; // Default logo if none exists
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $systemName = htmlspecialchars(trim($_POST['system-name']));
    $shortName = htmlspecialchars(trim($_POST['system-short-name']));
    $aboutUs = htmlspecialchars(trim($_POST['about-us']));

    // Handle file uploads for logo
    if (isset($_FILES['system-logo']) && $_FILES['system-logo']['error'] == 0) {
        // Get the file type and validate
        $fileType = mime_content_type($_FILES['system-logo']['tmp_name']);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        // Check if the file type is allowed
        if (in_array($fileType, $allowedTypes)) {
            // Generate a unique file name and set the upload path
            $logoPath = 'uploads/' . uniqid() . '-' . basename($_FILES['system-logo']['name']);
            
            // Attempt to move the uploaded file to the 'uploads' directory
            if (move_uploaded_file($_FILES['system-logo']['tmp_name'], $logoPath)) {
                // File uploaded successfully
            } else {
                $error = "Failed to move uploaded file.";
            }
        } else {
            $error = "Invalid file type. Please upload a JPEG, PNG, or GIF image.";
        }
    } else {
        // If no new logo was uploaded, keep the existing logo
        $logoPath = isset($currentSettings['logo']) ? $currentSettings['logo'] : 'uploads/default.png'; // You can specify a default logo if no new one is uploaded
    }

    // After handling the logo, update the database with the new settings
    if ($currentSettings) {
        // Update settings in the database
        $updateQuery = "UPDATE Settings SET system_name = ?, system_short_name = ?, about_us = ?, logo = ? WHERE id = 1";
        $stmt = $pdo->prepare($updateQuery);
        $success = $stmt->execute([$systemName, $shortName, $aboutUs, $logoPath]);

        if (!$success) {
            $error = "Failed to update settings. Please try again.";
        }
    } else {
        // Insert new settings into the database
        $insertQuery = "INSERT INTO Settings (system_name, system_short_name, about_us, logo) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($insertQuery);
        $success = $stmt->execute([$systemName, $shortName, $aboutUs, $logoPath]);

        if (!$success) {
            $error = "Failed to add settings. Please try again.";
        }
    }
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
    <link rel="stylesheet" href="../css/settings.css">
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
                <li class="nav-item" onclick="window.location='admin_dashboard.php';">
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
                <li class="nav-item active" onclick="window.location='settings.php';">
                    <a href="#"><img src="../icons/Settings.png" alt="Settings Icon" class="icon"><span class="text">Settings</span></a>
                </li>
                <li class="nav-item" onclick="window.location='../logout.php';">
                    <a href="#"><img src="../icons/logout.png" alt="Logout Icon" class="icon"><span class="text">Logout</span></a>
                </li>
            </ul>
        </nav>
        <nav class="mobile-sidebar">
    <div class="sidebar-header">
        <img src="../logo/RegLogo.png" alt="Reguladrive Logo" class="logo">
        <span class="logo-text">Reguladrive</span>
    </div>
    <div class="hamburger" id="hamburger">
        <i class="fas fa-bars"></i>
    </div>
    <ul class="nav-list">
        <li class="nav-item" onclick="window.location='admin_dashboard.php';">
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
                <li class="nav-item active" onclick="window.location='settings.php';">
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

            <section id="settings" class="content-section settings active">
                <h2>System Information</h2>
                <form class="settings-form" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="system-name">System Name</label>
                        <input type="text" id="system-name" name="system-name" placeholder="Enter system name" value="<?php echo htmlspecialchars($systemName); ?>">
                    </div>
                    <div class="form-group">
                        <label for="system-short-name">System Short Name</label>
                        <input type="text" id="system-short-name" name="system-short-name" placeholder="Enter short name" value="<?php echo htmlspecialchars($shortName); ?>">
                    </div>
                    <div class="form-group">
                        <label for="about-us">About Us</label>
                        <textarea id="about-us" name="about-us" rows="5" placeholder="Enter description"><?php echo htmlspecialchars($aboutUs); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="system-logo">System Logo</label>
                        <input type="file" id="system-logo" name="system-logo" accept="image/*">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="settings-update-btn">Update</button>
                    </div>
                </form>
            </section>  
        </div>
    </div>

    <!-- Toastify Script for Notifications -->
    <script>
        <?php if ($success): ?>
            Toastify({
                text: "Settings updated successfully!",
                duration: 3000,
                backgroundColor: "green",
                close: true,
                gravity: "top",
                position: "right"
            }).showToast();
        <?php elseif (!empty($error)): ?>
            Toastify({
                text: "<?php echo $error; ?>",
                duration: 3000,
                backgroundColor: "red",
                close: true,
                gravity: "top",
                position: "right"
            }).showToast();
        <?php endif; ?>
    </script>

    <script src="../js/script.js"></script>
</body>
</html>
