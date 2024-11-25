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


// Handle CRUD operations
$error = ""; // Initialize error message variable
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = isset($_POST['id']) ? trim($_POST['id']) : '';
    $datetime = isset($_POST['datetime']) ? trim($_POST['datetime']) : '';
    $license_id = isset($_POST['license_id']) ? trim($_POST['license_id']) : '';
    $traffic_enforcer = isset($_POST['traffic_enforcer']) ? trim($_POST['traffic_enforcer']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    $offense_name = isset($_POST['offense_name']) ? trim($_POST['offense_name']) : '';
    $offense_rate = isset($_POST['offense_rate']) ? trim($_POST['offense_rate']) : '';

    // Validate fields for create and update actions only
    if (($action === 'create' || $action === 'update') && 
        (empty($datetime) || empty($license_id) || empty($status) || empty($offense_name) || empty($offense_rate))) {
        $error = "All fields are required."; // Set error message
    } else {
        try {
            if ($action === 'create') {
                // Fetch the full name of the traffic enforcer
                $enforcers_stmt = $pdo->prepare("SELECT CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) AS name FROM traffic_enforcers WHERE username = ?");
                $enforcers_stmt->execute([$_SESSION['username']]);
                $traffic_enforcer_data = $enforcers_stmt->fetch(PDO::FETCH_ASSOC);

                // Check if the enforcer's name was found
                if ($traffic_enforcer_data) {
                    $traffic_enforcer = $traffic_enforcer_data['name'];
                } else {
                    $traffic_enforcer = 'Unknown Enforcer'; // Default value if not found
                }

                // Generate a random 7-digit number
                do {
                    $ticket_no = str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM offense_records WHERE ticket_no = ?");
                    $stmt->execute([$ticket_no]);
                    $ticket_no_exists = $stmt->fetchColumn();
                } while ($ticket_no_exists > 0);

                // Insert the new record
                $stmt = $pdo->prepare("INSERT INTO offense_records (datetime, ticket_no, license_id, traffic_enforcer, status, offense_name, offense_rate, enforcer_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$datetime, $ticket_no, $license_id, $traffic_enforcer, $status, $offense_name, $offense_rate, $_SESSION['enforcer_id']]);
                header("Location: enforcer_offenseRecords.php");
                exit();         
            } elseif ($action === 'update') {
                // Fetch the full name of the traffic enforcer
                $enforcers_stmt = $pdo->prepare("SELECT CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) AS name FROM traffic_enforcers WHERE username = ?");
                $enforcers_stmt->execute([$_SESSION['username']]);
                $traffic_enforcer_data = $enforcers_stmt->fetch(PDO::FETCH_ASSOC);

                // Check if the enforcer's name was found
                if ($traffic_enforcer_data) {
                    $traffic_enforcer = $traffic_enforcer_data['name'];
                } else {
                    $traffic_enforcer = 'Unknown Enforcer'; // Default value if not found
                }

                // Update the existing record
                $stmt = $pdo->prepare("UPDATE offense_records SET datetime = ?, license_id = ?, traffic_enforcer = ?, status = ?, offense_name = ?, offense_rate = ? WHERE id = ?");
                $stmt->execute([$datetime, $license_id, $traffic_enforcer, $status, $offense_name, $offense_rate, $id]);
                header("Location: enforcer_offenseRecords.php");
                exit();
            } elseif ($action === 'delete') {
                // Delete the existing record
                $stmt = $pdo->prepare("DELETE FROM offense_records WHERE id = ?");
                $stmt->execute([$id]);
                header("Location: enforcer_offenseRecords.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "An error occurred while processing your request. Please try again later.";
            error_log($e->getMessage()); // Log the error for debugging
        }
    }
}

// Search functionality with pagination
$search = isset($_GET['search']) ? '%' . trim($_GET['search']) . '%' : '%';
$stmt = $pdo->prepare("SELECT offense_records.*, 
                              CONCAT(drivers.firstname, ' ', COALESCE(drivers.middlename, ''), ' ', drivers.lastname) AS driver_name, 
                              CONCAT(traffic_enforcers.firstname, ' ', COALESCE(traffic_enforcers.middlename, ''), ' ', traffic_enforcers.lastname) AS enforcer_name
                       FROM offense_records 
                       LEFT JOIN drivers ON offense_records.license_id = drivers.license_id 
                       LEFT JOIN offenses ON offense_records.offense_name = offenses.name
                       LEFT JOIN traffic_enforcers ON offense_records.enforcer_id = traffic_enforcers.id
                       WHERE (ticket_no LIKE ? OR offense_records.license_id LIKE ? OR offense_records.status LIKE ? OR 
                              CONCAT(drivers.firstname, ' ', COALESCE(drivers.middlename, ''), ' ', drivers.lastname) LIKE ? OR 
                              offenses.name LIKE ? OR offenses.rate LIKE ?) 
                              AND offense_records.enforcer_id = ?
                       ORDER BY datetime DESC");
$stmt->execute([$search, $search, $search, $search, $search, $search, $_SESSION['enforcer_id']]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['search'])) {
    // Return records as JSON if search is performed via AJAX
    header('Content-Type: application/json');
    echo json_encode($records);
    exit();
}

// Get offense names and rates for the form dropdowns
$offenses_stmt = $pdo->prepare("SELECT id, name, rate FROM offenses");
$offenses_stmt->execute();
$offenses = $offenses_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegulaDrive</title>
    <link rel="icon" href="../logo/RegLogo.png" id="favicon">
    <link rel="stylesheet" href="../Enforcercss/EnforcerIndex.css">
    <link rel="stylesheet" href="../Enforcercss/EOffenseR.css">
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
                <li class="nav-item active" onclick="window.location='enforcer_offenseRecords.php';">
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

            <section id="offense-records" class="content-section offense-records active">
                <h2>List of Offense Records</h2>
                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <div class="offenseR-controls">
                    <button class="create-new" onclick="showRecordForm()">Create New</button>
                    <div class="search-container">
                        <label for="search">Search:</label>
                        <input type="text" id="search" name="search" placeholder="Search Records" oninput="searchRecords()" value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
                    </div>
                </div>
                <div class="offenseR-table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Datetime</th>
                                <th>Ticket No.</th>
                                <th>License ID</th>
                                <th>Driver Name</th>
                                <th>Offense</th>
                                <th>Penalty</th>
                                <th>Enforcer</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($record['datetime']))); ?></td>
                                    <td><?php echo htmlspecialchars($record['ticket_no']); ?></td>
                                    <td><?php echo htmlspecialchars($record['license_id']); ?></td>
                                    <td><?php echo htmlspecialchars($record['driver_name']); ?></td>
                                    <td><?php echo htmlspecialchars($record['offense_name']); ?></td>
                                    <td><?php echo htmlspecialchars('₱' . number_format($record['offense_rate'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($record['enforcer_name']); ?></td> <!-- Updated this line -->
                                    <td><span class="status <?php echo htmlspecialchars($record['status']); ?>"><?php echo htmlspecialchars($record['status']); ?></span></td>
                                    <td>
                                        <button class="edit-btn" onclick="editRecord('<?php echo htmlspecialchars($record['id']); ?>', '<?php echo htmlspecialchars($record['datetime']); ?>', '<?php echo htmlspecialchars($record['ticket_no']); ?>', '<?php echo htmlspecialchars($record['license_id']); ?>', '<?php echo htmlspecialchars($record['traffic_enforcer']); ?>', '<?php echo htmlspecialchars($record['status']); ?>', '<?php echo htmlspecialchars($record['offense_name']); ?>', '<?php echo htmlspecialchars($record['offense_rate']); ?>')">
                                            <img src="../icons/Edit.png" alt="Edit Icon" class="icon">
                                        </button>
                                        <form action="enforcer_offenseRecords.php" method="post" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($record['id']); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this record?');">
                                                <img src="../icons/Delete.png" alt="Delete Icon" class="icon">
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <!-- Modal for Create/Edit Record -->
    <div id="recordFormModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeRecordForm">&times;</span>
            <h2 id="formTitle">Create New Record</h2>
            <form action="enforcer_offenseRecords.php" method="post">
                <input type="hidden" id="record_id" name="id">
                
                <div class="form-group inline-group">
                    <label for="datetime">Datetime:</label>
                    <input type="datetime-local" id="datetime" name="datetime" required>
                </div>
                
                <div class="form-group inline-group">
                    <label for="ticket_no">Ticket No.:</label>
                    <input type="text" id="ticket_no" name="ticket_no" required readonly>
                </div>

                <div class="form-group inline-group">
                    <label for="license_id">License ID:</label>
                    <input type="text" id="license_id" name="license_id" required>
                </div>

                <div class="form-group inline-group">
                    <label for="offense_name">Offense:</label>
                    <select id="offense_name" name="offense_name" required onchange="updateOffenseRate()">
                        <?php foreach ($offenses as $offense): ?>
                            <option value="<?php echo htmlspecialchars($offense['name']); ?>" data-rate="<?php echo htmlspecialchars($offense['rate']); ?>">
                                <?php echo htmlspecialchars($offense['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group inline-group">
                    <label for="offense_rate">Penalty:</label>
                    <input type="text" id="offense_rate" name="offense_rate" required readonly>
                </div>

                <input type="hidden" id="traffic_enforcer" name="traffic_enforcer" value="<?php echo htmlspecialchars($_SESSION['username']); ?>">

                <div class="form-group inline-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="Pending">Pending</option>
                        <option value="Resolved">Resolved</option>
                        <option value="Unsettled">Unsettled</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" id="formAction" name="action" value="update" class="button">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showRecordForm() {
            document.getElementById('formTitle').textContent = 'Create New Record';
            document.getElementById('record_id').value = ''; // Clear ID
            document.getElementById('datetime').value = ''; // Clear datetime
            document.getElementById('license_id').value = ''; // Clear License ID
            document.getElementById('traffic_enforcer').value = '<?php echo htmlspecialchars($_SESSION['username']); ?>'; // Set Enforcer
            document.getElementById('status').value = 'Pending'; // Set default status
            document.getElementById('offense_name').selectedIndex = 0; // Reset offense selection
            document.getElementById('offense_rate').value = ''; // Clear penalty field
            
            // Show the modal
            document.getElementById('recordFormModal').style.display = 'block';
        }

        function updateOffenseRate() {
            const select = document.getElementById('offense_name');
            const rate = select.options[select.selectedIndex].getAttribute('data-rate');
            document.getElementById('offense_rate').value = rate;
        }
    </script>
    <script src="../js/script.js"></script>
    <script src="../js/enforcer_offenseRecords.js"></script>

</body>
</html>
