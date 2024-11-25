<?php
session_start();
if (!isset($_SESSION['driver_id'])) {
    header("Location: ../landingpage.php");
    exit();
}

// Database connection
include('../db_connection.php');
// Initialize variables
$aboutUs = ''; 
$systemName = 'Bogo City Traffic Violations System'; // Default system name
$shortName = 'RegulaDrive'; // Default short name
$logoPath = 'logo/RegLogo.png'; // Default logo

// Get the driver's ID from the session
$driver_id = $_SESSION['driver_id'];

// Fetch the driver's license_id and profile photo from the database
$stmt = $pdo->prepare("SELECT license_id, photo FROM drivers WHERE id = ?");
$stmt->execute([$driver_id]);
$driver = $stmt->fetch(PDO::FETCH_ASSOC);

if ($driver === false) {
    die('Driver not found or no data returned.');
}

$license_id = $driver['license_id'];

// Default image if no profile photo exists
$profileImage = !empty($driver['photo']) ? htmlspecialchars($driver['photo']) : '../icons/profile.png';

// Fetch offense records for the specific driver
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
    <link rel="stylesheet" href="../Drivercss/DOffenseR.css">
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
                <li class="nav-item" onclick="window.location='driver_dashboard.php';">
                    <a href="#">
                        <img src="../icons/Dashboard.png" alt="Dashboard Icon" class="icon">
                        <span class="text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item active" onclick="window.location='driver_offenseRecords.php';">
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
            <footer style="background-color: #004d40; color: #fff; text-align: center; padding: 20px 10px;">
    <div>
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($systemName); ?>. All Rights Reserved.</p>
    </div>
        </nav>
        <div class="main-content">
            <header class="header">
                <div class="header-left">
                    <h2>Bogo City Traffic Violations System</h2>
                </div>
                <div class="user-menu">
    <!-- Display the driver's profile photo -->
    <img src="<?php echo !empty($driver['photo']) ? (strpos($driver['photo'], '../') === 0 ? htmlspecialchars($driver['photo']) : '../' . htmlspecialchars($driver['photo'])) : '../icons/default.jpg'; ?>" alt="Driver's Profile Photo" class="user-icon">
    <span class="user-name">
        <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Unknown'; ?>
    </span>
    <div class="user-dropdown">
        <a href="driver_profile.php">
            <span class="text">My Profile</span>
        </a>
        <a href="../logout.php">Logout</a>
    </div>
</div>
            </header>

            <section id="offenseR" class="content-section offenseR active">
                <h2>List of Offense Records</h2>
                <div class="offenseR-controls">
                    <div class="search-container">
                        <label for="search-records"></label>
                        <input type="text" id="search-records" placeholder="Search Records">
                        <button class="search-btn">
                            <img src="../icons/search.png" alt="search Icon" class="icon">
                        </button>
                    </div>
                </div>
                <div class="offenseR-table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Datetime</th>
                                <th>Ticket No.</th>
                                <th>License ID</th>
                                <th>Offense</th>
                                <th>Penalty</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($driver_offenses)): ?>
                                <tr>
                                    <td colspan="7">No offense records found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($driver_offenses as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($record['datetime']))); ?></td>
                                        <td><?php echo htmlspecialchars($record['ticket_no']); ?></td>
                                        <td><?php echo htmlspecialchars($record['license_id']); ?></td>
                                        <td><?php echo htmlspecialchars($record['offense_name']); ?></td>
                                        <td><?php echo htmlspecialchars('₱' . number_format($record['offense_rate'], 2)); ?></td>
                                        <td>
                                            <span class="status <?php echo strtolower($record['status']); ?>">
                                                <?php echo htmlspecialchars($record['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const recordsInput = document.getElementById('search-records');
            const offensesInput = document.getElementById('search-offenses');
            
            recordsInput.addEventListener('keyup', function() {
                const filter = recordsInput.value.toLowerCase();
                const table = document.querySelector('#offenseR .table tbody');
                const rows = table.getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName('td');
                    let match = false;

                    for (let j = 0; j < cells.length; j++) {
                        if (cells[j]) {
                            if (cells[j].textContent.toLowerCase().includes(filter)) {
                                match = true;
                                break;
                            }
                        }
                    }
                    rows[i].style.display = match ? '' : 'none';
                }
            });

            offensesInput.addEventListener('keyup', function() {
                const filter = offensesInput.value.toLowerCase();
                const table = document.querySelector('#offenseL .offenseL-table tbody');
                const rows = table.getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName('td');
                    let match = false;

                    for (let j = 0; j < cells.length; j++) {
                        if (cells[j]) {
                            if (cells[j].textContent.toLowerCase().includes(filter)) {
                                match = true;
                                break;
                            }
                        }
                    }
                    rows[i].style.display = match ? '' : 'none';
                }
            });
        });
        </script>


    <script src="js/script.js"></script>
</body>
</html>