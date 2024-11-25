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

// Get the driver's license ID and profile photo
$driver_id = $_SESSION['driver_id'];
$stmt = $pdo->prepare("SELECT license_id, photo FROM drivers WHERE id = ?");
$stmt->execute([$driver_id]);
$driver = $stmt->fetch(PDO::FETCH_ASSOC);

if ($driver === false) {
    die('Driver not found or no data returned.');
}

$license_id = $driver['license_id'];

// Default image if no profile photo exists
$profileImage = !empty($driver['photo']) ? htmlspecialchars($driver['photo']) : '../icons/profile.png';

// Fetch announcements for notifications
$stmt = $pdo->prepare("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch contacts for the help desk section
$stmt = $pdo->prepare("SELECT * FROM contacts");
$stmt->execute();
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch notifications for the logged-in driver, including driver's details
$stmt = $pdo->prepare("
    SELECT 
        n.message, 
        n.created_at, 
        o.ticket_no, 
        o.offense_name, 
        o.offense_rate, 
        CONCAT(d.firstname, ' ', IFNULL(d.middlename, ''), ' ', d.lastname) AS driver_name,  -- Concatenate first, middle, and last names
        d.present_address AS driver_address
    FROM 
        notifications n
    LEFT JOIN 
        offense_records o ON n.ticket_no = o.ticket_no
    LEFT JOIN 
        drivers d ON n.driver_id = d.id
    WHERE 
        n.driver_id = ? AND n.status = 'unread'
    ORDER BY 
        n.created_at DESC
");
$stmt->execute([$driver_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Optionally, you can also fetch the total number of unread notifications for the driver
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE driver_id = ? AND status = 'unread'");
$stmt->execute([$driver_id]);
$unread_count = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegulaDrive</title>
    <link rel="icon" href="../logo/RegLogo.png" id="favicon">
    <link rel="stylesheet" href="../Drivercss/DriverIndex.css">
    <link rel="stylesheet" href="../Drivercss/DNotif.css">
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
                <li class="nav-item active" onclick="window.location='driver_notifications.php';">
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
            
            <section id="notification" class="content-section notification active">
                <h2>Notifications</h2>
                <div class="notification-list">
                    <ul>
                        <?php if (count($notifications) > 0): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <li onclick="openLetterModal(<?php echo htmlspecialchars(json_encode($notification)); ?>)">
                                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <small><em><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($notification['created_at']))); ?></em></small>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>No new notifications.</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="notification-list">
                    <ul>
                        <?php foreach ($announcements as $announcement): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($announcement['title']); ?></strong> 
                                <p><?php echo htmlspecialchars($announcement['content']); ?></p>
                                <small><em><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($announcement['created_at']))); ?></em></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        </div>
    </div>

        <!-- Modal for Viewing Letter -->
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close-button" onclick="closeLetterModal()">&times;</span>
                <div class="letter-content">
                    <header>
                        <div class="header-left">
                            <img src="../logo/RegLogo.png" alt="City Logo" class="city-logo">
                        </div>
                        <div class="header-right">
                            <h2>Office of the City Mayor</h2>
                            <h3>Bogo Traffic Management Office</h3>
                            <p>Bogo City</p>
                        </div>
                    </header>
                    <hr>
                    <section class="letter-body">
                        <p class="date"></p>
                        <p><strong><span class="driver-address"></span></strong></p><br>
                        <p>Dear Mr./Mrs. <strong class="driver-name"></strong>,</p>
                        <p>This shall serve as a formal written demand for the immediate payment in full of a fine for:</p>
                        <table class="citation-details">
                            <tr>
                                <td>Traffic Citation Ticket No.:</td>
                                <td><strong class="ticket-no"></strong></td>
                            </tr>
                            <tr>
                                <td>For violation of:</td>
                                <td><strong class="offense-name"></strong></td>
                            </tr>
                            <tr>
                                <td>Date of Violation:</td>
                                <td><strong class="violation-date"></strong></td>
                            </tr>
                            <tr>
                                <td>Amount of Fine:</td>
                                <td><strong class="offense-rate"></strong></td>
                            </tr>
                        </table>
                        <p>As stated in the citation ticket, you were given seven (7) days from the date of the violation to settle the fine, but our records show that, to date, no payment has been received.</p>
                        <p><strong>Failure to pay the fine</strong> will result in the matter being referred to the City Prosecutor's Office for the filing of an appropriate case against you in a court of law.</p>
                    </section>
                    <section class="signature">
                        <p>Sincerely,</p>
                        <p><strong>ROGEL L. LAYSON</strong><br>Lieutenant PNP (Ret)<br>OIC - Bogo Traffic Management Office</p>
                    </section>
                </div>
            </div>
        </div>

        <script>
       // Function to open the letter modal and display the letter details
        function openLetterModal(notification) {
            // Set dynamic content in the modal
            document.querySelector(".driver-name").textContent = notification.driver_name;
            document.querySelector(".ticket-no").textContent = notification.ticket_no;
            document.querySelector(".offense-name").textContent = notification.offense_name;

            // Prepend peso sign to the offense rate
            document.querySelector(".offense-rate").textContent = '₱' + parseFloat(notification.offense_rate).toFixed(2);  // Format the rate with peso sign

            document.querySelector(".violation-date").textContent = notification.created_at;
            document.querySelector(".driver-address").textContent = notification.driver_address;

            // Format the "created_at" date to display only the date (no time)
            const createdAt = new Date(notification.created_at);
            const formattedDate = createdAt.toLocaleString('default', { 
                month: 'long', 
                day: 'numeric', 
                year: 'numeric'
            });
            document.querySelector(".date").textContent = formattedDate;

            // Display the modal
            document.querySelector("#myModal").style.display = "block";
        }

        // Function to close the modal when the close button is clicked
        function closeLetterModal() {
            // Hide the modal
            document.querySelector("#myModal").style.display = "none";
        }

    </script>

</body>
</html>
