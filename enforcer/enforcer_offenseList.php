<?php
session_start();
if (!isset($_SESSION['enforcer_id'])) {
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

// Include the code to fetch the enforcer's profile photo
$enforcerId = $_SESSION['enforcer_id']; // Get the enforcer's ID from session
$query = "SELECT photo FROM traffic_enforcers WHERE id = :id"; // Query to get the photo from the database
$stmt = $pdo->prepare($query);
$stmt->execute(['id' => $enforcerId]); // Execute the query with the enforcer's ID
$enforcerDetails = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the enforcer's details

// Default image if no profile photo exists
$profileImage = !empty($enforcerDetails['photo']) ? htmlspecialchars($enforcerDetails['photo']) : '../icons/profile.png'; // Check if photo exists


// Get the enforcer's ID
$enforcer_id = $_SESSION['enforcer_id'];
$stmt = $pdo->prepare("SELECT badge_id FROM traffic_enforcers WHERE id = ?");
$stmt->execute([$enforcer_id]);
$enforcer = $stmt->fetch(PDO::FETCH_ASSOC);

if ($enforcer === false) {
    die('Enforcer not found or no data returned.');
}

$badge_id = $enforcer['badge_id'];

// Fetch enforcer's full details
$stmt = $pdo->prepare("SELECT * FROM traffic_enforcers WHERE badge_id = ?");
$stmt->execute([$badge_id]);
$enforcer_details = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all offenses
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
    <link rel="stylesheet" href="../Enforcercss/EnforcerIndex.css">
    <link rel="stylesheet" href="../Enforcercss/EOffenseL.css">
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
                <li class="nav-item" onclick="window.location='enforcer_dashboard.php';">
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
                <li class="nav-item active" onclick="window.location='enforcer_offenseList.php';">
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
                    <!-- Display the enforcer's profile photo -->
                    <img src="<?php echo htmlspecialchars($profileImage); ?>" class="user-icon" alt="Profile Picture">
                        <span class="user-name"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Unknown'; ?></span>
                    <div class="user-dropdown">
                        <a href="enforcer_profile.php">
                            <span class="text">My Profile</span>
                        </a>
                        <a href="../logout.php">Logout</a>
                    </div>
                </div>
            </header>

            <section id="offenseL" class="content-section offenseL active">
                <h2>List of Offenses</h2>
                <div class="offenseL-controls">
                    <div class="search-container">
                        <label for="search-offenses"></label>
                        <input type="text" id="search-offenses" placeholder="Search Offense">
                        <button class="search-btn">
                            <img src="../icons/search.png" alt="search Icon" class="icon">
                        </button>
                    </div>
                </div>
                <div class="offenseL-table-container">
                    <table class="offenseL-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_offenses as $offense): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($offense['name']); ?></td>
                                    <td><?php echo htmlspecialchars($offense['description']); ?></td>
                                    <td><?php echo htmlspecialchars($offense['rate']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const offensesInput = document.getElementById('search-offenses');
                    
                    offensesInput.addEventListener('keyup', function() {
                        const filter = offensesInput.value.toLowerCase();
                        const table = document.querySelector('#offenseL .offenseL-table tbody');
                        const rows = table.getElementsByTagName('tr');

                        for (let i = 0; i < rows.length; i++) {
                            const cells = rows[i].getElementsByTagName('td');
                            let match = false;

                            for (let j = 0; j < cells.length; j++) {
                                if (cells[j] && cells[j].textContent.toLowerCase().includes(filter)) {
                                    match = true;
                                    break;
                                }
                            }
                            rows[i].style.display = match ? '' : 'none';
                        }
                    });
                });
            </script>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
